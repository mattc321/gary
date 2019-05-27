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


      if ($this->view->filter['field_project_status_target_id']->operator == 'not') {
        $op = 'not in ';
      } else {
        $op = $this->view->filter['field_project_status_target_id']->operator;
      }
      $filter = $this->view->filter['field_project_status_target_id']->value;
      $tids = '';
      $i = 0;
      foreach($filter as $key => $filter_value) {
        $i++;
        if (count($filter) == $i) {
          $tids .= $filter_value;
        } else {
          $tids .= $filter_value.',';
        }
      }

      $filter_by = $op.'('.$tids.')';
      $sql = "
      select grandtotal
        from
        	(select field_account_reference_target_id,
        	@total1 := (select sum(field_mf_qty_value)
        		from node_field_data as node
        		join node__field_account_reference account_ref on nid = account_ref.entity_id
        		join node__field_project_units proj_units on nid = proj_units.entity_id
        		left JOIN paragraph__field_mf_qty pq ON pq.entity_id = proj_units.field_project_units_target_id
        		JOIN paragraph__field_unit_types ut ON ut.entity_id = proj_units.field_project_units_target_id
        		join node__field_project_status proj_status on node.nid = proj_status.entity_id
        		where node.type = 'projects'
        		and field_account_reference_target_id = node_field_data_node__field_account_reference.nid
        		and ut.field_unit_types_target_id = 96
        		and proj_status.field_project_status_target_id not in (70, 72)) as total_mf_qty,
        	@total2 := (select count(proj_units.entity_id)
        		from node_field_data as node
        		join node__field_account_reference account_ref on nid = account_ref.entity_id
        		join node__field_project_units proj_units on nid = proj_units.entity_id
        		left JOIN paragraph__field_mf_qty pq ON pq.entity_id = proj_units.field_project_units_target_id
        		JOIN paragraph__field_unit_types ut ON ut.entity_id = proj_units.field_project_units_target_id
        		join node__field_project_status proj_status on node.nid = proj_status.entity_id
        		where node.type = 'projects'
        		and field_account_reference_target_id = node_field_data_node__field_account_reference.nid
        		and ut.field_unit_types_target_id <> 96
        		and proj_status.field_project_status_target_id not in (70, 72)) as unit_count,
        @total1 + @total2 as grandtotal
        from node_field_data as node
        join node__field_account_reference account_ref on nid = account_ref.entity_id
        join node__field_project_units proj_units on nid = proj_units.entity_id
        left JOIN paragraph__field_mf_qty pq ON pq.entity_id = proj_units.field_project_units_target_id
        JOIN paragraph__field_unit_types ut ON ut.entity_id = proj_units.field_project_units_target_id
        join node__field_project_status proj_status on node.nid = proj_status.entity_id
        and node.type = 'projects'
        and proj_status.field_project_status_target_id ". $filter_by .
        " group by field_account_reference_target_id) as temp_table";

      // $sql = "SELECT sum(field_mf_qty_value)
      // from node_field_data as project_node
      // join node__field_account_reference account_ref on project_node.nid = account_ref.entity_id
      // join node__field_project_units proj_units on project_node.nid = proj_units.entity_id
      // JOIN paragraph__field_mf_qty pq ON pq.entity_id = proj_units.field_project_units_target_id
      // join paragraph__field_unit_types ut ON ut.entity_id = proj_units.field_project_units_target_id
      // join node__field_project_status proj_status on project_node.nid = proj_status.entity_id
      // where field_account_reference_target_id = node_field_data_node__field_account_reference_nid
      // and project_node.type = 'projects'
      // and ut.field_unit_types_target_id = " . $tid .
      // " and proj_status.field_project_status_target_id ". $filter_by;

      $params = ['aggregate' => TRUE];
      $this->field_alias = $this->query->addField(NULL, "(".$sql.")", 'field_total_unit_count', $params);
      $this->addAdditionalFields();

  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $alias = $this->field_alias;
    return (empty($values->$alias) ? '' : $values->$alias);
  }
}
