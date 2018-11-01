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
 * Plugin implementation of the 'fc_views_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "fc_views_formatter",
 *   label = @Translation("Field Collection View Formatter"),
 *   field_types = {
 *     "field_collection"
 *   }
 * )
 */
class GaryViewsFormatter extends FormatterBase {


  /**
   * The new table id.
   *
   * @var string
   */
  protected $tableId;

  /**
   * Set the new table id
   * @param string $tid the table id string
   */
  protected function setTableId($tid) {
      $this->tableId = $tid;
  }

  /**
   * Get the new table id
   * @return string TableId
   */
  public function getTableId() {
    return $this->tableId;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Display a field collection view');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = [];

    if (empty($this->getSetting('view_machine_name'))) {
      return $elements;
    }

    // Return nothing if there are no items in this array.
    if (count($items) <= 0) {
      return $elements;
    }


    //get name of field collection (id)
    $fc_name = $items->getName();

    ////set table id
    $this->setTableId(str_replace("_","-", $fc_name).'-view-formatter');

    //load up the view
    $args = [$items->getEntity()->id()];
    $view =  \Drupal\views\Views::getView($this->getSetting('view_machine_name'));
    $view->setArguments($args);

    //load the display if one is set
    if (!empty($this->getSetting('view_display_name'))) {
      $view->setDisplay($this->getSetting('view_display_name'));
    }

    // $view->getStyle()->definition['id']= 'poop';
    $view->dom_id = $this->getTableId();
    $dom_id = 'js-view-dom-id-'.$this->getTableId();
    $view->execute();
    $elements['#theme'] = 'fc_views_formatter';

    if ($view->getStyle()->definition['id'] != 'table') {
      $elements['#error'] = 'View must be in table format';
      return $elements;
    }

    //load the entity form if ajax_inputs is true
    $form = [];
    if ($this->getSetting('ajax_inputs')) {
      $form = \Drupal::formBuilder()->getForm('Drupal\gary_field_formatter\Form\InlineForm', $items->get(0), $fc_name, $dom_id);
    }

    // $elements['#plugin_id'] = $view->getStyle()->getPluginId(); //table
    $elements['#inline_form'] = $form;
    $elements['#view'] = $view->buildRenderable();
    $elements['#theme'] = 'fc_views_formatter';

    return $elements;
  }


  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['ajax_inputs'] = [
      '#title' => $this->t('Use Ajax Inputs'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('ajax_inputs'),
    ];

    $element['view_machine_name'] = [
      '#title' => $this->t('The machine name of the view'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('view_machine_name'),
      '#required' => TRUE,
    ];
    $element['view_display_name'] = [
      '#title' => $this->t('The display name to display. Leave blank for default'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('view_display_name'),
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
      'ajax_inputs' => FALSE,
      'view_machine_name' => "",
      'view_display_name' => "",
    ] + parent::defaultSettings();
  }

  protected function viewValue(FieldItemInterface $item, $fields_to_output = []) {
    $row = [];

    return $row;
  }
}
