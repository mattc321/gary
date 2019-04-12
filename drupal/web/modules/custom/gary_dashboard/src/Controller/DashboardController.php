<?php
/**
 * @file
 * Contains \Drupal\gary_dashboard\Controller\DashboardController.
 * Holds callbacks for items in gary_dashboard
 */
namespace Drupal\gary_dashboard\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\views\Views;
use Drupal\views\Plugin\Block\ViewsBlock;
use Drupal\core\Url;

class DashboardController extends ControllerBase {

  // protected $blockManager;
  //
  // public function __construct(BlockManager $block_manager) {
  //   $this->blockManager = $block_manager;
  // }
  //
  // /**
  //  * {@inheritdoc}
  //  */
  // public static function create(ContainerInterface $container) {
  //   // Instantiates this form class.
  //   return new static(
  //     // Load the service required to construct this class.
  //     $container->get('plugin.manager.block')
  //   );
  // }

  protected function getBlocks() {
    $config = \Drupal::config('gary_dashboard.adminsettings');
    $layout['top'] = $config->get('dashboard_layout_top');
    $layout['first'] = $config->get('dashboard_layout_first');
    $layout['second'] = $config->get('dashboard_layout_second');
    $layout['bottom'] = $config->get('dashboard_layout_bottom');
    return $layout;
  }

  public function buildDashboard() {
    // $block_instance = new DashboardBlockManager;
    $block_instance = \Drupal::service('plugin.manager.block');
    $blocks = $this->getBlocks();
    foreach ($blocks as $region => $region_blocks) {
      foreach ($region_blocks as $index => $block_id) {
        $load_block = $block_instance->createInstance($block_id);
        $build_block = $load_block->build();

        //check the plugin type
        $definition = $load_block->getPluginDefinition();

        //if its a view load it and check for exposed form
        if ($definition['id'] == 'views_block') {
          $parts = explode("-", $load_block->getDerivativeId());
          $view_id = str_replace('_','-',$parts[0]);
          $display_id = str_replace('_','-',$parts[1]);
          $view = Views::getView($parts[0]);
          $view->setDisplay($parts[1]);
          $form_selector = 'views-exposed-form-'.$view_id.'-'.$display_id;
          $build_block['#form_selector'] = $form_selector;
          $build_block['#block_title'] = $view->getTitle();

        }

        $build['#region'][$region][] = $build_block;
      }
    }

    $build['#adminsettings'] = [
      '#type' => 'link',
      '#access' => $this->getAccess(),
      '#title' => t('Settings'),
      '#url' => Url::fromRoute('gary_dashboard.admin_settings_form')
    ];

    $build['#attached']['library'][] = 'gary_dashboard/twocol';
    $build['#attached']['library'][] = 'gary_dashboard/dashboard';
    $build['#theme'] = 'gary_dashboard';
    return $build;
  }

  private function getAccess() {
    $user = \Drupal::currentUser();
    $roles = $user->getRoles();
    if (in_array('administrator', $roles) || in_array('limited_admin', $roles)) {
      return TRUE;
    }
    return FALSE;
  }
}
