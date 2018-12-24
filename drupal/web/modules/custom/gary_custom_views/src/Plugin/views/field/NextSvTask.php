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
 * Field handler for display next service task.
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
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {

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
    return 0;
  }
}
