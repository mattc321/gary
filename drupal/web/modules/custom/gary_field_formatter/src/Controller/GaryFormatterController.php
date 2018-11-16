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

  public function DeleteEntityParagraph($pid) {
    $storage_handler = \Drupal::entityTypeManager()->getStorage('paragraph');
    $paragraph = $storage_handler->load($pid);
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
    $response->addCommand(new InvokeCommand(NULL, 'refreshView', []));

    return $response;
  }

}
