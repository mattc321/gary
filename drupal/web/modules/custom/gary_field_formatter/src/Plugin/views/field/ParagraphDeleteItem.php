<?php

/**
 * @file
 * Definition of Drupal\gary_field_formatter\Plugin\views\field\ParagraphTypeFlagger
 */

namespace Drupal\gary_field_formatter\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler for deleting paragraph item.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("paragraph_delete_item")
 */
class ParagraphDeleteItem extends FieldPluginBase {

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

    $form['paragraph_delete_item'] = [
      '#title' => t('Ajaxed Delete Paragraph Item'),
      '#type' => 'item',
      '#description' => t('This field will create a link to delete the paragraph item')
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {

    //create a matching dom_id as set in the field handler
    $display_id = (!empty($this->view->current_display) ? $this->view->current_display : $this->view->getDisplay()->getPluginId());
    $dom_string = str_replace("_","-",$this->view->id()) . "-" . $display_id;
    $final_dom_id = 'js-view-dom-id-'.$dom_string;

    //the paragraph id to delete
    $id = isset($values->id) ? $values->id : null;
    if ($id == null) {
      $relationship_id = $this->options['relationship'];
      if ($relationship_id != 'none') {
        $id = $values->_relationship_entities[$relationship_id]->id();
        $final_dom_id = 'js-view-dom-id-'.$this->view->dom_id;
      } else {
        return ['#markup' => 'Error in relationship'];
      }
    } else {
      $id = $values->id;
    }

    $link = [
      '#title' => t('&times;'),
      '#description' => t('Remove the item from the opportunity'),
      '#type' => 'link',
      '#attributes' => [
        'class' => [
          'use-ajax',
          'delete-pg-item'
        ],
      ],
      '#url' => Url::fromRoute('gary_field_formatter.delete_paragraph', [
                      'pid' => $id,
                      'vid' => $final_dom_id], []),
    ];
    return $link;
  }
}
