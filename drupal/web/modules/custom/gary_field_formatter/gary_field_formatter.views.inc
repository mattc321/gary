<?php
/**
 * Implements hook_views_data_alter().
 */
function gary_field_formatter_views_data_alter(array &$data) {
  $data['paragraphs_item']['paragraph_delete_item'] = [
    'title' => t('Paragraph Delete Item'),
    'field' => [
      'title' => t('Paragraph Delete Item'),
      'help' => t('Creates a Delete Link for Paragraph Items'),
      'id' => 'paragraph_delete_item',
    ],
  ];
  $data['node']['entity_delete_item'] = [
    'title' => t('Ajaxed Delete Item'),
    'field' => [
      'title' => t('Ajaxed Delete Item'),
      'help' => t('Creates a Delete Link for Entity Items'),
      'id' => 'entity_delete_item',
    ],
  ];
}
