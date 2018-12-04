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
}
