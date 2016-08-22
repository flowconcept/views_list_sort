<?php

/**
 * @file
 * Definition of Drupal\views_list_sort\Plugin\views\sort\SortAllowedValues.
 */

namespace Drupal\views_list_sort\Plugin\views\sort;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\FieldAPIHandlerTrait;
use Drupal\views\Plugin\views\sort\SortPluginBase;

/**
 * Basic sort handler for dates.
 *
 * This handler enables sorting by time instead of complete date.
 *
 * @ingroup views_sort_handlers
 *
 * @ViewsSort("sort_allowed_values")
 */
class SortAllowedValues extends SortPluginBase {

  use FieldAPIHandlerTrait;

  /**
   * Options definition.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['allowed_values'] = array('default' => 0);
    return $options;
  }

  /**
   * Options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['allowed_values'] = array(
      '#type' => 'radios',
      '#title' => t('Sort by allowed values'),
      '#options' => array(t('No'), t('Yes')),
      '#default_value' => $this->options['allowed_values'],
    );
  }

  /**
   * Called to add the sort to a query.
   * Sort by index of allowed values using sql FIELD function.
   *
   * @see http://dev.mysql.com/doc/refman/5.5/en/string-functions.html#function_field
   */
  public function query() {
    $this->ensureMyTable();
    // Skip if disabled.
    if (!$this->options['allowed_values']) {
      return;
    }

    $field_storage = $this->getFieldStorageDefinition();
    $allowed_values = array_keys(options_allowed_values($field_storage));
    $connection = Database::getConnection();

    $formula = 'FIELD(' . $this->tableAlias .'.'. $this->field .', '. implode(', ', array_map(array($connection, 'quote'), $allowed_values)) .')';
    $this->query->addOrderBy(NULL, $formula, $this->options['order'], $this->tableAlias . '_' . $this->field . '_allowed_values');
  }
}
