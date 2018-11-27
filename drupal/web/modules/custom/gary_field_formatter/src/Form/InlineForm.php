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
use Drupal\Core\Entity\EntityFieldManagerInterface;



/**
 * Contribute form.
 */
class InlineForm extends FormBase {


  protected $submittedValues;

  protected $newPcItem;

  protected $fieldDefs;

  protected $hostFieldName;

  protected $builtForm;

  protected $hostNodeId;


  private function getWholeForm(){
    return $this->builtForm;
  }

  public function getHostNodeId() {
    return $this->host_node_id;
  }

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

  /**
   * set the Field List
   * @param string $pg_name the field name of the paragraph bundle
   */
  private function setFieldDefs($pg_name) {
    $pg_item = \Drupal\paragraphs\Entity\Paragraph::create(['type' => $pg_name,]);
    $displays = EntityViewDisplay::collectRenderDisplays([$pg_item], 'full');
    $display = $displays[$pg_item->bundle()];
    foreach ($display->getComponents() as $name => $options) {
      $fields_by_weight[$options['weight']] = $name;
    }
    ksort($fields_by_weight);
    $this->fieldDefs = $fields_by_weight;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $pg_name = NULL, $host_field = NULL, $host_node_id = NULL, $dom_id = NULL) {

    $this->setFieldDefs($pg_name);
    $this->hostFieldName = $host_field;
    $this->host_node_id = $host_node_id;
    $field_defs = $this->getFieldList();

    $pg = \Drupal::service('entity_type.manager')->getStorage('paragraph')->create(array(
                    'type' => $pg_name
                )
            );
    //Get the EntityFormDisplay (i.e. the default Form Display) of this content type
    $entity_form_display = \Drupal::service('entity_type.manager')->getStorage('entity_form_display')
                                    ->load('paragraph.'.$pg_name.'.default');
    $form = [];
    $default_values = [];

    //loading the form widget will fail without this
    $form['#parents'] = [];
    foreach ($field_defs as $el_key => $field) {
        //load each widget from the field defs
        if ($widget = $entity_form_display->getRenderer($field)) { //Returns the widget class
          $items = $pg->get($field); //Returns the FieldItemsList interface
          $items->filterEmptyItems();
          $form[$field] = $widget->form($items, $form, $form_state);
        }
    }

    //set additional properties
    $form['#prefix'] = '<div id="'.$this->getFormId().'">';
    $form['#suffix'] = '</div>';
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
    $this->builtForm = $form;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

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
    //clean dirty nested arrays
    foreach ($field_list as $key => $field) {
      $i = array_search($field, array_keys($form_values));
      $sliced_array = array_map('end', array_slice($form_values, $i, 1, TRUE));
      $pg_values = array_merge($pg_values, array_map('end',$sliced_array));
    }
    $this->addPgItem($pg, $pg_name, $pg_values);
  }


  /**
   * {@inheritdoc}
   */
  public function ajaxFormRebuild(array &$form, FormStateInterface $form_state) {
    \Drupal::logger('poop')->error('ajaxFormRebuild');
    if ($form_state->hasAnyErrors()) {
      return $form;
   \Drupal::service('entity_field.manager')->getFieldDefinitions('paragraph', $pg->bundle());
      $field_list = $this->preSave();
    $field_list = $this->getFieldList();
      $def = $definitions[$field]->getDefaultValue($pg);
      if (!empty($def)) {
        // dpm($def[0]);
      }

    }

    $response = new \Drupal\Core\Ajax\AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'refreshView', [$form['#dom_id']]));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::logger('poop')->error('subtitform');
    $form_state->setRebuild();
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    return NULL;
  }

  /**
   * Add a new field collection item
   * @param array $pg      The pg host
   * @param string $pg_name   The pg name
   * @param array $pg_values Array containing submitted values
   */
  private function addPgItem($pg, $pg_name, &$pg_values) {
    //load the parent node
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($this->getHostNodeId());
    $host_field = $this->getHostFieldName(); //the pg field name on the node

    $pg_item = \Drupal\paragraphs\Entity\Paragraph::create(['type' => $pg->bundle(),]);

    foreach ($pg_values as $field_name => $value) {
      if (!trim($value) == "" || !empty($value)) {
        $pg_item->set($field_name, $value);
      }
    }

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


  protected function clearFormInput(array $form, FormStateInterface $form_state) {
    // Replace the form entity with an empty instance.
    // $pg = \Drupal::service('entity_type.manager')->getStorage('paragraph')->create(array(
    //                 'type' => $pg_name
    //             )
    //         );


    // Clear user input.
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