<?php

namespace Drupal\gary_field_formatter\Ajax;
use Drupal\Core\Ajax\CommandInterface;


class DeleteParagraph implements CommandInterface {
  protected $message;
  // Constructs a ReadMessageCommand object.
  public function __construct($message) {
    $this->message = $message;
  }
  // Implements Drupal\Core\Ajax\CommandInterface:render().
  public function render() {
    return array(
      'command' => 'refreshView',
      'mid' => $this->message
    );
  }
}
