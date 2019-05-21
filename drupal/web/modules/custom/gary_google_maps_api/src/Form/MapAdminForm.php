<?php
/**
 * @file
 * Contains Drupal\gary_google_maps_api\Form\MapAdminForm.php.
 */
namespace Drupal\gary_google_maps_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class MapAdminForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'gary_google_maps_api.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'map_admin_form';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('gary_google_maps_api.adminsettings');
    $form = parent::buildForm($form, $form_state);

    $form['#title'] = "Google Maps API Key";
    $form['map_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google Maps API Key'),
      '#default_value' => $config->get('map_api_key'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('gary_google_maps_api.adminsettings');
    $config->set('map_api_key', $form_state->getValue('map_api_key'));
    $config->save();
  }

}
