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

        $title = $load_block->label();
        $build_block = $load_block->build();
        $build_block['#block_title'] = $title;

        //check the plugin type
        $definition = $load_block->getPluginDefinition();

        //if its a view load it and check for exposed form
        if ($definition['id'] == 'views_block') {
          $parts = explode("-", $load_block->getDerivativeId());
          $view_id = str_replace('_','-',$parts[0]);
          $display_id = str_replace('_','-',$parts[1]);
          $form_selector = 'views-exposed-form-'.$view_id.'-'.$display_id;
          $build_block['#form_selector'] = $form_selector;
        }

        $build['#region'][$region][] = $build_block;
      }
    }
    $build['#attached']['library'][] = 'gary_dashboard/twocol';
    $build['#theme'] = 'gary_dashboard';
    return $build;
  }

}
