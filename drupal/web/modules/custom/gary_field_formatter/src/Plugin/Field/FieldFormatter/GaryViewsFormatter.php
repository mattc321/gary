<?php

//Formatter for paragraphs

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
 *   label = @Translation("Entity View Formatter"),
 *   field_types = {
 *     "entity_reference_revisions",
 *     "entity_reference"
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
    $summary[] = $this->t('Display a paragraph or entity view');
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

    //get entity type and fieldname
    $type = $items->getFieldDefinition()->getSettings()['target_type'];
    $pg_name = reset($items->getFieldDefinition()->getSettings()['handler_settings']['target_bundles']);

    //only continue if type is pg or node
    if ($type != 'paragraph' && $type != 'node') {
      $error = t('This field formatter only supports entities of type Paragraph or Node. Field @pgname is of type @type', ['@pgname' => $pg_name, '@type' => $type]);
      $messenger = \Drupal::messenger();
      $messenger->addMessage($error, $messenger::TYPE_WARNING);
      return $elements;
    }

    //set the static field name for the inline form to build a variable form id
    self::$form_field_name = $items->getName();

    $host_node_id = $items->getEntity()->id();
    //load up the view
    $args = [$host_node_id];
    $view =  \Drupal\views\Views::getView($this->getSetting('view_machine_name'));
    $view->setArguments($args);

    //load the display if one is set
    if (!empty($this->getSetting('view_display_name'))) {
      $view->setDisplay($this->getSetting('view_display_name'));
    }

    //create a unique dom id and set it
    $display_id = (!empty($view->current_display) ? $view->current_display : $view->getDisplay()->getPluginId());
    $dom_string = str_replace("_","-",$view->id()) . "-" . $display_id;

    //store it
    $this->setDomId($dom_string);

    //set the dom id of the view
    $view->dom_id = $this->getDomId();

    //build the final dom id
    $final_dom_id = 'js-view-dom-id-'.$this->getDomId();

    $view->execute();
    $elements['#view'] = $view->buildRenderable();


    //init $switch_dom_id
    $switch_dom_id = "";
    //check if a switch view is there
    if (!empty($this->getSetting('switch_view'))) {

      $switch_view =  \Drupal\views\Views::getView($this->getSetting('switch_view'));
      $switch_view->setArguments($args);

      if (!empty($this->getSetting('switch_view_display'))) {
        $switch_view->setDisplay($this->getSetting('switch_view_display'));
      }

      $switch_view->execute();

      //set hidden class to hide by default
      $switch_view->getDisplay()->setOption('css_class', 'hidden');

      //create a unique dom id and set it
      $switch_display_id = $switch_view->current_display;
      $switch_dom_string = str_replace("_","-",$pg_name) . "-" . $switch_display_id;

      //set the dom id
      $switch_view->dom_id = $switch_dom_string;

      //build the final dom id
      $switch_dom_id = 'js-view-dom-id-'.$switch_dom_string;

      $elements['#switch_view'] = $switch_view->buildRenderable();
      $elements['#switch_view']['switch_container'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'switch-link-container'
          ],
        ],
      ];

      //link text from settings
      $link_text = (!empty($this->getSetting('switch_view_link_text')) ? $this->getSetting('switch_view_link_text') : "");

      $elements['#switch_view']['switch_container']['switcher'] = [
        '#title' => t($link_text),
        '#type' => 'link',
        '#attributes' => [
          'class' => [
            'use-ajax',
            'switch-link'
          ],
        ],
        '#url' => \Drupal\Core\Url::fromRoute('gary_field_formatter.switch_view', [
                        'vid_from' => $final_dom_id,
                        'vid_to' => $switch_dom_id], []),
      ];
    }

    //load the entity form if ajax_inputs is true
    $form = [];
    if ($this->getSetting('ajax_inputs')) {
      $host_field = $this->getFormFieldName();
      $form_class = [];
      if (!empty($this->getSetting('form_class'))) {
        $form_class = explode(' ', $this->getSetting('form_class'), 0);
      }

      //keep form expanded?
      $keep_expanded = $this->getSetting('keep_form_expanded');

      //load the form

      $form = \Drupal::formBuilder()
        ->getForm('Drupal\gary_field_formatter\Form\InlineForm', $pg_name, $host_field,
        $host_node_id, $final_dom_id, $form_class, $type, $keep_expanded);

      $elements['#inline_form']['container']['form'] = $form;

    }
    //attach js and set domid so we know which view to refresh
    $elements['#attached']['library'][] = 'gary_field_formatter/refresh';
    $elements['#theme'] = 'paragraph_views_formatter';

    return $elements;
  }


  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['instructions'] = [
      '#title' => $this->t('How:'),
      '#description' => $this->t('Create a view, add contextual filter for parent id, enable ajax on the view.'),
      '#type' => 'item',
    ];

    $element['ajax_inputs'] = [
      '#title' => $this->t('Use Ajax Inputs'),
      '#description' => $this->t('Display an entity form to ajax submit a new paragraph or entity item'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('ajax_inputs'),
    ];
    $element['keep_form_expanded'] = [
      '#title' => $this->t('Keep the Form Expanded after Submission'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('keep_form_expanded'),
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
    $element['switch_view_link_text'] = [
      '#title' => $this->t('The link text to display for switching the view'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('switch_view_link_text'),
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
      'keep_form_expanded' => FALSE,
      'view_machine_name' => "",
      'view_display_name' => "",
      'form_class' => "",
      'switch_view' => "",
      'switch_view_display' => "",
      'switch_view_link_text' => "",
    ] + parent::defaultSettings();
  }
}
