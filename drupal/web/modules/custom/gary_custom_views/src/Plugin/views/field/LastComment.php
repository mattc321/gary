<?php

/**
 * @file
 * Definition of Drupal\gary_custom_views\Plugin\views\field\LastComment
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
 * @ViewsField("field_last_comment")
 */
class LastComment extends FieldPluginBase {


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
      '#title' => t('Last Comment on The Entity'),
  	  '#description' => t('This field will show the last comment made on a node'),
      ];
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function addSelfTokens(&$tokens, $item) {
    // $tokens['{{ '.$item['field_name'].' }}'] = $item['value'];
  }

  /**
   * @{inheritdoc}
   */
  public function query() {

    $params = [];
    if (!empty($this->view->relationship) && !empty($this->view->field['field_last_comment']->relationship)) {
      $relationship = reset($this->view->relationship);
      $table = $relationship->table;
      $field = $relationship->field;
      $node_id = $table.'.'.$field;
    } else {
      $node_id = 'node_field_data.nid';
    }
    $sql = "SELECT cb.comment_body_value
              FROM comment_field_data fd
              join comment__comment_body cb on cb.entity_id = fd.cid
              join users_field_data ud on fd.uid = ud.uid
              where fd.entity_id =
              $node_id
              and cb.deleted = 0
              order by fd.created desc
              limit 0,1";
    $this->field_alias = $this->query->addField(NULL, "(".$sql.")", 'field_last_comment', $params);
    $this->addAdditionalFields();
  }
  public function render_item($count, $item) {
    // return $item['role'];
  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {

    $alias = $this->field_alias;
    return (empty($values->$alias) ? '' : $values->$alias);
  }


}
