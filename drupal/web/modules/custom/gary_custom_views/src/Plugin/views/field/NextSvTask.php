<?php

/**
 * @file
 * Definition of Drupal\gary_custom_views\Plugin\views\field\NextSvTask
 */

namespace Drupal\gary_custom_views\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler for display next site visit task.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("field_next_sv")
 */
class NextSvTask extends FieldPluginBase {


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
  	$form['field_info'] = [
      '#type' => 'item',
      '#title' => t('Next SV Task Info'),
  	  '#description' => t('This field will look up the next service task associated with the project node'),
      ];
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * @{inheritdoc}
   */
  public function query() {
    $term_name = 'Open';
    $vid = 'task_status';
    $term = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties(['vid' => $vid, 'name' => $term_name]);
      if (empty($term)) {
        \Drupal::logger('gary_custom_views')->error('Could not find the term Open in Task Status Vocab');
        return "error";
      }

    //the site visit task list id
    $site_visit_nid = 6499;

    //The tid for Open tasks
    $tid = key($term);

    //select the open site visit type task by min date

    $sql = "SELECT fd.title task_title
                FROM node__field_tasks ft
                join node__field_task_list ftl on ft.field_tasks_target_id = ftl.entity_id
                join node__field_task_status fts on ftl.entity_id = fts.entity_id
                join node_field_data fd on ftl.entity_id = fd.nid
                join node__field_task_due_date dd on ftl.entity_id = dd.entity_id
                where ft.entity_id = node_field_data.nid
                and ftl.field_task_list_target_id = ".$site_visit_nid."
                and field_task_status_target_id = ".$tid."
                and ft.deleted = 0
                order by dd.field_task_due_date_value asc
                limit 0,1";
    $params = [];
    $this->field_alias = $this->query->addField(NULL, "(".$sql.")", 'field_next_sv', $params);
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
