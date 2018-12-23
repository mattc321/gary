<?php

/**
 * @file
 * Definition of Drupal\gary_custom_views\Plugin\views\field\FieldUnitTypesCount
 */

namespace Drupal\gary_custom_views\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler for deleting entity item.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("field_unit_types_count")
 */
class FieldUnitTypesCount extends FieldPluginBase {

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
    $vid = 'unit_type';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {
     $term_data[$term->tid] = $term->name;
    }
    $this->term_data = $term_data;
    $options['term'] = ['default' => $term_data];
	  $options['sum_all'] = ['default' => 'No'];
    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {

    $form['term'] = [
        '#type' => 'select',
        '#title' => t('Select term for aggregation'),
        '#options' => $this->term_data,
        '#default_value' => $this->options['term'],
      ];
  	$form['sum_all'] = [
      '#type' => 'select',
      '#title' => t('Sum all values in this field'),
  	  '#description' => t('Sums all values for each Account'),
      '#options' => array(1 => 'No', 2 => 'Yes'),
      '#default_value' => $this->options['sum_all'],
      ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * @{inheritdoc}
   */
  public function query() {
    $tid = $this->options['term'];
    $sumall = $this->options['sum_all']; //1 == no // 2 == yes

    // [95] => LW
    // [96] => MF
    // [97] => SFR
    // [98] => TH/RH

    if ($sumall == 1 || $sumall == 'No') {

      //IF TID = 96 (MF) then we do not want to count it. We want to return field_mf_qty
      if ($tid == 96) {
        $sql = "SELECT sum(field_mf_qty_value)
          FROM node__field_project_units pu
          JOIN paragraph__field_mf_qty pq
          ON pq.entity_id = pu.field_project_units_target_id
          WHERE pu.entity_id = node_field_data.nid
          AND pq.bundle = 'project_units'
          AND pq.deleted = 0";
        // $this->ensureMyTable();
        // $this->field_alias = $this->query->add_field(NULL, "($sql)", 'field_unit_types_count');

        // Add the field.
        $params = ['aggregate' => TRUE];
        $this->field_alias = $this->query->addField(NULL, "(".$sql.")", 'field_unit_types_count', $params);
        $this->addAdditionalFields();
      }
    }
  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $alias = $this->field_alias;
    return $values->$alias;
  }
}
