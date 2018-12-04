<?php

namespace Drupal\gary_field_formatter\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the 'paragraph_views_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "paragraph_views_formatter",
 *   label = @Translation("Paragraph View Formatter"),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class GaryViewsFormatter extends FormatterBase {


  /**
   * The new table id.
   *
   * @var string
   */
  protected $dom_id;

  public static $form_field_name;

  /**
   * Set the new table id
   * @param string $tid the table id string
   */
  protected function setDomId($did) {
      $this->dom_id = $did;
  }


  public static function getFormFieldName(){
    return self::$form_field_name;
  }


  /**
   * Get the new table id
   * @return string TableId
   */
  public function getDomId() {
    return $this->dom_id;
  }

  // public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition) {
  //   ksm($plugin_id);
  // }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Display a paragraph view');
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
    // if (count($items) <= 0) {
    //
    //   $pg_name = reset($items->getFieldDefinition()->getSettings()['handler_settings']['target_bundles']);
    // } else {
    //   $pg = \Drupal\paragraphs\Entity\Paragraph::load($items->first()->getValue()['target_id']);
    //   $pg_name = $pg->bundle();
    // }


    // $pg = \Drupal\paragraphs\Entity\Paragraph::load($items->first()->getValue()['target_id']);
    // $pg_name = $pg->bundle();
    $pg_name = reset($items->getFieldDefinition()->getSettings()['handler_settings']['target_bundles']);

    //set the static field name for the inline form to build a variable form id
    self::$form_field_name = $items->getName();

    $dom_string = str_replace("_","-",$pg_name);
    $this->setDomId($dom_string);

    $host_node_id = $items->getEntity()->id();
    //load up the view
    $args = [$host_node_id];
    $view =  \Drupal\views\Views::getView($this->getSetting('view_machine_name'));
    $view->setArguments($args);

    //load the display if one is set
    if (!empty($this->getSetting('view_display_name'))) {
      $view->setDisplay($this->getSetting('view_display_name'));
    }

    //set the dom id of the view
    $view->dom_id = $this->getDomId();

    //build the final dom id
    $final_dom_id = 'js-view-dom-id-'.$this->getDomId();

    //load the entity form if ajax_inputs is true
    $form = [];
    if ($this->getSetting('ajax_inputs')) {
      $host_field = $this->getFormFieldName();
      $form_class = [];
      if (!empty($this->getSetting('form_class'))) {
        $form_class = explode(' ', $this->getSetting('form_class'), 0);
      }
      $form = \Drupal::formBuilder()->getForm('Drupal\gary_field_formatter\Form\InlineForm', $pg_name, $host_field, $host_node_id, $final_dom_id, $form_class);
    }

    $elements['#inline_form'] = $form;

    //attach js and set domid so we know which view to refresh
    $elements['#attached']['library'][] = 'gary_field_formatter/refresh';
    $elements['#theme'] = 'paragraph_views_formatter';

    $view->execute();
    $elements['#view'] = $view->buildRenderable();


    //check if a switch view is there
    if (!empty($this->getSetting('switch_view'))) {

      $switch_view =  \Drupal\views\Views::getView($this->getSetting('switch_view'));
      $switch_view->setArguments($args);

      if (!empty($this->getSetting('switch_view_display'))) {
        $switch_view->setDisplay($this->getSetting('switch_view_display'));
      }

      $switch_view->execute();
      $elements['#switch_view'] = $switch_view->buildRenderable();
      $elements['#switch_view']['switcher'] =[
        '#title' => t('Edit Mode'),
        '#type' => 'link',
        '#attributes' => [
          'class' => 'use-ajax'
        ],
        '#url' => \Drupal\Core\Url::fromRoute('gary_field_formatter.switch_view', ['vid' => 'butt'], []),
      ];
      ksm($elements['#switch_view']);
    }


    return $elements;
  }




  public function switchViewCallback(array &$form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    $response->addCommand(new \Drupal\Core\Ajax\AlertCommand('fricking test'));
    return $response;
  }


  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['instructions'] = [
      '#title' => $this->t('How:'),
      '#description' => $this->t('Create a paragraph view, add contextual filter for parent id, enable ajax on the view.'),
      '#type' => 'item',
    ];

    $element['ajax_inputs'] = [
      '#title' => $this->t('Use Ajax Inputs'),
      '#description' => $this->t('Display an entity form to ajax submit a new paragraph item'),
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
    $element['form_class'] = [
      '#title' => $this->t('Add a custom form class'),
      '#description' => $this->t('i.e. my-form-class another-form-class'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('form_class'),
    ];
    $element['switch_view'] = [
      '#title' => $this->t('Another view name to switch with'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('switch_view'),
    ];
    $element['switch_view_display'] = [
      '#title' => $this->t('The display name of the switch view. Leave blank for default'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('switch_view_display'),
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
      'form_class' => "",
      'switch_view' => "",
      'switch_view_display' => "",
    ] + parent::defaultSettings();
  }
}
