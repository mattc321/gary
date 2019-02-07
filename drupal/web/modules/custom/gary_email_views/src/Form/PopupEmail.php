<?php

/**
 * @file
 * Contains \Drupal\gary_field_formatter\Form\InlineForm.
 */

namespace Drupal\gary_email_views\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\gary_field_formatter\Plugin\Field\FieldFormatter\GaryViewsFormatter;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\EntityFormBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity;
use Drupal\Core\Ajax;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;
use Drupal\gary_custom\GaryFunctions;
use Symfony\Component\HttpFoundation\Request;
use Drupal\views\Views;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use  Drupal\Core\Render\Renderer;

/**
 * Contribute form.
 */
class PopupEmail extends FormBase {

  protected $user;

  protected $view;

  public function getFormId() {
    return 'email_views_form';
  }

  public function __construct(AccountProxyInterface $user, Renderer $renderer) {
    $this->user = $user;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $parent_view = NULL, $parent_display = NULL,
   $view_id = NULL, $display_id = NULL, Request $request = NULL) {


     //build args array
     $args = [];
     if (count($request->query->getIterator()) > 1) {
       foreach ($request->query->getIterator() as $field_name => $value) {
         $args[$field_name] = $value;
       }
     }
     //unset ajax wrapper if its set
     if (!empty($args)) {
       if (isset($args['_wrapper_format'])) {
         unset($args['_wrapper_format']);
       }
     }

    //exec the view
    $view = Views::getView($view_id);
    $view->setDisplay($display_id);
    $view->setArguments($args);
    $view->execute();

    //were going to use this later
    $this->view = $view;

    //if the parent and view ids are the same were just loading the current view
    if ($parent_view == $view_id && $parent_display == $display_id) {
      $parent_view = $view;
    } else {
      $parent_view = Views::getView($parent_view);
      $parent_view->setDisplay($parent_display);
      $parent_view->execute();
    }



    //get default options
    $options = $parent_view->header['email_views']->options;

    $from = $options['use_current_users_email'] ? $this->user->getEmail() : $options['from_alias'];

    $form = [];
    $form['email_to'] = [
      '#title' => t('Email To:'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $options['default_recipient'],
    ];
    $form['email_from'] = [
      '#title' => t('Email (alias) From:'),
      '#type' => 'textfield',
      '#description' => t('Leave blank to use system email'),
      '#default_value' => $from,
    ];
    $form['email_subject'] = [
      '#title' => t('Subject:'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $options['default_subject'],
    ];
    $form['body_msg'] = [
      '#title' => t('Message'),
      '#description' => t('The view will be embedded at the end of this email.'),
      '#type' => 'textarea',
      '#required' => TRUE,
      '#default_value' => $options['default_body'],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Send'),
      '#attributes' => [
        'class' => [
          'use-ajax'
        ],
      ],
      '#ajax' => [
        'callback' => '::ajaxFormRebuild',
        'wrapper' => 'test',
      ],
    ];
    $form['#submit'] = ['::ajaxFormSubmitHandler'];
    // $form['#attached']['library'][] = 'gary_field_formatter/refresh';
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxFormSubmitHandler(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();

    //do the stuff
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxFormRebuild(array &$form, FormStateInterface $form_state) {

    if ($form_state->hasAnyErrors()) {
      return $form;
    }

    $response = new AjaxResponse();
    $response->addCommand(new AlertCommand('fuck yea');
    // $response->addCommand(new InvokeCommand(NULL, 'clearValues', ['#'.$this->getFormId()]));


    return $response;
  }
  /**
 * {@inheritdoc}
 */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}
