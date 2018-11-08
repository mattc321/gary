<?php

/**
 * @file
 * Contains \Drupal\gary_field_formatter\Form\InlineForm.
 */

namespace Drupal\gary_field_formatter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\gary_field_formatter\Plugin\Field\FieldFormatter;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field_collection\Controller\FieldCollectionItemController;
use Drupal\Core\Entity\EntityFormBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity;
use Drupal\Core\Ajax;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * Contribute form.
 */
class InlineForm extends FormBase {


  protected $submittedValues;

  protected $newPcItem;

  protected $fieldDefs;

  protected $hostFieldName;

  public function getFormId() {
    $form_field_name = \Drupal\gary_field_formatter\Plugin\Field\FieldFormatter\GaryViewsFormatter::getFormFieldName();
    return 'inline_pg_form_'.$form_field_name;
  }

  public function getNewPcItem() {
    return $this->newPcItem;
  }

  public function getPcValues() {
    return $this->submittedValues;
  }

  public function getFieldList() {
    return $this->fieldDefs;
  }

  public function getHostFieldName() {
    return $this->hostFieldName;
  }

  protected function setNewPgItem($item) {
    $this->newPcItem = $item;
  }


  public function getFieldDefs($pg) {
    $displays = EntityViewDisplay::collectRenderDisplays([$pg], 'full');
    $display = $displays[$pg->bundle()];
    foreach ($display->getComponents() as $name => $options) {
      $fields_by_weight[$options['weight']] = $name;
    }
    ksort($fields_by_weight);

    return $fields_by_weight;
  }

  /**
   * Load the field collection entity form
   * @param  FieldItemInterface $item    item from formatter
   * @param  string             $fc_name The name of the field collection
   * @return array                      The sliced form containing fc fields
   */
  private function GetPgForm($pg, $pg_name) {

    $fields_by_weight = $this->getFieldDefs($pg);
    $this->fieldDefs = $fields_by_weight;
    //load fc entity
    $pg_item = \Drupal\paragraphs\Entity\Paragraph::create(['type' => $pg->bundle(),]);
    $form = \Drupal::service('entity.form_builder')->getForm($pg_item);
    $sliced_form = [];
    foreach($fields_by_weight as $field_key => $field) {
        $i = array_search($field, array_keys($form));
        $sliced_form = array_merge($sliced_form, array_slice($form, $i, 1, TRUE));
    }
    return $sliced_form;
  }

  /**
   * Remove unwanted keys from form elements
   * @param  array $form The $form array
   * @return array       $form stripped of bad keys
   */
  private function cleanIt($form) {
      $bad_keys = [
        '#parents',
        '#value',
        '#id',
        '#name'
      ];

      foreach ($bad_keys as $key) {
        if (array_key_exists($key, $form)) {
          unset($form[$key]);
        }
      }
      return $form;
  }

  public function buildForm(array $form, FormStateInterface $form_state, $pg = NULL, $pg_name = NULL, $dom_id = NULL, $host_field = NULL) {

    $form_fields = $this->GetPgForm($pg, $pg_name);
    $this->hostFieldName = $host_field;
    //build new form based on field collection fields
    $form = [];
    foreach ($form_fields as $el_key => $field) {
        //init the top level of the element array
        $form[$el_key] = (isset($field['widget'][0]['value']) ? $field['widget'][0]['value'] : $field['widget'][0]['target_id']);
        $form[$el_key] = $this->cleanIt($form[$el_key]);
        ksm($form[$el_key]);
    }

    // pass these to the submit handlers
    // $form['#field_definitions'] = $fields_by_weight;
    $form['#host'] = $pg;
    $form['#field_name'] = $pg_name;
    $form['#dom_id'] = $dom_id;
    $form['submit'] = [
      '#type' => 'submit',
      '#weight' => count($form) +1,
      '#value' => t('Submit'),
      '#ajax' => [
        'callback' => '::ajaxFormRebuild',
        'wrapper' => $this->getFormId(),
      ],
    ];
    $form['#submit'] = ['::ajaxFormSubmitHandler'];
    $form['#prefix'] = '<div id="'.$this->getFormId().'">';
    $form['#suffix'] = '</div>';
    $form['#attached']['library'][] = 'gary_field_formatter/append_handler';
    // $form['#attached']['drupalSettings']['appendhandler']['target'] = $table_id;
    // ksm($form['field_role']);


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    //no additional validation needed
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxFormSubmitHandler(array &$form, FormStateInterface $form_state) {
    \Drupal::logger('poop')->error('ajaxFormSubmitHandler');
    $form_values = $form_state->getValues();
    // $host = $form['#host']->getEntity();
    $pg = $form['#host'];
    $pg_name = $form['#field_name'];
    $field_list = $this->getFieldList();

    $pg_values = [];
    foreach ($field_list as $key => $field) {
      $i = array_search($field, array_keys($form_values));
      $pg_values = array_merge($pg_values, array_slice($form_values, $i, 1, TRUE));
    }
    // ksm($pg_values);
    // $this->submittedValues = $fc_values;
    $this->addPgItem($pg, $pg_name, $pg_values);
    $input = $form_state->getUserInput();
    // We should not clear the system items from the user input.
    $clean_keys = $form_state->getCleanValueKeys();
    $clean_keys[] = 'ajax_page_state';
    foreach ($input as $key => $item) {
      if (!in_array($key, $clean_keys) && substr($key, 0, 1) !== '_') {
        unset($input[$key]);
      }
    }
    $form_state->setUserInput($input);
    // Rebuild the form state values.
    $form_state->setRebuild();
    $form_state->setStorage([]);
  }


  /**
   * {@inheritdoc}
   */
  public function ajaxFormRebuild(array &$form, FormStateInterface $form_state) {
    \Drupal::logger('poop')->error('ajaxFormRebuild');
    if ($form_state->hasAnyErrors()) {
      $form_state->setRebuild();
      return $form;
    }

    $dom_id = $form['#dom_id'];
    // $form_values = $this->prepRows($form_state->getValues());
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    // $response->addCommand(new \Drupal\Core\Ajax\AppendCommand($table_id, $form_values));
    $response->addCommand(new InvokeCommand(NULL, 'appendRow', [$dom_id]));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {

  }

  /**
   * Add a new field collection item
   * @param array $host      The field collection host
   * @param string $fc_name   The name of the parent field collection
   * @param array $fc_values Array containing submitted values
   */
  private function addPgItem($pg, $pg_name, &$pg_values) {
    // //create pg item
    $node = $pg->getParentEntity(); //the node entity
    $host_field = $this->getHostFieldName(); //the ph field name on the node
    $pg_item = \Drupal\paragraphs\Entity\Paragraph::create(['type' => $pg->bundle(),]);

    foreach ($pg_values as $field_name => $value) {
      if (!trim($value) == "" || !empty($value)) {
        $pg_item->set($field_name, $value);
      }
    }
    // $pg_item->set('field_description', 'test');

    $pg_item->isNew();
    $pg_item->save();

    // Grab any existing paragraphs from the node, and add this one
    $current = $node->get($host_field)->getValue();
    $current[] = array(
        'target_id' => $pg_item->id(),
        'target_revision_id' => $pg_item->getRevisionId(),
      );
    $node->set($host_field, $current);
    $node->save();
    return;
  }



  /**
   * Clears form input.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  protected function clearFormInput(array $form, FormStateInterface $form_state) {
    $input = $form_state->getUserInput();
    // We should not clear the system items from the user input.
    $clean_keys = $form_state->getCleanValueKeys();
    $clean_keys[] = 'ajax_page_state';
    foreach ($input as $key => $item) {
      if (!in_array($key, $clean_keys) && substr($key, 0, 1) !== '_') {
        unset($input[$key]);
      }
    }
    $form_state->setUserInput($input);
    // Rebuild the form state values.
    $form_state->setRebuild();
    $form_state->setStorage([]);
  }
}
