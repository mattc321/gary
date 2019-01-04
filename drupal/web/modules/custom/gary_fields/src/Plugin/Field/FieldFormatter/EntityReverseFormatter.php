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
    ksm($items);
    $element = [];
    $settings =$items->getFieldDefinition()->getSettings();
    $cardinality = $items->getFieldDefinition()->getFieldStorageDefinition()->getCardinality();
    $id = $items->getEntity()->id();
    $entity_type = $items->getEntity()->getEntityTypeId();

    $parent_bundle = $settings['parent_bundle'];
    $parent_field_table = $entity_type.'__'.$settings['parent_field_name'];
    $parent_field_column = $settings['parent_field_name'].'_target_id';

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
        $entities_relating[$node->id()] = [
          'title' => $node->getTitle(),
          'path' => $url
        ];
      }
    } else {
      return $element;
    }

    $element['#entity_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'entity-reverse-markup-container'
        ],
      ],
    ];
    foreach ($entities_relating as $delta => $item) {
      $element['#entity_container'][$delta] = [
        '#type' => 'link',
        '#title' => $item['title'],
        '#url' => $item['path'],
      ];
    }
    $element['#theme'] = 'entity_reverse_output';

    return $element;
  }

}
