<?php

namespace Drupal\gary_fields\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'entity_reverse_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reverse_formatter",
 *   module = "gary_fields",
 *   label = @Translation("Link to Entity"),
 *   field_types = {
 *     "entity_reverse_lookup"
 *   }
 * )
 */
class EntityReverseFormatter extends FormatterBase {


  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    //build definitions
    $settings =$items->getFieldDefinition()->getSettings();
    $cardinality = $items->getFieldDefinition()->getFieldStorageDefinition()->getCardinality();
    $multiple = $cardinality == 1 ? FALSE : TRUE;
    $id = $items->getEntity()->id();
    $entity_type = $items->getEntity()->getEntityTypeId();
    $field_name = $items->getName();
    $field_type = $items->getFieldDefinition()->getType();
    $field_label = $items->getFieldDefinition()->label();
    $parent_bundle = $items->getFieldDefinition()->getDefaultValueLiteral()[0]['parent_bundle'];
    $parent_field_table = 'node__'.$items->getFieldDefinition()->getDefaultValueLiteral()[0]['parent_field_name'];
    $parent_field_column = $items->getFieldDefinition()->getDefaultValueLiteral()[0]['parent_field_name'].'_target_id';
    $label_display = $this->label;

    //query nodes for references to this node
    $query = \Drupal::database()->select($parent_field_table, 'n');
    $query->addField('n', 'entity_id');
    $query->condition('n.bundle', $parent_bundle);
    $query->condition('n.'.$parent_field_column, $id);
    $query->condition('n.deleted', 0);
    $results = $query->execute()->fetchAll();
    $entities_relating = [];
    if (count($results) > 0) {
      foreach ($results as $key => $result) {
        $node = \Drupal::entityTypeManager()->getStorage($entity_type)->load($result->entity_id);
        $options = ['absolute' => FALSE];
        $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $node->id()], $options)
          ->toString();
        $entities_relating[$node->id()]['title']=$node->getTitle();
        $entities_relating[$node->id()]['path']=$url;
      }
    } else {
      $element['#theme'] = 'entity_reverse_output';
      $element['#label'] = $field_label;
      $element['#multiple'] = $multiple;
      $element['#label_hidden'] = $label_display == 'visually-hidden' || $label_display == 'hidden' ? TRUE : FALSE;
      $element['#field_name'] = $field_name;
      $element['#field_type'] = $field_type;
      $element['#label_display'] = $label_display;
      return $element;
    }

    if ($multiple) {
      foreach ($entities_relating as $delta => $item) {
        $element['#items'][$delta]['content'] = [
          'title' => $item['title'],
          'path' => $item['path']
        ];
      }
    } else {
      $element['#items'][0]['content'] = [
        'title' => reset($entities_relating)['title'],
        'path' => reset($entities_relating)['path']
      ];
    }


    $element['#label'] = $field_label;
    $element['#theme'] = 'entity_reverse_output';
    $element['#multiple'] = $multiple;
    $element['#label_hidden'] = $label_display == 'visually-hidden' || $label_display == 'hidden' ? TRUE : FALSE;
    $element['#field_name'] = $field_name;
    $element['#field_type'] = $field_type;
    $element['#label_display'] = $label_display;
    return $element;
  }

}
