<?php


namespace Drupal\gary_fields\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\field\Entity\FieldStorageConfig;

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
    $item = $items[0];
    //might be rendering outside the config page so just return
    if (!method_exists($form_state->getFormObject(), 'getBaseFormId')) {
      return $element;
    }
    $form_type = $form_state->getFormObject()->getBaseFormId();
    //TODO default value not pulling for some reason
    $default_bundle = $items->getFieldDefinition()->getDefaultValueLiteral()[0]['parent_bundle'];
    $default_bundle = 'article';
    $default_parent_field_name = '';
    $default_parent_field_name = $items->getFieldDefinition()->getDefaultValueLiteral()[0]['parent_field_name'];
    $default_bundle_shared = $items->getFieldDefinition()->getDefaultValueLiteral()[0]['parent_bundle_shared'];
    $parent_bundle = !empty($item->parent_bundle) ? $item->parent_bundle : $default_bundle;
    $parent_field_name = !empty($item->parent_field_name) ? $item->parent_field_name : $default_parent_field_name;
    $parent_bundle_shared = !empty($item->parent_bundle_shared) ? $item->parent_bundle_shared : $default_bundle_shared;
    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('node');
    $options = [];
    foreach ($bundles as $machine_name => $bundle) {
      $options[$machine_name] = $bundle['label'];
    }
    $element['#attached']['library'][] = 'gary_fields/garyfields';
    $element['parent_bundle_shared'] = [
      '#title' => $this->t('Is there multiple parent bundles?'),
      '#type' => $form_type == 'field_config_form' ? 'checkbox' : 'hidden',
      '#default_value' => $parent_bundle_shared,
      '#description' => $this->t('If this field is shared between bundles check this box to return the value regardless of its bundle selected below')
    ];
    $element['parent_bundle'] = [
      '#title' => $this->t('Parent Bundle'),
      '#type' => $form_type == 'field_config_form' ? 'select' : 'hidden',
      '#options' => $options,
      '#default_value' => $parent_bundle,
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
      ->load('node' . '.' .$parent_bundle. '.' . 'default')
      ->getComponents();
    $field_options = "";
    foreach($bundle_fields as $field_name => $bundle_field) {
      $field_config = FieldStorageConfig::loadByName('node', $field_name);
      if (!empty($field_config)) {
          if ($field_config->getType() == 'entity_reference') {
            $field_options .= '<p>'.$field_name.'</p>';
          }
      }
    }
    $element['parent_field_name'] = [
      '#title' => $this->t('Parent Field Name'),
      '#type' => $form_type == 'field_config_form' ? 'textfield' : 'hidden',
      '#required' => TRUE,
      '#default_value' => $parent_field_name,
    ];

    $element['parent_field_name_description'] = [
      '#title' => $this->t('Choose the field containing the reference to this bundle'),
      '#prefix' => '<div class="parent-field-name-options">',
      '#suffix' => '</div>',
      '#type' => $form_type == 'field_config_form' ? 'item' : 'hidden',
      '#description' => $field_options,
    ];
    return $element;
   }

   public function updateFields(array &$form, FormStateInterface $form_state) {
     $response = new \Drupal\Core\Ajax\AjaxResponse();
     $response->addCommand(new InvokeCommand(NULL, 'updateFieldsSelect', ['parent-bundle-select', 'parent-field-name-options .description']));
     return $response;
   }

 }
