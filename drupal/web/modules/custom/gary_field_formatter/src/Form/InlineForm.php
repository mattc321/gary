<?php

/**
 * @file
 * Contains \Drupal\gary_field_formatter\Form\InlineForm.
 */

namespace Drupal\gary_field_formatter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\gary_field_formatter\Plugin\Field\FieldFormatter\GaryCustomFormatter;
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

  protected $newFcItem;

  protected $fieldDefs;

  public function getFormId() {
    return 'inline_pg_form';
  }

  public function getNewFcItem() {
    return $this->newFcItem;
  }

  public function getFcValues() {
    return $this->submittedValues;
  }

  public function getFieldList() {
    return $this->fieldDefs;
  }

  protected function setNewFcItem($item) {
    $this->newFcItem = $item;
  }


  public function getFieldDefs($pg) {
    $displays = EntityViewDisplay::collectRenderDisplays([$pg], 'full');
    $display = $displays[$pg->bundle()];
    foreach ($display->getComponents() as $name => $options) {
      $fields_by_weight[$options['weight']] = $name;
    }
    ksort($fields_by_weight);
    $this->fieldDefs = $fields_by_weight;
    return $fields_by_weight;
  }

  /**
   * Load the field collection entity form
   * @param  FieldItemInterface $item    item from formatter
   * @param  string             $fc_name The name of the field collection
   * @return array                      The sliced form containing fc fields
   */
  // private function getFCForm(FieldItemInterface $item, $fc_name) {
  //
  //   $fields_by_weight = $this->getFieldDefs($item);
  //   //load fc entity
  //   $fieldCollection = \Drupal\field_collection\Entity\FieldCollection::load($fc_name);
  //
  //
  //   $field_collection_item = \Drupal::entityTypeManager()
  //     ->getStorage('field_collection_item')
  //     ->create([
  //       'field_name' => $fieldCollection->id(),
  //       'host_type' => 'node',
  //       'revision_id' => 0,
  //     ]);
  //
  //   $form = \Drupal::service('entity.form_builder')->getForm($field_collection_item);
  //
  //   $form['actions']['submit']['#submit']=['::ajaxFormSubmitHandler'];
  //   $form['#prefix'] = '<div id="inline_fc_form">';
  //   $form['#suffix'] = '</div>';
  //   $form['actions']['submit']['#ajax_processed'] = TRUE;
  //   $form['#validate'] = ['::validateForm'];
  //   $form['#submit'] = ['::ajaxFormSubmitHandler'];
  //
  //
  //   $sliced_form = [];
  //   foreach($fields_by_weight as $field_key => $field) {
  //       $i = array_search($field, array_keys($form));
  //       $sliced_form = array_merge($sliced_form, array_slice($form, $i, 1, TRUE));
  //   }
  //
  //   return $sliced_form;
  // }

  /**
   * Remove unwanted keys from form elements
   * @param  array $form The $form array
   * @return array       $form stripped of bad keys
   */
  // private function cleanIt($form) {
  //     $bad_keys = [
  //       '#parents',
  //       '#value',
  //       '#id',
  //       '#name'
  //     ];
  //
  //     foreach ($bad_keys as $key) {
  //       if (array_key_exists($key, $form)) {
  //         unset($form[$key]);
  //       }
  //     }
  //     return $form;
  // }

  public function buildForm(array $form, FormStateInterface $form_state, $pg = NULL, $pg_name = NULL, $dom_id = NULL) {

    // $form_fields = $this->getFCForm($item, $fc_name);
    $form=[];
    $fields_by_weight = $this->getFieldDefs($pg);

    // $entity_form = \Drupal::service('entity.form_builder')->getForm($pg);
    $pg_item = \Drupal\paragraphs\Entity\Paragraph::create(['type' => $pg->bundle(),]);
    // ksm($pg_item);
    $entity_form = \Drupal::service('entity.form_builder')->getForm($pg_item);

    ksm($entity_form['field_contact_reference']['widget'][0]);
    // ksm(\Drupal::service('entity.form_builder')->getForm($pg));
    //build new form based on field collection fields
    // $form = [];


    $element = [];
    foreach ($fields_by_weight as $key => $field) {
        // dpm($key);
        //init the top level of the element array
        // $form[$field] = entity_get_form_display('paragraph', 'project_contacts', 'default')->getComponent($field);
        $el = \Drupal::entityTypeManager()
          ->getStorage('entity_form_display')
          ->load('paragraph' . '.' . 'project_contacts' . '.' . 'default');
          // $ff = \Drupal::formBuilder()->getForm($entity_form);
        // ksm($el->getRenderer($field)->getPluginDefinition());

        // $renderer = \Drupal::service('renderer');
        // ksm($renderer->renderPlain($el->getComponent($field)));
        // $wtf = \Drupal\Core\Render\Renderer::renderPlain($field);
        // ksm($wtf);
        // $delta = 0;
        // $element = [
        //   '#title' => $el->getLabel(),
        //   '#description' => 'test'
        // ];
        // ksm($element);
        // $form[$field] = $this->cleanIt($form[$el_key]);
        // $settings = entity_get_form_display('paragraph', 'project_contacts', 'default')->getComponent($field);
    }
    // ksm(\Drupal::formBuilder()->getForm($el));
    //
    // //pass these to the submit handlers
    // $form['#field_definitions'] = $fields_by_weight;
    // $form['#host'] = $item;
    // $form['#field_name'] = $fc_name;
    // $form['#table_id'] = $table_id;
    // $form['submit'] = [
    //   '#type' => 'submit',
    //   '#weight' => count($form) +2,
    //   '#value' => t('Submit'),
    //   '#ajax' => [
    //     'callback' => '::ajaxFormRebuild',
    //     'wrapper' => 'inline_fc_form',
    //   ],
    // ];
    //
    // $form['#submit'] = ['::ajaxFormSubmitHandler'];
    // $form['#prefix'] = '<div id="'.$this->getFormId().'">';
    // $form['#suffix'] = '</div>';

    // $form['#attached']['library'][] = 'gary_field_formatter/append_handler';
    // $form['#attached']['drupalSettings']['appendhandler']['target'] = $table_id;

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
  public function ajaxFormRebuild(array &$form, FormStateInterface $form_state) {

    // if ($form_state->hasAnyErrors()) {
    //   return $form;
    // }
    //
    // $table_id = $form['#table_id'];
    // $form_values = $this->prepRows($form_state->getValues());
    // $response = new \Drupal\Core\Ajax\AjaxResponse();
    // // $response->addCommand(new \Drupal\Core\Ajax\AppendCommand($table_id, $form_values));
    // $response->addCommand(new InvokeCommand(NULL, 'appendRow', [$form_values, $table_id]));
    // return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxFormSubmitHandler(array &$form, FormStateInterface $form_state) {
    // \Drupal::logger('poop')->error('ajaxFormSubmitHandler');
    // $form_values = $form_state->getValues();
    // $host = $form['#host']->getEntity();
    // $fc_name = $form['#field_name'];
    // $field_list = $form['#field_definitions'];
    //
    // $fc_values = [];
    // foreach ($field_list as $key => $field) {
    //   $i = array_search($field, array_keys($form_values));
    //   $fc_values = array_merge($fc_values, array_slice($form_values, $i, 1, TRUE));
    // }
    // $this->submittedValues = $fc_values;
    // $this->addFCItem($host, $fc_name, $fc_values);
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
    return NULL;
  }

  /**
   * Add a new field collection item
   * @param array $host      The field collection host
   * @param string $fc_name   The name of the parent field collection
   * @param array $fc_values Array containing submitted values
   */
  private function addFCItem($host, $fc_name, &$fc_values) {
    //create pg item
    // $pg = \Drupal\paragraphs\Entity\Paragraph::load($items->getValue()[0]['target_id']);
    //
    // $node = $items->getEntity();
    // $pg_item = \Drupal\paragraphs\Entity\Paragraph::create(['type' => $pg->bundle(),]);
    // $pg_item->set('field_description', 'test');
    // $pg_item->isNew();
    // $pg_item->save();

    // Grab any existing paragraphs from the node, and add this one
    // $current = $node->get('field_project_contacts')->getValue();
    // $current[] = array(
    //     'target_id' => $pg_item->id(),
    //     'target_revision_id' => $pg_item->getRevisionId(),
    //   );
    // $node->set('field_project_contacts', $current);
    // $node->save();
  }


  /**
   * Prepare the row using the newly added fc item
   * @return string html row
   */
  private function prepRows() {

    $item = $this->getNewFcItem();
    $field_list = $this->getFieldList();

    $source = \Drupal::entityManager()->getStorage('field_collection_item')->load($item->id());
    $view_builder = \Drupal::entityManager()->getViewBuilder('field_collection_item');

    $html = '<tr>';
    foreach ($field_list as $field_name) {
      if ($source->hasField($field_name) && $source->access('view')) {
          $f = $source->$field_name->view(['label' => 'hidden']);
          if ($source->$field_name->isEmpty()) {
            $field_string = '';
          } else {
            $field_string =  \Drupal::service('renderer')->renderRoot($f)->__toString();
          }
          $html .= '<td>' . $field_string . '</td>';
      }
    }
    $html .= '</tr>';
    return $html;
  }

}
