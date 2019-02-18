<?php

namespace Drupal\gary_custom_views;

use Drupal\views\EntityViewsData;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeInterface;

class EntityReverseRelations extends EntityViewsData {

  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    \Drupal::logger('my_module')->notice('yo');
      dpm(1);
    return parent::createInstance($container, $entity_type);

  }

  public function getViewsData() {
  $data = parent::getViewsData();
  // ksm($data);
  // dpm(1);
  $data['node_field_data']['nid'] = [
    'title' => t('Entity Reverse Lookup'),
    'help' => t('some useful description that displays in Add Relationships popup'),
    'relationship' => [
      'field_name' => 'field_service_tasks',
      'field table' => 'node__field_service_tasks',
      'field field' => 'field_service_tasks_target_id',
      'base' => 'node_field_data',
      'base field' => 'nid',
      'id' => 'entity_reverse',
      'label' => ' views field settings popups',
    ],
  ];

  return $data;
}

}
