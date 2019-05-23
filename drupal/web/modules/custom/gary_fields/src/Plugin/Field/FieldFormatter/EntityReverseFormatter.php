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
    //not going to be looking at $cardinality for now. Displayed based on result count
    $cardinality = $items->getFieldDefinition()->getFieldStorageDefinition()->getCardinality();
    $multiple = FALSE;
    $id = $items->getEntity()->id();
    $entity_type = $items->getEntity()->getEntityTypeId();
    $field_name = $items->getName();
    $field_type = $items->getFieldDefinition()->getType();
    $field_label = $items->getFieldDefinition()->label();
    // ksm($items->getFieldDefinition()->getDefaultValueLiteral());
    $parent_bundle_shared = $items->getFieldDefinition()->getDefaultValueLiteral()[0]['parent_bundle_shared'];
    $parent_bundle = $items->getFieldDefinition()->getDefaultValueLiteral()[0]['parent_bundle'];
    $parent_field_table = 'node__'.$items->getFieldDefinition()->getDefaultValueLiteral()[0]['parent_field_name'];
    $parent_field_column = $items->getFieldDefinition()->getDefaultValueLiteral()[0]['parent_field_name'].'_target_id';
    $label_display = $this->label;

    $results = $this->getReverseResults($parent_field_table, $parent_bundle, $parent_field_column, $id, $parent_bundle_shared);
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
      $multiple = (count($results) == 1 ? FALSE : TRUE);

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

  /**
   * Helper function for fetching results of entities that reference the given
   * @param  string $parent_field_table  The table name for the default parent_bundle
   * @param  string $parent_bundle       The default bundle to look for results
   * @param  string $parent_field_column The colomn containing target ids
   * @param  string $id                  The ID of the entity to look for
   * @param  string $parent_bundle_shared   Is the bundle shared
   * @return array                      Results of the query
   */
  public static function getReverseResults(string $parent_field_table, string $parent_bundle, string $parent_field_column, string $id, $parent_bundle_shared = NULL) {
    //query nodes for references to this node
    $query = \Drupal::database()->select($parent_field_table, 'n');
    $query->addField('n', 'entity_id');
    if ($parent_bundle_shared <> 1) {
      $query->condition('n.bundle', $parent_bundle);
    }
    $query->condition('n.'.$parent_field_column, $id);
    $query->condition('n.deleted', 0);
    $results = $query->execute()->fetchAll();
    return $results;
  }

}
