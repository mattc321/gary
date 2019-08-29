<?php

/**
 * @file
 * Contains \Drupal\gary_field_formatter\Form\InlineForm.
 */

namespace Drupal\gary_field_formatter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\gary_field_formatter\Plugin\Field\FieldFormatter\GaryViewsFormatter;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\field_collection\Controller\FieldCollectionItemController;
use Drupal\Core\Entity\EntityFormBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity;
use Drupal\Core\Ajax;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;
use Drupal\gary_custom\GaryFunctions;
use Drupal\Core\Url;

/**
 * Contribute form.
 */
class InlineForm extends FormBase {


  protected $submittedValues;

  protected $newPcItem;

  protected $fieldDefs;

  protected $hostFieldName;

  protected $preBuiltForm;

  protected $hostNodeId;

  protected $newNode;

  protected $targetType;

  private $helper;

  private function getPreBuiltForm(){
    return $this->preBuiltForm;
  }

  public function getHostNodeId() {
    return $this->host_node_id;
  }

  public function getFormId() {
    $form_field_name = GaryViewsFormatter::getFormFieldName();
    $form_id = 'inline_pg_form_'.$form_field_name;
    return $form_id;
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

  public function getTargetType() {
    return $this->targetType;
  }

  protected function setNewPgItem($item) {
    $this->newPcItem = $item;
  }

  protected function __construct() {
    $this->helper = new GaryFunctions();
  }

  /**
   * set the Field List
   * @param string $pg_name The field name or the paragraph bundle
   * @param string $type The entity type
   */
  protected function setFieldDefs($pg_name, $type) {

    if ($type == 'node') {
      $pg_item = Node::create(['type' => $pg_name,]);
    }
    if ($type == 'paragraph') {
      $pg_item = Paragraph::create(['type' => $pg_name,]);
    }

    $displays = EntityFormDisplay::collectRenderDisplay($pg_item, 'default');

    foreach ($displays->getComponents() as $name => $options) {
      //for some reason langcode gets the same weight as legit fields
      //check for duplicate weights and assign a new key to avoid
      //overwriting a field key
      if (isset($fields_by_weight[$options['weight']])) {
        $getmax = max(array_keys($fields_by_weight)) + 1;
        $fields_by_weight[$getmax] = $name;
      } else {
        $fields_by_weight[$options['weight']] = $name;
      }
    }

    ksort($fields_by_weight);
    $this->fieldDefs = $fields_by_weight;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $pg_name = NULL,
        $host_field = NULL, $host_node_id = NULL, $dom_id = NULL, $form_class = NULL, $type = NULL, $keep_expanded = NULL,
      $switch_dom_id = NULL) {

    //get and set the list of field defs and vars
    $this->setFieldDefs($pg_name, $type);
    $this->hostFieldName = $host_field;
    $this->host_node_id = $host_node_id;
    $field_defs = $this->getFieldList();
    $this->targetType = $type;

    $pg = \Drupal::service('entity_type.manager')->getStorage($type)->create(array(
                    'type' => $pg_name
                )
            );
    //Get the EntityFormDisplay (i.e. the default Form Display) of this content type
    $entity_form_display = \Drupal::service('entity_type.manager')->getStorage('entity_form_display')
                                    ->load($type.'.'.$pg_name.'.default');

    //init $form
    $form = [];
    $default_values = [];
    $wrapper = 'inline_pg_form_'.$this->getHostFieldName();


    //container for add more button
    $form['add_item_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'add-item-container',
          'add-item-button-'.$dom_id
        ],
      ],
    ];

    $form['add_item_container']['add_item'] = [
      '#type' => 'button',
      '#value' => 'Add',
      '#attributes' => [
        'class' => [
          'add-pg-item'
        ],
        'data-id' => [
          $wrapper
        ],
        'onclick' => 'return false;'
      ],
    ];

    //container attach form and add button
    $form['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'pg-form-container'
      ],
    ];

    //loading the form widget will fail without this
    $form['#parents'] = [];

    foreach ($field_defs as $el_key => $field) {
        //load each widget from the field defs
        if ($widget = $entity_form_display->getRenderer($field)) { //Returns the widget class
          $items = $pg->get($field); //Returns the FieldItemsList interface
          $items->filterEmptyItems();
          $form['container'][$field] = $widget->form($items, $form, $form_state);
          //unsetting required on entity reference because its firing a false positive for an empty title
          //validation still works
          if ($form['container'][$field]['widget']['#field_name'] == 'title') {
            $form['container'][$field]['widget'][0]['value']['#required'] = FALSE;
          }
        }
    }

    //set additional properties
    $form['container']['#prefix'] = '<div id="'.$wrapper.'" class="hidden">';
    $form['container']['#suffix'] = '</div>';
    $form['#host'] = $pg;
    $form['#field_name'] = $pg_name;
    $form['#dom_id'] = $dom_id;
    $form['#switch_dom_id'] = $switch_dom_id;
    $form['#keep_expanded'] = $keep_expanded;

    //get custom form classes if there are any
    if (!empty($form_class)) {
      $form_classes=[];
      foreach ($form_class as $key => $class) {
        $form_classes[] = $class;
      }
      $form['container']['#attributes']['class'] = $form_classes;
    }

    //add multiple functionality for project units type
    if ($pg_name == 'project_units') {

      $options[0] = '-';

      for ($i = 1; $i <= 10; $i++) {
        $options[$i] = $i;
      }
      $form['container']['add_multiple'] = [
        '#type' => 'select',
        '#title' => 'Add Items',
        '#options' => $options
      ];
    }

    $form['container']['submit'] = [
      '#type' => 'submit',
      '#weight' => count($form) +1,
      '#value' => t('Save item'),
      '#attributes' => [
        'class' => [
          'use-ajax'
        ],
        'jelly' => 'check'
      ],
      '#ajax' => [
        'callback' => '::ajaxFormRebuild',
        'wrapper' => $wrapper,
      ],
    ];
    $form['#submit'] = ['::ajaxFormSubmitHandler'];
    $form['#attached']['library'][] = 'gary_field_formatter/refresh';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if (isset($form_state->getCompleteForm()['#cache_token'])) {
      $values = $form_state->getValues();
      if (isset($values['title']) && trim($values['title'][0]['value']) == '') {
          $form_state->setErrorByName('title', $this->t('This field is required!'));
       }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function ajaxFormSubmitHandler(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
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
    //add the new entity if its node
    if ($this->getTargetType() == 'node') {
      $this->addNodeItem($pg, $pg_name, $pg_values);
    }

    //add the new entity if its paragraph
    if ($this->getTargetType() == 'paragraph') {

      //if add multiple is selected then add blank rows
      if (isset($form_values['add_multiple'])) {
        if ($form_values['add_multiple'] > 0) {
          $qty = $form_values['add_multiple'];
          $this->addPgBlankItems($pg, $pg_name, $pg_values, $qty);
          return;
        }
      }
      $this->addPgItem($pg, $pg_name, $pg_values);
    }

  }


  /**
   * {@inheritdoc}
   */
  public function ajaxFormRebuild(array &$form, FormStateInterface $form_state) {
    $pg = $form['#host'];
    $form_values = $form_state->getValues();
    if ($form_state->hasAnyErrors()) {
      $wrapper = 'inline_pg_form_'.$this->getHostFieldName();

      //return the form with errors but keep it open
      $form['container']['#prefix'] = '<div id="'.$wrapper.'" class="">';
      $form['container']['#suffix'] = '</div>';
      return $form['container'];

    }

    $response = new \Drupal\Core\Ajax\AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'clearValues', ['#'.$this->getFormId()]));

    if (isset($form_values['add_multiple'])) {
      if ($form_values['add_multiple'] > 0) {
        // $currentURL = Url::fromRoute('<current>');
        // $response->addCommand(new RedirectCommand($currentURL->toString()));
        // $response->addCommand(new InvokeCommand(NULL, 'refreshView', [$form['#switch_dom_id']]));
        // $response->addCommand(new InvokeCommand(NULL, 'refreshView', [$form['#dom_id']]));
        $response->addCommand(new InvokeCommand(NULL, 'reloadJump', [$form['#dom_id'], $form['#switch_dom_id']]));
        // $response->addCommand(new InvokeCommand(NULL, 'switchView', [$form['#dom_id'], $form['#switch_dom_id']]));
        // $response->addCommand(new InvokeCommand(NULL, 'toggleElement', [$form['#dom_id'], 'hidden']));
        return $response;
      }
    }

    // $response->addCommand(new InvokeCommand(NULL, 'refreshAndHide', [$form['#dom_id'], $form['#switch_dom_id']]));
    $response->addCommand(new InvokeCommand(NULL, 'refreshView', [$form['#dom_id']]));
    // $response->addCommand(new InvokeCommand(NULL, 'switchView', [$form['#switch_dom_id'], $form['#dom_id']]));

    //if keep_expanded is false hide it
    if (!$form['#keep_expanded']) {
      $response->addCommand(new InvokeCommand(NULL, 'toggleElement', ['#'.$this->getFormId(), 'hidden']));
    }

    //if tasks then send a notification
    //had to do this here because the task gets created before its appended to a parent node
    //and sending the mail directly in the ajax response breaks the process for some reason
    // if($pg->bundle() == 'tasks') {
    //   if (!empty($this->newNode)) {
    //     $node = \Drupal::entityTypeManager()->getStorage('node')->load($this->getHostNodeId());
    //     $response->addCommand(new InvokeCommand(NULL, 'notifyAssignee', [$this->newNode->id(), $node->id()]));
    //   }
    // }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->clearErrors();
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

    //create a paragraph item
    $pg_item = Paragraph::create(['type' => $pg->bundle(),]);

    //loop through form values and add to pg item
    foreach ($pg_values as $field_name => $value) {
      if (trim($value) != "" || !empty($value)) {
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

  /**
   * Add a new field collection item
   * @param array $pg      The pg host
   * @param string $pg_name   The pg name
   * @param array $pg_values Array containing submitted values
   * @param integer $qty The qty of blank rows to add
   */
  private function addPgBlankItems($pg, $pg_name, &$pg_values, $qty) {
    //load the parent node
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($this->getHostNodeId());
    $host_field = $this->getHostFieldName(); //the pg field name on the node
    $current = $node->get($host_field)->getValue();

    $pg_items = [];
    for ($i = 1; $i<=$qty; $i++) {
      $pg_item = Paragraph::create(['type' => $pg->bundle(),]);
      $pg_item->isNew();
      $pg_item->save();

      $pg_items[] = array(
          'target_id' => $pg_item->id(),
          'target_revision_id' => $pg_item->getRevisionId(),
        );
    }
    $current = array_merge($current, $pg_items);
    $node->set($host_field, $current);
    $node->save();
    return;
  }

  private function addNodeItem($pg, $pg_name, &$pg_values) {
    //load the parent node
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($this->getHostNodeId());
    $host_field = $this->getHostFieldName(); //the pg field name on the node

    $pg_item = Node::create(['type' => $pg->bundle(),]);


    foreach ($pg_values as $field_name => $value) {
      if (trim($value) != "" || !empty($value)) {

        //exception if we dont preformat the date field value idk why
        if ($pg_item->get($field_name)->getFieldDefinition()->getType() == 'datetime') {

          $pg_item->set($field_name, $value->format('Y-m-d'));

        } else {

          //setTitle if its title. This is lame
          if ($field_name == 'title') {
            $pg_item->setTitle($value);
          } else {
            $pg_item->set($field_name, $value);
          }

        }

      }
    }

    $pg_item->isNew();
    $pg_item->save();

    $this->newNode = $pg_item;

    // Grab any existing entities from the node, and add this one
    $current = $node->get($host_field)->getValue();

    $current[] = array(
        'target_id' => $pg_item->id(),
      );
    $node->set($host_field, $current);
    $node->save();

    return;
  }

}
