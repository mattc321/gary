<?php

/**
 * @file
 * Definition of Drupal\gary_custom_views\Plugin\views\field\TotalFieldUnitCount
 */

namespace Drupal\gary_custom_views\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler for displaying unit counts.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("field_total_unit_count")
 */
class TotalFieldUnitCount extends FieldPluginBase {

  /**
   * Hold the term data
   */
  private $term_data;

  /**
   * Define the available options
   * @return array
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {

    $form['description'] = [
        '#markup' => '<strong>This field will sum mf qty and add it to
          the total count of other unit types for a total unit count</strong>',
      ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * @{inheritdoc}
   */
  public function query() {

  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {

    $sum = 0;
    foreach ($values as $key => $value) {
      if (strpos($key, 'field_unit_types_count') !== FALSE) {
        $sum = !empty($value) ? $sum + $value : $sum;
      }
    }
    // $alias = $this->field_alias;
    // return (empty($values->$alias) ? '' : $values->$alias);
    return $sum;
  }
}
