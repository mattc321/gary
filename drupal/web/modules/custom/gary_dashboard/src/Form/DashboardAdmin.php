<?php
/**
 * @file
 * Contains Drupal\gary_dashboard\Form\DashboardAdmin.
 */
namespace Drupal\gary_dashboard\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class DashboardAdmin extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'gary_dashboard.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dashboard_admin_form';
  }

  protected function getAvailableBlocks() {
    $blockManager = \Drupal::service('plugin.manager.block');
    $contextRepository = \Drupal::service('context.repository');
    // Get blocks definition
    $definitions = $blockManager->getDefinitionsForContexts($contextRepository->getAvailableContexts());
    $blocks_select = [];
    foreach ($definitions as $block_id => $definition) {
      $blocks_select[$block_id] = (is_object($definition['admin_label']) ? $definition['admin_label']->__toString() : $definition['admin_label']);
    }
    return $blocks_select;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('gary_dashboard.adminsettings');
    $form = parent::buildForm($form, $form_state);
    $options = $this->getAvailableBlocks();
    $form['dashboard_layout_top'] = [
      '#type' => 'select2',
      '#multiple' => TRUE,
      '#title' => $this->t('Blocks for top region'),
      '#default_value' => $config->get('dashboard_layout_top'),
      '#options' => $options,
    ];
    $form['dashboard_layout_first'] = [
      '#type' => 'select2',
      '#title' => $this->t('Blocks for first region'),
      '#multiple' => TRUE,
      '#options' => $options,
      '#default_value' => $config->get('dashboard_layout_first'),
    ];
    $form['dashboard_layout_second'] = [
      '#type' => 'select2',
      '#title' => $this->t('Blocks for second region'),
      '#multiple' => TRUE,
      '#options' => $options,
      '#default_value' => $config->get('dashboard_layout_second'),
    ];
    $form['dashboard_layout_bottom'] = [
      '#type' => 'select2',
      '#title' => $this->t('Blocks for bottom region'),
      '#multiple' => TRUE,
      '#options' => $options,
      '#default_value' => $config->get('dashboard_layout_bottom'),
    ];

    $form['mobile_title'] = [
      '#markup'=> '<br/><h3>Select Blocks to Load for Mobile Display</h3>'
    ];
    $form['mobile_dashboard_layout'] = [
      '#type' => 'select2',
      '#multiple' => TRUE,
      '#title' => $this->t('Blocks to load for mobile'),
      '#default_value' => $config->get('mobile_dashboard_layout'),
      '#options' => $options,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('gary_dashboard.adminsettings');
    $config->set('dashboard_layout_top', $form_state->getValue('dashboard_layout_top'));
    $config->set('dashboard_layout_first', $form_state->getValue('dashboard_layout_first'));
    $config->set('dashboard_layout_second', $form_state->getValue('dashboard_layout_second'));
    $config->set('dashboard_layout_bottom', $form_state->getValue('dashboard_layout_bottom'));
    $config->set('mobile_dashboard_layout', $form_state->getValue('mobile_dashboard_layout'));
    $config->save();
  }

}
