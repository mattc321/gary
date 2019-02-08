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
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;
use Drupal\gary_custom\GaryFunctions;
use Symfony\Component\HttpFoundation\Request;
use Drupal\views\Views;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use  Drupal\Core\Render\Renderer;
use Drupal\Core\Render\Element\StatusMessages;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Mail\MailManager;

/**
 * Contribute form.
 */
class PopupEmail extends FormBase {

  protected $user;

  protected $view;

  /**
  * The Messenger service.
  *
  * @var \Drupal\Core\Messenger\MessengerInterface
  */
  protected $messenger;

  /**
  * The Mailer service.
  *
  * @var \Drupal\Core\Mail\MailManager
  */
  protected $mailer;


  public function getFormId() {
    return 'email_views_form';
  }

  public function __construct(AccountProxyInterface $user, Renderer $renderer, MessengerInterface $messenger,
    MailManager $mailer) {
    $this->user = $user;
    $this->renderer = $renderer;
    $this->messenger = $messenger;
    $this->mailer = $mailer;
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
      $container->get('renderer'),
      $container->get('messenger'),
      $container->get('plugin.manager.mail')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $parent_view = NULL, $parent_display = NULL,
   $view_id = NULL, $display_id = NULL, Request $request = NULL) {


    //if the parent and view ids are the same were just loading the current view
    if ($parent_view == $view_id && $parent_display == $display_id) {
      //build args array
      $args = [];
      if (count($request->query->getIterator()) > 1) {
        foreach ($request->query->getIterator() as $field_name => $value) {
          $args[$field_name] = $value;
        }
      }
      unset($args['_wrapper_format']);
      unset($args['field_account_reference_target_id']);
      $args['field_account_reference_target_id'] = [2494];
      //unset ajax wrapper if its set
      // if (!empty($args)) {
      //   if (isset($args['_wrapper_format'])) {
      //     unset($args['_wrapper_format']);
      //     unset($args['field_account_reference_target_id']);
      //   }
      // }
      ksm($args);
      //exec the view
      $view = Views::getView($view_id);
      $view->setDisplay($display_id);
      $view->setArguments($args);
      $view->execute();

      $parent_view = $view;

    //if theyre not the same, load the parent view and match filter arguments
    } else {

      $parent_view = Views::getView($parent_view);
      $parent_view->setDisplay($parent_display);
      $parent_view->execute();

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
    }

    //were going to use this later
    $this->view = $view;

    // ksm($view->filter);
    // ksm($parent_view->filter);



    //get default options
    $options = $parent_view->header['email_views']->options;

    $from = $options['use_current_users_email'] ? $this->user->getEmail() : $options['from_alias'];

    $form = [];
    $form['status'] = [
      '#markup' => '<div id="form-status-messages"></div>'
    ];
    $form['email_to'] = [
      '#title' => t('Email To:'),
      '#type' => 'email',
      '#required' => TRUE,
      '#default_value' => $options['default_recipient'],
    ];
    $form['email_from'] = [
      '#title' => t('Email (alias) From:'),
      '#type' => 'email',
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
        'wrapper' => $this->getFormId(),
      ],
    ];
    $form['#submit'] = ['::ajaxFormSubmitHandler'];
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxFormSubmitHandler(array &$form, FormStateInterface $form_state) {

    $form_values = $form_state->getValues();
    // ksm($form_values['body_msg']);
    $params = [
      'email_to' => $form_values['email_to'],
      'email_from' => $form_values['email_from'],
      'email_subject' => $form_values['email_subject'],
      'body_msg' => $form_values['body_msg'],
      'rendered_view' => $this->renderer->renderPlain($this->view->buildRenderable())
    ];

    $module = 'gary_email_views';
    $key = 'email_view';
    $params['values'] = $params;
    $langcode = $this->user->getPreferredLangcode();
    $send = TRUE;
    $result = $this->mailer->mail($module, $key, $form_values['email_to'], $langcode, $params, NULL, $send);
    if ($result['result'] !== TRUE) {
      $this->messenger
        ->addMessage('The email could not be sent. Contact site admin', $this->messenger::TYPE_WARNING);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxFormRebuild(array &$form, FormStateInterface $form_state) {
    \Drupal::logger('this')->error('ajaxFormRebuild');

    //respond with any form errors
    if ($form_state->hasAnyErrors()) {
      $response = new AjaxResponse();
      $messages = StatusMessages::renderMessages('error');
      $response
        ->addCommand(new HtmlCommand('#form-status-messages', $this->renderer->renderPlain($messages)));
      return $response;
    }

    $response = new AjaxResponse();
    $this->messenger
      ->addMessage(t('Email Sent!'), $this->messenger::TYPE_STATUS);
    $response->addCommand(new CloseModalDialogCommand());

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
