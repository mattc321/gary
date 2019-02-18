<?php

/**
 * @file
 * Definition of Drupal\gary_custom_views\Plugin\views\field\EntityReverseLookup
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
 * @ViewsField("field_entity_reverse_lookup")
 */
class EntityReverseLookup extends FieldPluginBase {


  /**
   * Define the available options
   * @return array
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['parent_bundle'] = ['default' => 'article'];
    $options['parent_field_name'] = ['default' => ''];

    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
  	$form['parent_bundle'] = [
      '#type' => 'textfield',
      '#title' => t('The parent bundle'),
  	  '#default_value' => $this->options['parent_bundle'],
      ];
    $form['parent_field_name'] = [
      '#type' => 'textfield',
      '#title' => t('The parent field name referencing this content'),
  	  '#default_value' => $this->options['parent_field_name'],
      ];
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * @{inheritdoc}
   */
  public function query() {
    $sql = "SELECT n.entity_id
            from node__field_service_tasks n
            where field_service_tasks_target_id = node_field_data.nid";
    $params = [];
    $this->field_alias = $this->query->addField(NULL, "(".$sql.")", 'field_entity_reverse_lookup', []);
    $this->addAdditionalFields();
  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $alias = $this->field_alias;
    return (empty($values->$alias) ? 'x' : $values->$alias);
    // return 'test';
  }
}
