<?php
/**
 * @file
 * Contains \Drupal\gary_field_formatter\Controller\GaryFormatterController.
 */
namespace Drupal\gary_field_formatter\Controller;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\gary_field_formatter\Ajax\DeleteParagraph;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Ajax;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\node\Entity\Node;


class GaryFormatterController extends ControllerBase {


  /**
   * Delete a paragraph item by id
   * @param string $pid Paragraph Id
   * @param string $vid The view id to refresh
   * @return AjaxResponse
   */
  public function DeleteEntityParagraph($pid, $vid) {
    $storage_handler = \Drupal::entityTypeManager()->getStorage('paragraph');
    $paragraph = $storage_handler->load($pid);

    //if $paragraph is null item doesnt exist just refresh view
    if (empty($paragraph)) {
      $response = new AjaxResponse();
      $response->addCommand(new InvokeCommand(NULL, 'refreshView', [$vid]));

      return $response;
    }

    $parent_field_name = $paragraph->parent_field_name->value;
    $node = $paragraph->getParentEntity(); //the node entity

    //delete paragraph item
    $paragraph->delete();

    // Grab any existing paragraphs from the node, and unset the one deleted
    $items = $node->get($parent_field_name)->getValue();
    foreach ($items as $key => $item) {
      if ($item['target_id'] == $pid) {
        unset($items[$key]);
      }
    }
    $node->set($parent_field_name, $items);
    $node->save();

    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'refreshView', [$vid]));

    //if we are deleting a service we need to refresh the total amount in the dom
    if ($paragraph->getType() == 'opportunity_services') {
      $amount = $node->field_amount->value;
      $response->addCommand(new InvokeCommand(NULL, 'refreshTotalAmount', [$amount]));
    }

    return $response;
  }

  /**
   * Delete an entity item by id
   * @param string $pid Entity Id
   * @param string $vid The view id to refresh
   * @param string $host_id The host id containing the relationship if there is one
   * @param string $host_field The host field containing the entity references
   */
  public function DeleteEntityItem($pid, $vid, $host_id = NULL, $host_field = NULL) {
    $storage_handler = \Drupal::entityTypeManager()->getStorage('node');
    $entity = $storage_handler->load($pid);

    //if $entity is null item doesnt exist just refresh view
    if (empty($entity)) {
      $response = new AjaxResponse();
      $response->addCommand(new InvokeCommand(NULL, 'refreshView', [$vid]));

      return $response;
    }

    //delete paragraph item
    $entity->delete();

    //if the host id and host field have values, this is a referenced field
    //load the parent and unset the deleted referenced entity
    if ($host_id != 0 && $host_field != 'none' ) {
      $parent_node = Node::load($host_id);

      // Grab any existing references from the node, and unset the one deleted
      $items = $parent_node->get($host_field)->getValue();
      foreach ($items as $key => $item) {
        if ($item['target_id'] == $pid) {
          unset($items[$key]);
        }
      }
      $parent_node->set($host_field, $items);
      $parent_node->save();
    }

    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'refreshView', [$vid]));

    return $response;
  }


  /**
   * Toggle between views
   * @param string $vid_from The view id to hide
   * @param string $vid_to   The view id to unhide
   */
  public function SwitchView($vid_from, $vid_to) {
    $response = new AjaxResponse();

    // $vid = "view-id-".$vid;
    $response->addCommand(new InvokeCommand(NULL, 'switchView', [$vid_from, $vid_to]));
    //dont display the add button when switching the view, too many ux issues
    $response->addCommand(new InvokeCommand(NULL, 'toggleElement', ['.'.'add-item-button-'.$vid_from, 'hidden']));
    return $response;
  }


  /**
   * Toggle an element by property
   * @param string $selector The element to select
   * @param string $property The property to toggle
   */
  public function ToggleElement($selector, $property) {
    $response = new AjaxResponse();

    // $vid = "view-id-".$vid;
    $response->addCommand(new InvokeCommand(NULL, 'toggleElement', [$selector, $property]));
    return $response;
  }

}
