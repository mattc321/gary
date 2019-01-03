<?php


namespace Drupal\gary_fields\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
/**
 * A entity_reverse_widget
 *
 * @FieldWidget(
 *   id = "entity_reverse_widget",
 *   label = @Translation("Entity Reverse Widget"),
 *   field_types = {
 *     "entity_reverse_lookup",
 *     "string"
 *   }
 * )
 */

 class EntityReverseWidget extends WidgetBase implements WidgetInterface {

   /**
    * {@inheritdoc}
    */
   public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
     $element['value'] = $element + [
       '#type' => 'textfield',
       '#default_value' => 'wtf is this',
      //  '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
     ];

     return $element;
   }

  /**
  * {@inheritdoc}
  */
  public static function defaultSettings() {
    return [
    // Create a default setting 'size', and
    // assign a default value of 60
    'parent_bundle' => 'my_bundle',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['parent_bundle'] = [
      '#type' => 'textfield',
      '#title' => t('Parent Bundle'),
      '#default_value' => $this->getSetting('parent_bundle'),
      '#required' => TRUE
    ];

    return $element;
  }
    /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Parent Bundle is set to: @bundle', array('@bundle' => $this->getSetting('parent_bundle')));

    return $summary;
  }
 }
