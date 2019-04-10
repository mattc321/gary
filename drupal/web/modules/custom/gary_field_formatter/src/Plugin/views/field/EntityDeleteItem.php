<?php

/**
 * @file
 * Definition of Drupal\gary_field_formatter\Plugin\views\field\ParagraphTypeFlagger
 */

namespace Drupal\gary_field_formatter\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
/**
 * Field handler for deleting entity item.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("entity_delete_item")
 */
class EntityDeleteItem extends FieldPluginBase {

  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

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

    $form['entity_delete_item'] = [
      '#title' => t('Ajaxed Delete Entity Item'),
      '#type' => 'item',
      '#description' => t('This field will create a link to delete the entity item')
    ];

    parent::buildOptionsForm($form, $form_state);
  }


  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {

    //get the right id to delete
    $relationship_id = $this->options['relationship'];

    //apparently the route needs this values initialized they cant be blank or null
    $host_id = 0;
    $id = 0;
    if ($relationship_id == 'none') {
      $id = $values->_entity->id();
    }
    elseif (isset($values->_relationship_entities[$relationship_id])) {
      $id = $values->_relationship_entities[$relationship_id]->id();
      $host_id = $values->_entity->id();
    }

    //create a matching dom_id as set in the field handler
    $display_id = (!empty($this->view->current_display) ? $this->view->current_display : $this->view->getDisplay()->getPluginId());
    $dom_string = str_replace("_","-",$this->view->id()) . "-" . $display_id;
    $final_dom_id = 'js-view-dom-id-'.$dom_string;

    $user = User::load(\Drupal::currentUser()->id());
    $access = $this->checkUserAccess($user);

    $link = [
      '#title' => t('&times;'),
      '#type' => 'link',
      '#access' => $access,
      '#attributes' => [
        'class' => [
          'use-ajax',
          'delete-entity-item'
        ],
      ],
      '#url' => Url::fromRoute('gary_field_formatter.delete_entity', [
                      'pid' => $id,
                      'vid' => $final_dom_id,
                      'host_id' => $host_id,
                      'host_field' => $relationship_id], []),
    ];
    return $link;
  }

  /**
   * check if user has ec roles
   * @param  Object $user The user
   * @return bool       True if they have it
   */
  protected function checkUserAccess($user) {
    if ($user->hasRole('ec_admin')) {
      return TRUE;
    }
    if ($user->hasPermission('administrator')) {
      return TRUE;
    }
    return FALSE;
  }
}
