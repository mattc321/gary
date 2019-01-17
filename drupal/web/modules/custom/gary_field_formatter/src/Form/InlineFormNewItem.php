<?php

/**
 * @file
 * Contains \Drupal\gary_field_formatter\Form\InlineFormNewItem.
 */

namespace Drupal\gary_field_formatter\Form;


/**
 * Inline form for adding new items
 */
class InlineFormNewItem extends InlineForm {

  protected static $slug;

  public function getFormId() {
    $form_id = 'inline_pg_form_new_item_'.self::$slug;
    return $form_id;
  }

  public function __construct($field_name = NULL) {
    self::$slug = $field_name;
  }

}
