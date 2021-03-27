<?php

/**
 * @file
 * Definition of Drupal\gary_custom_views\Plugin\views\field\URLPathAlias
 */

namespace Drupal\gary_custom_views\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler for displaying path alias.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("field_url_path_alias")
 */
class URLPathAlias extends FieldPluginBase {


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
      '#title' => t('Display the URL Alias')
      ];
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * @{inheritdoc}
   */
  public function query() {}


  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    if (!empty($this->view->relationship)) {
      if ($id = $values->_relationship_entities['node']) {
        $alias = \Drupal::service('path_alias.manager')
          ->getAliasByPath('/node/'.$id->id());
        return $alias;
      } else {
        \Drupal::messenger()
          ->addMessage(t('Error with the path alias field.'), 'error');
        return '';
      }
    } else {
      $alias = \Drupal::service('path_alias.manager')
        ->getAliasByPath('/node/'.$values->_entity->id());
      return $alias;
    }
    return '';
  }


}
