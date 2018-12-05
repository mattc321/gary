<?php
/**
 * @file
 * Contains \Drupal\gary_field_formatter\Controller\GaryFormatterController.
 */
namespace Drupal\gary_field_formatter\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\gary_field_formatter\Ajax\DeleteParagraph;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Ajax;
use Drupal\Core\Ajax\InvokeCommand;


class GaryFormatterController extends ControllerBase {


  /**
   * Delete a paragraph item by id
   * @param string $pid Paragraph Id
   * @param string $vid The view id to refresh
   */
  public function DeleteEntityParagraph($pid, $vid) {
    $storage_handler = \Drupal::entityTypeManager()->getStorage('paragraph');
    $paragraph = $storage_handler->load($pid);

    //if $paragraph is null item doesnt exist just refresh view
    if (empty($paragraph)) {
      $response = new \Drupal\Core\Ajax\AjaxResponse();
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

    $response = new \Drupal\Core\Ajax\AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'refreshView', [$vid]));

    return $response;
  }


  public function SwitchView($vid_from, $vid_to) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();

    // $vid = "view-id-".$vid;
    $response->addCommand(new InvokeCommand(NULL, 'switchView', [$vid_from, $vid_to]));
    return $response;
  }


}
