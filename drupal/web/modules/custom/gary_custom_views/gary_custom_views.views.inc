<?php
/**
 * Implements hook_views_data_alter().
 */
function gary_custom_views_views_data_alter(array &$data) {
  $data['node']['field_unit_types_count'] = [
    'title' => t('Unit Types Count'),
    'field' => [
      'title' => t('Unit Type Count'),
      'help' => t('Count unit nodes by type'),
      'id' => 'field_unit_types_count',
    ],
  ];

  $data['node']['field_next_sv'] = [
    'title' => t('Next SV'),
    'help' => t('Next open Site Visit Task title.'),
    'field' => [
      'title' => t('Next Site Visit Task'),
      'help' => t('Display title of next site visit'),
      'id' => 'field_next_sv',
    ],
  ];

  $data['node']['field_next_sv_date'] = [
    'title' => t('Next SV Date'),
    'help' => t('Next Site Visit due date.'),
    'field' => [
      'title' => t('Next Site Visit Task Date'),
      'help' => t('Display the next due date for site visit'),
      'id' => 'field_next_sv_date',
    ],
  ];

  $data['node']['field_last_comment'] = [
    'title' => t('Last Comment'),
    'help' => t('Last Comment on Entity'),
    'field' => [
      'title' => t('Last Comment'),
      'help' => t('Gary Custom - Display the last comment on the entity'),
      'id' => 'field_last_comment',
    ],
  ];

  // $data['node']['field_entity_reverse_lookup'] = [
  //   'title' => t('Entity Reverse Lookup'),
  //   'help' => t('Gets host entity'),
  //   'field' => [
  //     'title' => t('Entity Reverse Lookup'),
  //     'help' => t('Get the host entity'),
  //     'id' => 'field_entity_reverse_lookup',
  //   ],
  // ];
  // $data['node__field_service_tasks']['services']['relationship'] = [
  //   'title' => t('Services Relating to Service Task'),
  //   'label' => t('Services Relating to Service Task'),
  //   'group' => 'Services',
  //   'help' => t('Reference to service from a service task'),
  //   'id' => 'entity_reverse',
  //   'base' => 'node',
  //   'base field' => 'nid',
  //   'field' => 'entity_id',
  // ];
  //
  // $data['node']['reverse_lookups']['relationship'] = [
  //   'title' => t('Reverse Lookup For Services'),
  //   'help' => t('Reverse Lookup For Services'),
  //   'field_name' => 'field_service_tasks',
  //   'field table' => 'node__field_service_tasks',
  //   'field field' => 'field_service_tasks_target_id',
  //   'base' => 'node',
  //   'base field' => 'nid',
  //   'id' => 'entity_reverse',
  //   'label' => 'Smack the llama',
  // ];
  //  $data['node']['field_display_all_services'] = array(
  //   'title' => t('Display All Services'),
  //   'help' => t('Return all services for a project onto one record.'),
  //   'field' => array(
  //     'handler' => 'ec_app_handler_field_display_all_services',
  //   ),
  // );
  //    $data['field_data_field_todo_user_reference']['assignee_select'] = array(
  //   'title' => t('Filter by Assignee'),
  //   'group' => t('Content'),
  //   'help' => t('Add drop down feature for field_assign_to to only show users in the role assignee'),
  //   'real field' => 'field_todo_user_reference_uid',
  //   'filter' => array(
  //     'handler' => 'ec_app_handler_filter_by_assignee',
  //   ),
  // );
  // $data['node']['service_select'] = array(
  //   'title' => t('Filter by Service'),
  //   'group' => t('Content'),
  //   'help' => t('Bring service node titles into a multi choice dropdown for filtering'),
  //   'real field' => 'nid',
  //   'filter' => array(
  //     'handler' => 'ec_app_handler_filter_by_service',
  //   ),
  // );
}
