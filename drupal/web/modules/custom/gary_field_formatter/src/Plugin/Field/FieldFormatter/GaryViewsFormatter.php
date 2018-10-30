<?php

namespace Drupal\gary_field_formatter\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'fc_views_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "fc_views_formatter",
 *   label = @Translation("Field Collection View Formatter"),
 *   field_types = {
 *     "field_collection"
 *   }
 * )
 */
class GaryViewsFormatter extends FormatterBase {


  /**
   * The new table id.
   *
   * @var string
   */
  protected $tableId;


  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Display a field collection view');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    // Return nothing if there are no items in this array.
    if (count($items) <= 0) {
      return $elements;
    }

    return $elements;
  }


  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['ajax_inputs'] = [
      '#title' => $this->t('Use Ajax Inputs'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('ajax_inputs'),
    ];

    $element['view_machine_name'] = [
      '#title' => $this->t('The machine name of the view'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('view_machine_name'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Declare a setting named 'text_length', with
      // a default value of 'short'
      'ajax_inputs' => FALSE,
      'view_machine_name' => "",
    ] + parent::defaultSettings();
  }

  protected function viewValue(FieldItemInterface $item, $fields_to_output = []) {
    $row = [];

    return $row;
  }
}
