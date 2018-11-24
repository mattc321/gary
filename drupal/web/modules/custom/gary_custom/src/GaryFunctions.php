<?php

/**
 * File for holding helper functions user by Gary
 */


 namespace Drupal\gary_custom;

 use Drupal\Core\Routing\RouteMatchInterface;
 use Drupal\Core\Entity\EntityInterface;
 use Drupal\Core\Entity\FieldableEntityInterface;


class GaryFunctions {

  public function calculateField(EntityInterface $entity, $field_name) {

    if ($field_name == 'field_ach50') {
      //(CFM50 * 60) / (CFA * Height)
      $cfm = (!empty($entity->get('field_cfm_50')->getValue()) ? $entity->get('field_cfm_50')->getValue() : 0);
      $cfa = (!empty($entity->get('field_cfa')->getValue()) ? $entity->get('field_cfa')->getValue() : 0);
      $ht = (!empty($entity->get('field_height')->getValue()) ? $entity->get('field_height')->getValue() : 0);
      $calc = ($cfm + $cfa + $ht <= 0 ? 0 : ($cfm * 60) / ($cfa * $ht));
      return $calc;
    }
    return;
  }

  public function cleanParagraphs(EntityInterface $entity) {
    // Check to make sure method exists.
    if (!($entity instanceof FieldableEntityInterface)) {
      return;
    }

    // Get all field definitions for this entity.
    $field_definitions = $entity->getFieldDefinitions();

    // Loop through each field definition looking for paragraphs.
    foreach ($field_definitions as $field_definition) {
      // Check if a paragraph field with revisions.
      if ($field_definition->getSetting('target_type') != 'paragraph' || $field_definition->getType() != 'entity_reference_revisions') {
        continue;
      }

      // Get field original ids.
      $original_ids = array_column($entity->original->{$field_definition->getName()}->getValue(), 'target_id');

      // If no original ids, skip field.
      if (empty($original_ids)) {
        continue;
      }

      // Get field new ids.
      $new_ids = array_column($entity->{$field_definition->getName()}->getValue(), 'target_id');

      // Flag original ids not in new ids for deletion.
      $delete_ids = array_diff($original_ids, $new_ids);

      // Delete flagged ids.
      if (!empty($delete_ids)) {
        $storage_handler = \Drupal::entityTypeManager()->getStorage('paragraph');
        $entities = $storage_handler->loadMultiple($delete_ids);
        $storage_handler->delete($entities);
      }
    }
  }
}
