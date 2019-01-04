<?php
/**
 * @file
 * Contains \Drupal\gary_fields\Controller\CallbackController.
 * Holds callbacks for items in gary_custom
 */
namespace Drupal\gary_fields\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\Entity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


class CallbackController extends ControllerBase {

  public function getFieldOptions($bundle) {
    if ($bundle == '_none') {
      return new Response(0);
    }

    $bundle_fields = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load('node' . '.' . $bundle . '.' . 'default')
      ->getComponents();

    $field_options = [];
    $i = 0;
    $len = count($bundle_fields);
    foreach($bundle_fields as $field_name => $bundle_field) {
      $field_config = \Drupal\field\Entity\FieldStorageConfig::loadByName('node', $field_name);
      if (!empty($field_config)) {
          if ($field_config->getType() == 'entity_reference') {
            $field_options[] = '<p>'.$field_name.'</p>';

          }
      }
    }

    return new JsonResponse($field_options);
  }

}
