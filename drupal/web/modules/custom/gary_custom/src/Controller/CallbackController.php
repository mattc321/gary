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
use Drupal\gary_custom\GaryFunctions;
use Drupal\views\Views;
use Drupal\Core\Url;
class CallbackController extends ControllerBase {

  /**
   * Get the price of a service
   * @param  string $sid The ID of the service entity
   * @return string      The price or zero if not found
   */
  public function getServicePrice(string $sid) {

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

  /**
   * Send notification to assignee
   * @param  string $tid The id of the task
   * @return string      0 if false 1 if True
   */
  public function getNotifyAssignee(string $tid) {

    if (empty($tid)) {
      return new Response(0);
    }

    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $task_node = $storage->load($tid);
    $helper = new GaryFunctions();
    $send = $helper->notifyAssignee($task_node);

    if ($send) {
      return new Response(1);
    } else {
      return new Response(0);
    }

  }

  /**
   * Toggle 2 elements hidden properties
   * @param  string $selector_from The first selector to toggle
   * @param  string $selector_to   The second selector to toggle
   * @return [type]                [description]
   */
  public function toggleHidden($selector_from, $selector_to) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();

    // $vid = "view-id-".$vid;
    $response->addCommand(new InvokeCommand(NULL, 'toggleHidden', [$selector_from, $selector_to]));
    return $response;
  }


}
