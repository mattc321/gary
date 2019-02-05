<?php

namespace Drupal\gary_email_views\Plugin\views\area;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\area\AreaPluginBase;
/**
 * A special handler to add emailing the view functionality.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("email_views")
 */
class EmailViews extends AreaPluginBase {

  /**
    * {@inheritdoc}
    */
   protected function defineOptions() {
     $options = parent::defineOptions();
     $options['alternate_view_id'] = [
       'default' => '',
     ];
     $options['link_text'] = [
       'default' => 'Email This List',
     ];
     $options['from_alias'] = [
       'default' => '',
     ];
     $options['use_current_users_email'] = [
       'default' => TRUE,
     ];
     $options['default_subject'] = [
       'default' => '',
     ];
     $options['default_recipient'] = [
       'default' => '',
     ];
     $options['default_body'] = [
       'default' => '',
     ];

     return $options;
   }

   /**
    * {@inheritdoc}
    */
   public function buildOptionsForm(&$form, FormStateInterface $form_state) {
     parent::buildOptionsForm($form, $form_state);

     $form['alternate_view_id'] = [
       '#title' => $this
         ->t('Email a Different View'),
       '#type' => 'textfield',
       '#description' => $this->t('You can optionally embed a different view or display other than this one.
          The Email Link will still be visible on this display.
          If this field is populated it will be the only view sent in the email.(syntax: view_name|display_name)'),
       '#default_value' => $this->options['alternate_view_id']
     ];

     $form['link_text'] = [
       '#title' => $this
         ->t('Link Text'),
       '#type' => 'textfield',
       '#required' => TRUE,
       '#description' => $this->t('The clickable link that will display in the view header'),
       '#default_value' => $this->options['link_text']
     ];

     $form['from_alias'] = [
       '#title' => $this
         ->t('Default From: Alias'),
       '#type' => 'textfield',
       '#description' => $this->t('Override the from address here instead of using site email address.
          Leave blank to use "no-reply@"'),
       '#default_value' => $this->options['from_alias']
     ];

     $form['use_current_users_email'] = [
       '#title' => $this
         ->t('Use the Current User\'s Email'),
       '#type' => 'checkbox',
       '#required' => TRUE,
       '#description' => $this->t('Use the currently logged in user\'s email address as the senders email by default.'),
       '#default_value' => $this->options['use_current_users_email']
     ];

     $form['default_subject'] = [
       '#title' => $this
         ->t('Default Subject'),
       '#type' => 'textfield',
       '#description' => $this->t('Subject text to appear by default. Users will still be prompted for a subject.'),
       '#default_value' => $this->options['default_subject']
     ];

     $form['default_recipient'] = [
       '#title' => $this
         ->t('Default To: Recipient'),
       '#type' => 'textfield',
       '#description' => $this->t('Email address for the default recipient(s) separated by comma. Users will still be prompted for an email.'),
       '#default_value' => $this->options['default_recipient']
     ];

     $form['default_body'] = [
       '#title' => $this
         ->t('Default Body Message'),
       '#type' => 'textarea',
       '#description' => $this->t('The default message used in the email body before the view is inserted.
          You may use HTML and inline CSS'),
       '#default_value' => $this->options['default_body']
     ];

   }

   /**
    * {@inheritdoc}
    */
   public function render($empty = FALSE) {


     if (!$empty || !empty($this->options['empty'])) {

      $build['#attached']['library'][] = 'gary_email_views/emailview';

      $output = '<div class="use-ajax email-view-link">';
      $output .= $options['link_text'];
      $output .= '</div>';

      $build['markup'] = [
        '#markup' => $output,
      ];

      return $build;
     }
     return [];
   }
}
