<?php

namespace Drupal\gary_comments\Form;

use Drupal\comment\CommentForm;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides ajax enhancements to core default Comment form.
 * Add submit handler to custom message tagging
 *
 * @package Drupal\gary_comments
 */
class GaryCommentsForm extends CommentForm {

  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);
  }
  public function form(array $form, FormStateInterface $form_state) {
  }
}
