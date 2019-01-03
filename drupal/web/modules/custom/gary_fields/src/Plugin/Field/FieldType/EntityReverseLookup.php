<?php

namespace Drupal\gary_fields\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
/**
 * Provides a field type of entity_reverse_lookup.
 *
 * @FieldType(
 *   id = "entity_reverse_lookup",
 *   label = @Translation("Entity Reverse Lookup"),
 *   default_formatter = "entity_reverse_formatter",
 *   default_widget = "entity_reverse_widget",
 * )
 */

 class EntityReverseLookup extends FieldItemBase implements FieldItemInterface {

   /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      // columns contains the values that the field will store
      'columns' => array(
        // List the values that the field will save. This
        // field will only save a single value, 'value'
        'value' => array(
          'type' => 'text',
          'size' => 'tiny',
          'not null' => FALSE,
        ),
      ),
    );
  }

  /**
  * {@inheritdoc}
  */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = [];
    $properties['value'] = DataDefinition::create('string');

    return $properties;
  }

  /**
  * {@inheritdoc}
  */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      // Declare a single setting, 'size', with a default
      // value of 'large'
      'parent_bundle' => 'large',
    ] + parent::defaultFieldSettings();
  }

  /**
  * {@inheritdoc}
  */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {

    $element = [];
    // The key of the element should be the setting name
    $element['parent_bundle'] = [
      '#title' => $this->t('Parent Bundle'),
      '#type' => 'select',
      '#options' => [
        'small' => $this->t('Small'),
        'medium' => $this->t('Medium'),
        'large' => $this->t('Large'),
      ],
      '#default_value' => $this->getSetting('size'),
    ];

    return $element;
  }


 }
