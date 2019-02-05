<?php
/**
 * Implements hook_views_data_alter().
 */
function gary_email_views_views_data() {
  $data['views']['email_views'] = [
    'title' => t('Email View'),
    'help' => t('Email this view with configurable options.'),
    'area' => [
      'id' => 'email_views',
    ],
  ];
  return $data;
}
