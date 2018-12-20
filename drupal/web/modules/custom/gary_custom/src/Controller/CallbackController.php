<?php
/**
 * @file
 * Contains \Drupal\gary_field_formatter\Controller\CallbackController.
 * Holds callbacks for items in gary_custom
 */
namespace Drupal\gary_custom\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\Entity;
use Symfony\Component\HttpFoundation\Response;


class CallbackController extends ControllerBase {

  /**
   * Get the price of a service
   * @param  string $sid The ID of the service entity
   * @return string      The price or zero if not found
   */
  public function getServicePrice($sid) {

    if ($sid == '_none') {
      return new Response(0);
    }

    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $service_entity = $storage->load($sid);
    $price = 0;
    if ($service_entity->hasField('field_unit_price')) {
      $price = $service_entity->get('field_unit_price')->value;
    }

    return new Response($price);
  }

}
