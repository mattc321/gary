<?php

/**
 * File for holding helper functions user by Gary
 */


namespace Drupal\gary_custom;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

class GaryFunctions {

  /**
   * Utility function to load an entity reference
   * @param  EntityInterface $entity The entity containing the entity ref
   * @param  string          $field  The entity ref fieldname
   * @return object                  The loaded entity
   */
  public static function loadEntityRef(EntityInterface $entity, $field) {
    return
      $entity
      ->get($field)
      ->first()
      ->get('entity')
      ->getTarget()
      ->getValue();
  }

  /**
   * Get a list of available services and prices by id
   * @return array An Associative array containing service info
   */
  public static function getServiceOptions() {
    //query services available to use
    $query = \Drupal::database()->select('node_field_data', 'n');
    $query->addField('n', 'nid');
    $query->addField('n', 'title');
    $query->addField('p', 'field_unit_price_value');
    $query->addJoin('inner','node__field_disabled','d','n.nid=d.entity_id');
    $query->addJoin('inner','node__field_unit_price','p','n.nid=p.entity_id');
    $query->condition('n.type', 'services');
    $query->condition('d.field_disabled_value', 1, '<>');
    $query->condition('d.deleted', 0);
    $query->condition('p.deleted', 0);
    $results = $query->execute()->fetchAll();
    return $results;
  }

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
   * Get the default account manager located from its referenced account
   * @param  EntityInterface $entity The entity being updated
   * @return mixed                  The account manager empty if needed or an empty array if not
   */
  public function getDefaultAccountManagerIfNeeded(EntityInterface $entity) {

    if (!$entity->hasField('field_orig_account')) {
      return;
    }

    if (empty($entity->get('field_orig_account')->referencedEntities())) {
      return;
    }

    $account = $entity->get('field_orig_account')->referencedEntities()[0];
    if (!$account->hasField('field_account_manager')) {
      return;
    }

    if (empty($account->get('field_account_manager')->referencedEntities())) {
      return;
    }

    $account_mgr = $account->get('field_account_manager')->referencedEntities()[0];
    if (!$entity->hasField('field_account_manager')) {
      return;
    }

    if ($entity->get('field_account_manager')->isEmpty()) {
      return $account_mgr;
    }

    return [];
  }

  /**
   * Helper function for fetching results of entities that reference the opportunity
   * @param  string $id                  The ID of the entity to look for
   * @return array                      Results of the query
   */
  public static function getReferencedProject(string $id) {
    //query nodes for references to this node
    $query = \Drupal::database()->select('node__field_opportunity', 'n');
    $query->addField('n', 'entity_id');
    $query->condition('n.bundle', 'projects');
    $query->condition('n.field_opportunity_target_id', $id);
    $query->condition('n.deleted', 0);
    $results = $query->execute()->fetchAll();
    return $results;
  }

  /**
   * update the the sum of all services on the parent entity
   * @param  EntityInterface $entity The paragraph entity
   * @return boolean         Return true of the parent field was found and set
   */
  public function updateTotalAmount(EntityInterface $entity) {

    if (!$entity->getParentEntity()->hasField('field_opportunity_services_ref')) {
      return;
    }

    if ($entity->getParentEntity()->get('field_opportunity_services_ref')->isEmpty()) {
      return;
    }
    if (!$entity->getParentEntity()->hasField('field_amount')) {
      return;
    }
    //calculate the total amount of services on the parent node
    $total_price = 0;
    foreach($entity->getParentEntity()->get('field_opportunity_services_ref')->referencedEntities() as $key => $pg_item) {
      if ($pg_item->hasField('field_line_total')) {
        $total_price = $total_price + $pg_item->get('field_line_total')->value;
      }
    }
    $entity->getParentEntity()->set('field_amount', $total_price);
    $entity->getParentEntity()->save();
    return TRUE;
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
   * Get an array of users with a specific role
   * @param  string $role The role to select users by
   * @return mixed       Mixed array of users
   */
  public static function loadUsersByRole(string $role = NULL) {

    $ids = \Drupal::entityQuery('user')
    ->condition('status', 1)
    ->condition('roles', $role)
    ->execute();
    $users = [];
    $users = User::loadMultiple($ids);

    return $users;
  }

  /**
   * Check if entity field content has changed
   * @param  EntityInterface $entity     The entity being saved
   * @return boolean                     True if field has change, false if hasnt changed or is not found
   */
  public function entityHasChanged(EntityInterface $entity) {
    // $changed_fields = [];
    if (!$entity->original) {
      return false;
    }
    $field_names = $this->getFieldList($entity->bundle(), $entity->getEntityTypeId());
    foreach($field_names as $key => $field_name) {
      if($entity->hasField($field_name) && $field_name != 'field_comments' && !$entity->get($field_name)->equals($entity->original->get($field_name))){
        // $changed_fields[] = $field_name;
        return true;
      }
    }
     return false;
  }

  /**
   * Close all of the tasks on an opp or project if it is closed
   * @param  EntityInterface $entity The project or opportunity node
   * @return boolean                  True if an update was made False if not
   */
  public function closeAllTasks(EntityInterface $entity) {

    if (!$entity->hasField('field_tasks')) {
      \Drupal::logger('gary_custom')
        ->error('field_tasks does not exist. Cannot close them');
      return FALSE;
    }

    if ($entity->get('field_tasks')->isEmpty()) {
      return FALSE;
    }

    //set all of the tasks to closed - tid 1
    foreach ($entity->get('field_tasks')->referencedEntities() as $key => $task) {
      $task->set('field_task_status', 1);
      $task->save();
    }
    return TRUE;
  }

  /**
   * Check if entity field content has changed
   * @param  EntityInterface $entity     The entity being saved
   * @param  array          $field_names The name of the field to check
   * @return boolean                     True if field has change, false if hasnt changed or is not found
   */
  public function fieldHasChanged(EntityInterface $entity, string $field_name) {

    if (!$entity->hasField($field_name)) {
      return FALSE;
    }

    if (!$entity->original) {
      return FALSE;
    }

    if(!$entity->get($field_name)->equals($entity->original->get($field_name))){
       return TRUE;
    }

    return FALSE;
  }

  /**
   * Get list of field names from bundle
   * @param  string $bundle Bundle name
   * @return array         Array of field names
   */
  public function getFieldList($bundle, $entity_type_id) {
    $fields_by_weight = [];
    // $entity = \Drupal\paragraphs\Entity\Paragraph::create(['type' => $bundle,]);
    $bundle_fields = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load($entity_type_id . '.' . $bundle . '.' . 'default')
      ->getComponents();

    // $displays = EntityViewDisplay::collectRenderDisplays([$entity], 'full');
    // $display = $displays[$entity->bundle()];
    foreach ($bundle_fields as $name => $options) {
      $fields_by_weight[] = $name;
    }
    return $fields_by_weight;
  }

  /**
   * Email the assignee of a task
   * @param  EntityInterface $entity The task node
   * @return boolean                  True if it sent false if not
   */
  public function notifyAssignee(EntityInterface $entity) {

    if (!$entity->hasField('field_task_assigned_to')) {
      return FALSE;
    }

    if ($entity->get('field_task_assigned_to')->isEmpty()) {
      return FALSE;
    }

    $assigned_to = $entity->get('field_task_assigned_to')
      ->first()
      ->get('entity')
      ->getTarget()
      ->getValue();

    $from = $entity->getRevisionAuthor();

    $parent_nid = $this->getParentNid($entity);
    $parent_node = \Drupal::entityManager()
      ->getStorage('node')
      ->load($parent_nid);


    if ($parent_node->hasField('field_account_reference')) {
      $account_title = $parent_node->get('field_account_reference')
        ->first()
        ->get('entity')
        ->getTarget()
        ->getValue()
        ->getTitle();
    } else {
      $account_title = 'No Account Set';
    }

    $parent_title = $parent_node->getTitle();
    $bundle = $parent_node->bundle();

    $params = array(
      'to_email' => $assigned_to->getEmail(),
      'from_email' => $from->getEmail(),
      'from_name' => $from->getDisplayName(),
      'task_title' => $entity->getTitle(),
      'task_id' => $entity->id(),
      'account_title' => $account_title,
      'parent_title' => $parent_title,
      'parent_bundle' => $bundle,
    );

    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'gary_custom';
    $key = 'notify_assignee';
    $params['values'] = $params;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = true;
    $result = $mailManager->mail($module, $key, $assigned_to->getEmail(), $langcode, $params, NULL, $send);
    if ($result['result'] !== true) {
      $messenger = \Drupal::messenger();
      $messenger->addMessage('An error happened and the notification to the assignee was not sent', $messenger::TYPE_WARNING);
      return FALSE;
    }
    return TRUE;
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
      $messenger->addMessage('Very unsavory error occurred! Contact the site administrator and check the log', $messenger::TYPE_WARNING);
      \Drupal::logger('gary_custom')->error('More than one parent node is referencing a single task id. No bueno');
    } elseif (count($results) == 0) {
      return $results;
    }

    //the parent nid referencing the task
    return $results[0]->entity_id;
  }

  /**
   * Average a subcontractors grades and return the TID for that avg grade
   * @param  string $sub_entity_id The entity id of the subcontractor reference
   * @return array                The TID associated with the avg grade
   */
  protected function getAvgGrade($sub_entity_id) {

    //get the avg the grade value
    $query = \Drupal::database()->select('paragraph__field_sub_contractor', 'sc');
    $query->addExpression('round(avg(t.field_grade_value_value))', 'avg_grade');
    $query->addJoin('inner','paragraph__field_grade','g','sc.entity_id=g.entity_id');
    $query->addJoin('inner','taxonomy_term__field_grade_value','t','g.field_grade_target_id=t.entity_id');
    $query->condition('sc.field_sub_contractor_target_id', $sub_entity_id);
    $query->condition('sc.deleted', 0);
    $query->condition('g.deleted', 0);
    $query->condition('g.deleted', 0);
    $results = $query->execute()->fetchAll();
    $avg_grade_val = $results[0]->avg_grade;

    //get the tid associated with that value
    $query = \Drupal::database()->select('taxonomy_term__field_grade_value', 't');
    $query->addField('t','entity_id');
    $query->condition('t.field_grade_value_value', $avg_grade_val);
    $query->condition('t.deleted', 0);
    $avg_tid = $query->execute()->fetchAll();
    return $avg_tid[0]->entity_id;
  }

  /**
   * Update a sub contractor record with a new average grade based on
   * all projects its associated with
   * @param  EntityInterface $entity The entity being updated
   * @return boolean                  True if action is taken
   */
  public function updateSubGrade(EntityInterface $entity) {
    if ($entity->hasField('field_sub_contractor') && $entity->hasField('field_grade')) {
      if (!$entity->get('field_grade')->isEmpty()) {
        $avg_grade = $this->getAvgGrade($entity->get('field_sub_contractor')->entity->id());
        if (!empty($avg_grade)) {
          $entity->get('field_sub_contractor')->entity->set('field_avg_grade', $avg_grade);
          $entity->get('field_sub_contractor')->entity->save();
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Create auto tasks for a project and return them
   * @param  EntityInterface $entity The entity being created
   * @return array                  An array of created task nodes
   */
  public function createReturnAutoTasks(EntityInterface $entity) {

    //only procede if these fields are present and opp is not empty
    if(!$entity->hasField('field_tasks')) {
      return;
    }

    if(!$entity->hasField('field_opportunity')) {
      return;
    }

    if ($entity->get('field_opportunity')->isEmpty()) {
      return;
    }

    $opportunity = $this->loadEntityRef($entity, 'field_opportunity');

    if (!$opportunity->hasField('field_opportunity_services_ref')) {
      return;
    }

    $services_ids = $opportunity->get('field_opportunity_services_ref');
    $new_nodes = [];
    foreach ($services_ids as $services_id) {

      $service_paragraph_id = $services_id->getValue();
      $service_paragraph = Paragraph::load(array_shift($service_paragraph_id));

      if (!$service_paragraph->hasField('field_opportunity_service')) {
        //dont even continue if the field isnt there
        return;
      }

      $service_id = $service_paragraph->get('field_opportunity_service');

      if ($service_paragraph->get('field_opportunity_service')->isEmpty()) {
        continue;
      }

      $service = $this->loadEntityRef($service_paragraph, 'field_opportunity_service');

      $auto_tasks = $service->get('field_service_tasks');

      if ($auto_tasks->isEmpty()) {
        continue;
      }
      $new_nodes[] = $this->makeTasksFromAutoTasks($auto_tasks->referencedEntities(), $entity);
    }

    //merge nodes down in case multiple services happened
    $node_merge = [];
    foreach ($new_nodes as $node) {
      $node_merge = array_merge($node_merge, $node);
    }
    return $node_merge;
  }

  /**
   * Create new tasks for a set of auto tasks
   * @param  array $auto_tasks An array of loaded autotasks
   * @param  array $entity The parent project entity
   * @return array             An array of new task nodes
   */
  protected function makeTasksFromAutoTasks($auto_tasks, EntityInterface $entity) {

    //the created nodes to return
    $new_nodes = [];

    //the values to set in the new nodes
    $node_items = [];

    //the sort order based on field_task_weight
    $order = [];

    foreach ($auto_tasks as $index => $auto_task) {

      //if the autotask disabled field is true skip it
      if($auto_task->hasField('field_disable_auto_task')) {
        if ($auto_task->field_disable_auto_task->value) {
          continue;
        }
      }

      //if assign to account mgr is true then get the
      //account manager as the task assigned to
      if ($auto_task->hasField('field_assign_to_account_manager')) {
        if ($auto_task->field_assign_to_account_manager->value) {
          $assign_to = $entity->field_account_manager->target_id;
        } else {
          $assign_to = $auto_task->field_st_assigned_to->target_id;
        }
      } else {
        $assign_to = $auto_task->field_st_assigned_to->target_id;
      }

      //calculate a due date from the date offset value
      //if the project intake date is set then offset from that
      $date = NULL;
      if ($entity->hasField('field_intake_date')) {
        if (!$entity->get('field_intake_date')->isEmpty()) {
          $intake = $entity->get('field_intake_date')->getString();
          $date_offset = !empty($auto_task->field_date_offset->value) ? $auto_task->field_date_offset->value : 0;
          $op = $date_offset > -1 ? '+' : '-' ;
          $date_string = $op . $date_offset . ' day';
          $date = date('Y-m-d', strtotime($intake . $date_string));
        }
      }

      $order[$index] = ['field_task_weight' => $auto_task->field_task_weight->value];
      $node_items[] = [
      'title' => $auto_task->title->value,
      'field_task_assigned_to' => $assign_to,
      'field_task_due_date' => $date,
      'field_task_list' => $auto_task->field_task_list->target_id,
      'field_task_status' => 2,
      'field_task_weight' => $auto_task->field_task_weight->value
      ];
    }
    //sort by field_task_weight and create them in that order
    array_multisort($node_items, SORT_ASC, SORT_NUMERIC, $order);

    return $this->createNodes($node_items, 'tasks');
  }

  /**
   * Creates a node from a keyed list of values
   * @param  array  $node_items Array of desired node items containing values keyed by field name
   * @param  string $bundle The bundle to create the node in
   * @return object         The newly created node
   */
  public function createNodes(array $node_items, string $bundle) {
    $created_nodes = [];
    foreach ($node_items as $index => $node_item) {
      $new_node = Node::create(['type' => $bundle,]);
      foreach ($node_item as $field_name => $value) {
        if (empty($value)) {
          continue;
        }

        if (!$new_node->hasField($field_name)) {
          continue;
        }

        //setTitle if its title.
        if ($field_name == 'title') {
          $new_node->setTitle($value);
        } else {
          $new_node->set($field_name, $value);
        }
      }
      $new_node->isNew();
      $new_node->save();
      $created_nodes[] = $new_node;
    }
    return $created_nodes;
  }

  /**
   * Create tasks for a new opportunity
   * @param  EntityInterface $entity The opportunity entity being created
   * @return array                  An array of new tasks created
   */
  public function createOpportunityAutoTasks(EntityInterface $entity) {

    $auto_tasks = $this->getOpportunityAutoTasks();

    if (empty($auto_tasks)) {
      return;
    }

    //prepared new task values
    $node_items = [];

    //hold order by task weight
    $order = [];
    foreach ($auto_tasks as $auto_task_id => $auto_task) {

      //if the autotask disabled field is true skip it
      if($auto_task->hasField('field_disable_auto_task')) {
        if ($auto_task->field_disable_auto_task->value) {
          continue;
        }
      }

      //calculate a due date from now + offset value
      $date_offset = !empty($auto_task->field_date_offset->value) ? $auto_task->field_date_offset->value : 0;
      $op = $date_offset > -1 ? '+' : '-' ;
      $date_string = $op . $date_offset . ' day';
      $date = date('Y-m-d');
      $date = date('Y-m-d', strtotime($date . $date_string));

      $order[$auto_task_id] = ['field_task_weight' => $auto_task->field_task_weight->value];

      $node_items[] = [
      'title' => $auto_task->title->value,
      'field_task_assigned_to' => $auto_task->field_task_assigned_to->target_id,
      'field_task_due_date' => $date,
      'field_task_list' => $auto_task->field_task_list->target_id,
      'field_task_status' => 2,
      'field_task_weight' => $auto_task->field_task_weight->value
      ];
    }
    //sort by field_task_weight and create them in that order
    array_multisort($node_items, SORT_ASC, SORT_NUMERIC, $order);

    return $this->createNodes($node_items, 'tasks');
  }

  /**
  * Query and load opportunity auto tasks
   * @return array An array of loaded opportunity auto tasks
   */
  private function getOpportunityAutoTasks() {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $query_result = $storage->getQuery()
      ->condition('status', 1)
      ->condition('type', 'opportunity_auto_tasks')
      ->execute();

    if (empty($query_result)) {
      return;
    }
    return $storage
      ->loadMultiple(array_values($query_result));
  }

  /**
   * Create new contacts based on the contacts assigned to
   * the referenced opportunity
   * @param  EntityInterface $entity The project entity
   * @return array                  an array newly created paragraph items
   */
  public function createContacts(EntityInterface $entity) {
    if (!$entity->hasField('field_project_contacts')) {
      return;
    }

    if (!$entity->hasField('field_opportunity')) {
      return;
    }
    //load the op
    $opportunity = $this->loadEntityRef($entity, 'field_opportunity');

    if (!$opportunity->hasField('field_project_contacts')) {
      return;
    }

    //iterate through contacts and make new ones for the project
    $new_pg_ids = [];
    foreach ($opportunity->get('field_project_contacts') as $contact_pg) {
      $contact_pg_id = $contact_pg->getValue();
      $contact_paragraph = Paragraph::load(array_shift($contact_pg_id));

      $pg_item = Paragraph::create(['type' => 'project_contacts',]);
      $pg_item->set('field_contact_reference',$contact_paragraph->field_contact_reference->target_id);
      $pg_item->set('field_description',$contact_paragraph->field_description->value);
      $pg_item->set('field_role',$contact_paragraph->field_role->target_id);
      $pg_item->isNew();
      $pg_item->save();
      $new_pg_ids[] = [
        'target_id' => $pg_item->id(),
        'target_revision_id' => $pg_item->getRevisionId(),
      ];
    }
    return $new_pg_ids;
  }
}
