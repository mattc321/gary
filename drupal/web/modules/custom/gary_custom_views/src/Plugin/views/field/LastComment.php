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
      '#title' => t('Next SV Task Info'),
  	  '#description' => t('This field will look up the next service task associated with the project node'),
      ];
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function addSelfTokens(&$tokens, $item) {
    $tokens['{{ '.$item['field_name'].' }}'] = $item['value'];
  }

  /**
   * @{inheritdoc}
   */
  public function query() {

    //select the last comment and user info
    // $fields_to_add = [
    //     'field_last_comment_cid' => 'fd.cid',
    //     'field_last_comment_subject' => 'fd.subject',
    //     'field_last_comment_body_value' => 'cb.comment_body_value',
    //     'field_last_comment_uid' => 'fd.uid',
    //     'field_last_comment_created' => 'fd.created',
    //     'field_last_comment_name' => 'ud.name',
    //
    // ];
    $params = [];
    // foreach ($fields_to_add as $fieldname => $column) {
    //   // $this0->items[]
    //   $sql = "SELECT ".$column."
    //             FROM gary.comment_field_data fd
    //             join comment__comment_body cb on cb.entity_id = fd.cid
    //             join users_field_data ud on fd.uid = ud.uid
    //             where fd.entity_id = node_field_data.nid
    //             and cb.deleted = 0
    //             order by fd.created desc
    //             limit 0,1";
    //   $this->aliases[] = $this->query->addField(NULL, "(".$sql.")", $fieldname, $params);
    // }

    $sql = "SELECT cb.comment_body_value
              FROM gary.comment_field_data fd
              join comment__comment_body cb on cb.entity_id = fd.cid
              join users_field_data ud on fd.uid = ud.uid
              where fd.entity_id = node_field_data.nid
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
    // $last_field = end($this->view->field);
    // $fake_item = [
    //   'alter_text' => TRUE,
    //   'text' => 'testy',
    // ];
    // if (isset($last_field->last_tokens)) {
    //   $tokens = $last_field->last_tokens;
    // }
    // else {
    //   $tokens = $last_field
    //     ->getRenderTokens($fake_item);
    // }
    //
    // // $tokens = $this->getRenderTokens($item);
    // foreach($this->aliases as $fieldname) {
    //   $item = [
    //     'field_name' => $fieldname,
    //     'value' => $values->$fieldname,
    //   ];
    //   $this->addSelfTokens($tokens, $item);
    // }
    //
    $alias = $this->field_alias;
    return (empty($values->$alias) ? '' : $values->$alias);
  }


}
