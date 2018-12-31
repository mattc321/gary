<?php

//gist waiting for Core Views to support entity reference drop downs filters
//https://gist.github.com/StryKaizer/ae1cb9abc4844a9e7ac12317a9d84a78


namespace Drupal\gary_custom_views\Plugin\views\filter;

use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\UserStorage;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\node\NodeStorageInterface;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Filter by user id.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("user_index")
 */
class UserIndex extends ManyToOne {
  // Stores the exposed input for this filter.
  public $validated_exposed_input = NULL;

  /**
   * User storage handler.
   *
   * @var \Drupal\user\UserStorage
   */
  protected $userTypeStorage;

  /**
   * Constructs a NodeIndexNid object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\user\UserStorage $user_storage
   *   The user storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, UserStorage $user_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->userTypeStorage = $user_storage;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('user')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    // ksm($this->definition);
    // ksm($this->options);
    // if (!empty($this->definition['node'])) {
    //   $this->options['bundle'] = $this->definition['node'];
    // }
  }
  public function hasExtraOptions() {
    return TRUE;
  }
  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    return $this->valueOptions;
  }
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['type'] = array('default' => 'textfield');
    $options['limit'] = array('default' => TRUE);
    $options['bundle'] = array('default' => '');
    $options['error_message'] = array('default' => TRUE);
    return $options;
  }
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {
    $users = $this->userTypeStorage->loadMultiple();
    $options = array();
    $roles = \Drupal::entityManager()
      ->getStorage('user_role')->loadMultiple();
    unset($roles['anonymous']);
    foreach ($roles as $role) {
      $options[$role->id()] = $role->label();
    }
    if ($this->options['limit']) {
      // We only do this when the form is displayed.
      if (empty($this->options['bundle'])) {
        $first_role = reset($roles);
        $this->options['bundle'] = $first_role->id();
      }
      if (empty($this->definition['bundle'])) {
        $form['bundle'] = array(
          '#type' => 'select',
          '#multiple' => TRUE,
          '#required' => TRUE,
          '#title' => $this->t('Role'),
          '#options' => $options,
          '#description' => $this->t('Select which role(s) to show users for in the regular options.'),
          '#default_value' => $this->options['bundle'],
        );
      }
    }
    $form['type'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Selection type'),
      '#options' => array(
        'select' => $this->t('Dropdown'),
        'textfield' => $this->t('Autocomplete')
      ),
      '#default_value' => $this->options['type'],
    );
  }
  protected function valueForm(&$form, FormStateInterface $form_state) {

      if ($this->options['type'] == 'textfield') {
          $roles = $this->options['bundle'];
          $allowed_roles = [];
          foreach ($roles as $role) {
            $allowed_roles[] = $role;
          }
          $form['value'] = array(
          '#title' => $this->t('Select a User'),
          '#type' => 'entity_autocomplete',
          '#required' => FALSE,
          '#target_type' => 'user',
          '#default_value' => '',
          '#selection_handler' => 'default:user',
          '#selection_settings' => [
            'include_anonymous' => FALSE,
            'filter' => [
              'type' => 'role',
              'role' => $allowed_roles,
            ],
          ],
        );
      }
    else {
      $roles = $this->options['bundle'];
      $query = $this->userTypeStorage->getQuery();
      $group = $query->orConditionGroup();
      foreach ($roles as $role) {
        $group->condition('roles',$role);
      }
      $ids = $query
        ->condition('status', 1)
        ->condition($group)
        ->execute();
      $users = $this->userTypeStorage->loadMultiple($ids);
      $options = [];
      foreach ($users as $id => $user) {
        $options[$id] = ucfirst($user->getDisplayName());
      }
      $default_value = (array) $this->value;

      if ($exposed = $form_state->get('exposed')) {
        $identifier = $this->options['expose']['identifier'];
        if (!empty($this->options['expose']['reduce'])) {
          $options = $this->reduceValueOptions($options);
          if (!empty($this->options['expose']['multiple']) && empty($this->options['expose']['required'])) {
            $default_value = array();
          }
        }
        if (empty($this->options['expose']['multiple'])) {
          if (empty($this->options['expose']['required']) && (empty($default_value) || !empty($this->options['expose']['reduce']))) {
            $default_value = 'All';
          }
          elseif (empty($default_value)) {
             $keys = array_keys($options);
            $default_value = array_shift($keys);
          }
          // Due to #1464174 there is a chance that array('') was saved in the admin ui.
          // Let's choose a safe default value.
          elseif ($default_value == array('')) {
            $default_value = 'All';
          }
          else {
            $copy = $default_value;
            $default_value = array_shift($copy);
          }
        }
      }
      $form['value'] = array(
        '#type' => 'select',
        '#title' => $this->t('Select a User'),
        '#multiple' => FALSE,
        '#options' => $options,
        '#size' => min(9, count($users) + 5),
        '#default_value' => $default_value,
      );
      $user_input = $form_state->getUserInput();
      if ($exposed && isset($identifier) && !isset($user_input[$identifier])) {
        $user_input[$identifier] = $default_value;
        $form_state->setUserInput($user_input);
      }
    }
    if (!$form_state->get('exposed')) {
      // Retain the helper option
      $this->helper->buildOptionsForm($form, $form_state);
      // Show help text if not exposed to end users.
      $form['value']['#description'] = t('Leave blank for all. Otherwise, the first selected user will be the default');
    }
  }

  protected function valueValidate($form, FormStateInterface $form_state) {
    // We only validate if they've chosen the text field style.
    if ($this->options['type'] != 'textfield') {
      return;
    }
    $tids = array();
    if ($values = $form_state->getValue(array('options', 'value'))) {
      foreach ($values as $value) {
        $tids[] = $value['target_id'];
      }
    }
    $form_state->setValue(array('options', 'value'), $tids);
  }

  public function acceptExposedInput($input) {
    if (empty($this->options['exposed'])) {
      return TRUE;
    }
    // We need to know the operator, which is normally set in
    // \Drupal\views\Plugin\views\filter\FilterPluginBase::acceptExposedInput(),
    // before we actually call the parent version of ourselves.
    if (!empty($this->options['expose']['use_operator']) && !empty($this->options['expose']['operator_id']) && isset($input[$this->options['expose']['operator_id']])) {
      $this->operator = $input[$this->options['expose']['operator_id']];
    }
    // If view is an attachment and is inheriting exposed filters, then assume
    // exposed input has already been validated
    if (!empty($this->view->is_attachment) && $this->view->display_handler->usesExposed()) {
      $this->validated_exposed_input = (array) $this->view->exposed_raw_input[$this->options['expose']['identifier']];
    }
    // If we're checking for EMPTY or NOT, we don't need any input, and we can
    // say that our input conditions are met by just having the right operator.
    if ($this->operator == 'empty' || $this->operator == 'not empty') {
      return TRUE;
    }
    // If it's non-required and there's no value don't bother filtering.
    if (!$this->options['expose']['required'] && empty($this->validated_exposed_input)) {
      return FALSE;
    }
    $rc = parent::acceptExposedInput($input);
    if ($rc) {
      // If we have previously validated input, override.
      if (isset($this->validated_exposed_input)) {
        $this->value = $this->validated_exposed_input;
      }
    }
    return $rc;
  }
  public function validateExposed(&$form, FormStateInterface $form_state) {
    if (empty($this->options['exposed'])) {
      return;
    }
    $identifier = $this->options['expose']['identifier'];
    // We only validate if they've chosen the text field style.
    if ($this->options['type'] != 'textfield') {
      if ($form_state->getValue($identifier) != 'All') {
        $this->validated_exposed_input = (array) $form_state->getValue($identifier);
      }
      return;
    }
    if (empty($this->options['expose']['identifier'])) {
      return;
    }
    if ($values = $form_state->getValue($identifier)) {
      foreach ($values as $value) {
        $this->validated_exposed_input[] = $value['target_id'];
      }
    }
  }

  protected function valueSubmit($form, FormStateInterface $form_state) {
    // prevent array_filter from messing up our arrays in parent submit.
  }

  public function buildExposeForm(&$form, FormStateInterface $form_state) {
    parent::buildExposeForm($form, $form_state);
    if ($this->options['type'] != 'select') {
      unset($form['expose']['reduce']);
    }
    $form['error_message'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Display error message'),
      '#default_value' => !empty($this->options['error_message']),
    );
  }

  public function adminSummary() {
    // // set up $this->valueOptions for the parent summary
    // $this->valueOptions = array();
    // if ($this->value) {
    //   $this->value = array_filter($this->value);
    //   $nodes = Node::loadMultiple($this->value);
    //   foreach ($nodes as $node) {
    //     $this->valueOptions[$node->id()] = \Drupal::entityManager()
    //       ->getTranslationFromContext($node)
    //       ->label();
    //   }
    // }
    // return parent::adminSummary();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // $contexts = parent::getCacheContexts();
    // // The result potentially depends on term access and so is just cacheable
    // // per user.
    // // @todo See https://www.drupal.org/node/2352175.
    // $contexts[] = 'user';
    // return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    // $dependencies = parent::calculateDependencies();
    // $bundle = $this->nodeTypeStorage->load($this->options['bundle']);
    // $dependencies[$bundle->getConfigDependencyKey()][] = $bundle->getConfigDependencyName();
    // foreach ($this->nodeStorage->loadMultiple($this->options['value']) as $term) {
    //   $dependencies[$term->getConfigDependencyKey()][] = $term->getConfigDependencyName();
    // }
    // return $dependencies;
  }
}
