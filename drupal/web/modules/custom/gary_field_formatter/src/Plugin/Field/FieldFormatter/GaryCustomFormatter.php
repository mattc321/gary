<?php

namespace Drupal\gary_field_formatter\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'fc_table_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "fc_table_formatter",
 *   label = @Translation("Custom FC Table Formatter"),
 *   field_types = {
 *     "field_collection"
 *   }
 * )
 */
class GaryCustomFormatter extends FormatterBase {


  /**
   * The new table id.
   *
   * @var string
   */
  protected $tableId;


  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays the Field Collection as a responsive table with ajax inputs');
    return $summary;
  }

  /**
     * {@inheritdoc}
     */
  protected function setTableId($tid) {
    $this->tableId = $tid;
  }

  /**
     * {@inheritdoc}
     */
  public function getTableId() {
    return $this->tableId;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    // Return nothing if there are no items in this array.
    if (count($items) <= 0) {
      return $elements;
    }

    //get name of field collection (id)
    $fc_name = $items->getName();

    // Get the table headers using the first row of field values to be rendered.
    $fields = $this->getFieldList($items->get(0));

    $elements['#header'] = array_values($fields);

    // Loop through each field value to be rendered and render it.
    foreach ($items as $delta => $item) {
      $elements['#rows'][$delta] = $this->viewValue($item, array_keys($fields));
    }
    $numItems = count($elements['#rows']);

    //set table id
    $this->setTableId(str_replace("_","-", $fc_name).'-table');

    //we only need one record to load the entity form
    //if building a custom form
    $form = \Drupal::formBuilder()->getForm('Drupal\gary_field_formatter\Form\InlineForm', $items->get(0), $fc_name, $this->getTableId());

    //vars to template
    $elements['#theme'] = 'fc_table_formatter';
    $elements['#caption'] = '';
    $elements['#colgroups'] = [];
    $elements['#sticky'] = FALSE;
    $elements['#footer'] = NULL;
    $elements['#empty'] = '';
    $elements['#no_striping'] = 0;
    $elements['#header_columns'] = $numItems;
    $elements['#inline_form'] = ($this->getSetting('use_ajax_inputs') ? $form : NULL);

    // Add classes.
    $elements['#attributes']['class'] = [
      $items->name,
      'fc-table',
    ];
    $elements['#attributes']['id'] = [
      $this->getTableId()
    ];

    // $elements['#elements'] = $elements + ['#type' => 'table'];

    // Return the data in a table.
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['use_ajax_inputs'] = [
      '#title' => $this->t('Use Ajax Inputs'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('use_ajax_inputs'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Declare a setting named 'text_length', with
      // a default value of 'short'
      'use_ajax_inputs' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * Get a list of fields and their machine names, ordered by weight.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One group of field values inside a field collection instance.
   *
   * @return array
   *   An associative array of field labels keyed by field machine names.
   */
  protected function getFieldList(FieldItemInterface $item) {
    $field_list = [];
    $fields_by_weight = [];

    // Build a list of the fields in the field collection's "full" display mode,
    // along with their weights.
    $entity = $item->getFieldCollectionItem();
    $displays = EntityViewDisplay::collectRenderDisplays([$entity], 'full');
    $display = $displays[$entity->bundle()];
    foreach ($display->getComponents() as $name => $options) {
      $fields_by_weight[$options['weight']] = $name;
    }

    // Now sort those fields by weight.
    ksort($fields_by_weight);

    // Now loop through the (weight-ordered) list, and build the final output:
    // field labels keyed by machine name.
    foreach ($fields_by_weight as $weight => $machine_name) {
      $field_list[$machine_name] = $entity->getFieldDefinition($machine_name)->getLabel();
    }

    return $field_list;
  }

  /**
   * Generate a table row corresponding to a single field collection instance.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One group of field values inside a field collection instance.
   * @param array $fields_to_output
   *   An array of field machine names, used to determine both which fields to
   *   put in a row, and what order to put them in.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item, $fields_to_output = []) {
    $row = [];

    // Get display data for the fields in the field collection.
    $entity = $item->getFieldCollectionItem();
    $displays = EntityViewDisplay::collectRenderDisplays([$entity], 'full');
    $display_components = $displays[$entity->bundle()]->getComponents();

    // For each field to output, look for that field inside the field collection
    // instance, render it without a label (since those will be in the header
    // row), and add it to the current row.
    foreach ($fields_to_output as $machine_name) {
      $display_settings = $display_components[$machine_name];
      $row[] = ['data' => $item->getFieldCollectionItem()->get($machine_name)->view([
        'label' => 'hidden',
      ] + $display_settings)];
    }

    return $row;
  }
}
