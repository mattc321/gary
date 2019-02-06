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
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;
use Drupal\gary_custom\GaryFunctions;

/**
 * Contribute form.
 */
class PopupEmail extends FormBase {

  public function getFormId() {
    $form_id = 'email_views_form';
    return $form_id;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = [];

    $form['email_to'] = [
      '#title' => t('Email To:'),
      '#type' => 'textfield',
      '#default_value' => $form_state['def_recipient'],
    ];
    $form['email_from'] = [
      '#title' => t('Email (alias) From:'),
      '#type' => 'textfield',
      '#default_value' => $form_state['from_alias'],
    ];
    $form['email_subject'] = [
      '#title' => t('Subject:'),
      '#type' => 'textfield',
      '#default_value' => $form_state['subject'],
    ];
    $form['body_msg'] = [
      '#title' => t('Message'),
      '#description' => t('The view will be embedded at the end of this email.'),
      '#type' => 'textarea',
      '#default_value' => $form_state['body_msg'],
    ];

    $form['container']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
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


  }


}
