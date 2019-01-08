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
    $item = $items[$delta];
    $form_type = $form_state->getFormObject()->getBaseFormId();
    $default_bundle = $items->getFieldDefinition()->getDefaultValueLiteral()[0]['parent_bundle'];
    $default_parent_field_name = $items->getFieldDefinition()->getDefaultValueLiteral()[0]['parent_field_name'];
    $parent_bundle = isset($item->parent_bundle) ? $item->parent_bundle : $default_bundle;
    $parent_field_name = isset($item->parent_field_name) ? $item->parent_field_name : $default_parent_field_name;
    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('node');
    $options = [];
    foreach ($bundles as $machine_name => $bundle) {
      $options[$machine_name] = $bundle['label'];
    }
    $element['#attached']['library'][] = 'gary_fields/garyfields';
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
      ->load('node' . '.' . $parent_bundle . '.' . 'default')
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

     // return $form;
     $response = new \Drupal\Core\Ajax\AjaxResponse();
     // $response->addCommand(new \Drupal\Core\Ajax\AlertCommand('test'));
     $response->addCommand(new InvokeCommand(NULL, 'updateFieldsSelect', ['parent-bundle-select', 'parent-field-name-options .description']));
     return $response;
   }

   // private function getDefaultValue($bundle) {
   //   if ($type == 'node') {
   //     $pg_item = Node::create(['type' => $pg_name,]);
   //   }
   //
   //   $displays = EntityFormDisplay::collectRenderDisplay($pg_item, 'default');
   //
   //   foreach ($displays->getComponents() as $name => $options) {
   //     $fields_by_weight[$options['weight']] = $name;
   //   }
   //   ksort($fields_by_weight);
   //   $this->fieldDefs = $fields_by_weight;
   // }

 }
