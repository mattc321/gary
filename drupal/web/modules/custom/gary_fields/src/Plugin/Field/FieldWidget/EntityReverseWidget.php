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
 *   module = "gary_fields",
 *   label = @Translation("Entity Reverse Widget"),
 *   field_types = {
 *     "entity_reverse_lookup"
 *   }
 * )
 */

 class EntityReverseWidget extends WidgetBase {

   /**
    * {@inheritdoc}
    */
   public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
     $element = [];
    //  $value = isset($items[$delta]->value) ? $items[$delta]->value : '';
    //   $element += [
    //     '#type' => 'textfield',
    //     '#default_value' => $value,
    //     '#size' => 7,
    //     '#maxlength' => 7,
    //   ];
    //   return ['value' => $element];

     return $element;
   }
 }
