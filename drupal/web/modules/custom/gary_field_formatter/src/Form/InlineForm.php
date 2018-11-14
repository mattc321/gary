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
  private function GetPgForm($pg_name) {

    $pg_item = \Drupal\paragraphs\Entity\Paragraph::create(['type' => $pg_name,]);
    $fields_by_weight = $this->getFieldDefs($pg_item);
    $this->fieldDefs = $fields_by_weight;
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

  public function buildForm(array $form, FormStateInterface $form_state, $pg_name = NULL, $host_field = NULL, $host_node_id = NULL) {

    $form_fields = $this->GetPgForm($pg_name);
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
    $form['#parents'] = [];
    foreach ($field_defs as $el_key => $field) {
        //init the top level of the element array
        // $form[$el_key] = (isset($field['widget'][0]['value']) ? $field['widget'][0]['value'] : $field['widget'][0]['target_id']);
        // $form[$el_key] = $this->cleanIt($form[$el_key]);
        // $form[$el_key]['#attributes']['class'][] = 'ajax-processed';
        // ksm($form[$el_key]);
        //Get the body field widget and add it to the form

        if ($widget = $entity_form_display->getRenderer($field)) { //Returns the widget class
          $items = $pg->get($field); //Returns the FieldItemsList interface
          $items->filterEmptyItems();
          $form[$field] = $widget->form($items, $form, $form_state);
          // ksm($form[$field]['widget'][0]);
        }
    }
    // ksm($form);

    //set additional properties
    $form['#prefix'] = '<div id="'.$this->getFormId().'">';
    $form['#suffix'] = '</div>';
    $form['#host'] = $pg;
    $form['#field_name'] = $pg_name;
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
    }
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'refreshView', []));
    // $response->addCommand(new \Drupal\Core\Ajax\AppendCommand('#'.$this->getFormId(),$this->getWholeForm()));
    // ksm($this->getWholeForm());
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
   * @param array $host      The field collection host
   * @param string $fc_name   The name of the parent field collection
   * @param array $fc_values Array containing submitted values
   */
  private function addPgItem($pg, $pg_name, &$pg_values) {
    // //create pg item
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($this->getHostNodeId());
    $host_field = $this->getHostFieldName(); //the pg field name on the node

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
}
