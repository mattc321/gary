<?php

/**
 * File for holding helper functions user by Gary
 */


namespace Drupal\gary_custom;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

class GaryFunctions {

  /**
   * Handler for calculating fields in gary
   * @param  EntityInterface $entity     The entity containing the field we are calculating
   * @param  string          $field_name The field name string of the field we are calculating
   * @return boolean                      return
   */
  public function calculateField(EntityInterface $entity, $field_name) {


    //calculate ACH50
    if ($field_name == 'field_ach50') {
      //(CFM50 * 60) / (CFA * Height)
      $cfm = (!empty($entity->get('field_cfm_50')->getValue()) ? $entity->get('field_cfm_50')->get(0)->getValue()['value'] : 0.00);
      $cfa = (!empty($entity->get('field_cfa')->getValue()) ? $entity->get('field_cfa')->get(0)->getValue()['value'] : 0.00);
      $ht = (!empty($entity->get('field_height')->getValue()) ? $entity->get('field_height')->get(0)->getValue()['value'] : 0.00);

      if ($cfm > 0 && $cfa > 0 && $ht > 0 ) {
        $calc = ($cfm * 60) / ($cfa * $ht);
        return $calc;
      }
      return 0;
    }

    //calculate Volume
    if ($field_name == 'field_volume') {
      //CFA * Height
      $cfa = (!empty($entity->get('field_cfa')->getValue()) ? $entity->get('field_cfa')->get(0)->getValue()['value'] : 0.00);
      $ht = (!empty($entity->get('field_height')->getValue()) ? $entity->get('field_height')->get(0)->getValue()['value'] : 0.00);

      if ($cfa > 0 && $ht > 0 ) {
        $calc = $cfa * $ht;
        return $calc;
      }
      return 0;
    }

    return;
  }

  /**
   * Delete revision entities on parapraph deletion
   * @param  EntityInterface $entity The paragraph entity
   * @return boolean                   Return
   */
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
    return;
  }

  /**
   * Check if entity field content has changed
   * @param  EntityInterface $entity     The entity being saved
   * @return boolean                     True if field has change, false if hasnt changed or is not found
   */
  public function entityHasChanged(EntityInterface $entity) {

    if (!$entity->original) {
      return false;
    }

    $field_names = $this->getFieldList($entity->bundle());
    foreach($field_names as $key => $field_name) {
      if(!$entity->get($field_name)->equals($entity->original->get($field_name))){
         return true;
      }
    }
     return false;
  }

  /**
   * Check if entity field content has changed
   * @param  EntityInterface $entity     The entity being saved
   * @param  array          $field_names The name of the field to check
   * @return boolean                     True if field has change, false if hasnt changed or is not found
   */
  public function fieldHasChanged(EntityInterface $entity, string $field_name) {

    if (!$entity->hasField($field_name)) {
      return false;
    }

    if (!$entity->original) {
      return false;
    }

    if(!$entity->get($field_name)->equals($entity->original->get($field_name))){
       return true;
    }

    return false;
  }

  /**
   * Get list of field names from bundle
   * @param  string $bundle Bundle name
   * @return array         Array of field names
   */
  public function getFieldList($bundle) {
    $entity = \Drupal\paragraphs\Entity\Paragraph::create(['type' => $bundle,]);
    $displays = EntityViewDisplay::collectRenderDisplays([$entity], 'full');
    $display = $displays[$entity->bundle()];
    foreach ($display->getComponents() as $name => $options) {
      $fields_by_weight[] = $name;
    }
    return $fields_by_weight;
  }

  /**
   * Get the parent node id referencing this task
   * @param  EntityInterface $task_entity The task entity
   * @return string                       The parent node id
   */
  public function getParentNid(EntityInterface $task_entity) {
    //connect db lookup entity_id referencing this target_id
    $query = \Drupal::database()->select('node__field_tasks', 'n');
    $query->addField('n', 'entity_id');
    $query->condition('n.field_tasks_target_id', $task_entity->id());
    $results = $query->execute()->fetchAll();

    //there should never be more than one parent. If so the first keyed one is likely wrong
    if (count($results) > 1) {
      $messenger = \Drupal::messenger();
      $messenger->addMessage('An error occurred! Contact the site administrator and check the log', $messenger::TYPE_WARNING);
      \Drupal::logger('gary_custom')->error('More than one parent node is referencing a single task id. No bueno');
    }

    //the parent nid referencing the task
    return $results[0]->entity_id;
  }

}
