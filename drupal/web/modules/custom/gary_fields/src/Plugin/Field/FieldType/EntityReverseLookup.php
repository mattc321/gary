<?php

namespace Drupal\gary_fields\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Provides a field type of entity_rev  erse_lookup.
 *
 * @FieldType(
 *   id = "entity_reverse_lookup",
 *   module = "gary_fields",
 *   label = @Translation("Entity Reverse Lookup"),
 *   default_formatter = "entity_reverse_formatter",
 *   default_widget = "entity_reverse_widget",
 * )
 */

 class EntityReverseLookup extends FieldItemBase {

   /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema['columns']['parent_bundle_shared'] = [
      'description' => 'If the parent bundle is shared.',
      'type' => 'int',
      'size' => 'tiny',
      'not null' => FALSE,
    ];
    $schema['columns']['parent_bundle'] = [
      'description' => 'The ID of the view display.',
      'type' => 'text',
      'size' => 'tiny',
      'not null' => FALSE,
    ];

    $schema['columns']['parent_field_name'] = [
      'description' => 'Arguments to be passed to the display.',
      'not null' => FALSE,
      'type' => 'text',
      'size' => 'tiny',
    ];

    return $schema;
  }

  /**
  * {@inheritdoc}
  */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = [];
    $properties['parent_bundle_shared'] = DataDefinition::create('integer')
      ->setLabel(t('Shared Parent Bundle?'));
    $properties['parent_bundle'] = DataDefinition::create('string')
      ->setLabel(t('Bundle?'));
    $properties['parent_field_name'] = DataDefinition::create('string')
      ->setLabel(t('Field Name'));
    return $properties;
  }

  /**
  * {@inheritdoc}
  */
  public function isEmpty() {
    $value = $this->get('parent_bundle')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {

    return parent::defaultFieldSettings();
  }

  /**
  * {@inheritdoc}
  */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {

    $element = [];
    return $element;
  }


 }
