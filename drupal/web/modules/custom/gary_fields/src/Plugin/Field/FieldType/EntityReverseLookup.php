<?php

namespace Drupal\gary_fields\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Ajax;
use Drupal\Core\Ajax\InvokeCommand;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides a field type of entity_reverse_lookup.
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
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Bundle?'));
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
      // value of 'article'
      'parent_bundle' => 'article',
      'parent_field_name' => '',
    ] + parent::defaultFieldSettings();
  }

  /**
  * {@inheritdoc}
  */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('node');
    $options = [];
    foreach ($bundles as $machine_name => $bundle) {
      $options[$machine_name] = $bundle['label'];
    }
    $element =[];
    $element['#attached']['library'][] = 'gary_fields/garyfields';
    $element['parent_bundle'] = [
      '#title' => $this->t('Parent Bundle'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $this->getSetting('parent_bundle'),
      '#ajax' => [
        'callback' => [$this,'updateFields'],
        'event' => 'change',
        'progress' => [
          'type' => 'throbber'
        ],
      ]
    ];
    $element['parent_bundle']['#attributes']['class'][]='parent-bundle-select';

    $bundle_fields = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load('node' . '.' . $this->getSetting('parent_bundle') . '.' . 'default')
      ->getComponents();

    $field_options = "";
    // ksm($bundle_fields);
    foreach($bundle_fields as $field_name => $bundle_field) {
      $field_config = \Drupal\field\Entity\FieldStorageConfig::loadByName('node', $field_name);
      if (!empty($field_config)) {
          if ($field_config->getType() == 'entity_reference') {
            $field_options .= '<p>'.$field_name.'</p>';
          }
      }
    }

    $element['parent_field_name'] = [
      '#title' => $this->t('Parent Field Name'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $this->getSetting('parent_field_name'),
    ];

    $element['parent_field_name_description'] = [
      '#title' => $this->t('Choose the field containing the reference to this bundle'),
      '#type' => 'item',
      '#description' => $field_options,
    ];
    return $element;
  }

  public function updateFields(array &$form, FormStateInterface $form_state) {

    // return $form;
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    // $response->addCommand(new \Drupal\Core\Ajax\AlertCommand('test'));
    $response->addCommand(new InvokeCommand(NULL, 'updateFieldsSelect', ['parent-bundle-select', 'edit-settings-parent-field-name-description--description']));
    return $response;
  }

 }
