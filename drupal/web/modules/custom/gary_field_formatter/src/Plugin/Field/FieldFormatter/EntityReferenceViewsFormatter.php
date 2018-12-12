<?php

//Formatter for entity references mainly task references

namespace Drupal\gary_field_formatter\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

use Drupal\gary_field_formatter\Plugin\Field\FieldFormatter\GaryViewsFormatter;
/**
 * Plugin implementation of the 'entity_ref_views_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_ref_views_formatter",
 *   label = @Translation("Entity Ref View Formatter"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class EntityReferenceViewsFormatter extends FormatterBase {

  /**
     * {@inheritdoc}
     */
    public function settingsSummary() {
      $summary = [];
      $summary[] = $this->t('Displays an entity reference view.');
      return $summary;
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

    /**
     * {@inheritdoc}
     */
    public function viewElements(FieldItemListInterface $items, $langcode) {
      $elements = [];

      if (empty($this->getSetting('view_machine_name'))) {
        return $elements;
      }


      $host_node_id = $items->getEntity()->id();
      //load up the view
      $args = [$host_node_id];
      $view =  \Drupal\views\Views::getView($this->getSetting('view_machine_name'));
      $view->setArguments($args);

      //load the display if one is set
      if (!empty($this->getSetting('view_display_name'))) {
        $view->setDisplay($this->getSetting('view_display_name'));
      }


      return $elements;
    }

}
