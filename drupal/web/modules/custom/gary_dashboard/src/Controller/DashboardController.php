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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends ControllerBase {

  protected $isMobile;

  protected $config;

  public function __construct() {
    $tempstore = \Drupal::service('user.private_tempstore')->get('gary_custom');
    $this->isMobile = $tempstore->get('is_mobile');
    $this->config = \Drupal::config('gary_dashboard.adminsettings');
  }

  protected function getBlocks() {
    $layout['top'] = $this->config->get('dashboard_layout_top');
    $layout['first'] = $this->config->get('dashboard_layout_first');
    $layout['second'] = $this->config->get('dashboard_layout_second');
    $layout['bottom'] = $this->config->get('dashboard_layout_bottom');
    return $layout;
  }

  protected function getMobileBlocks() {
    $layout['mobile'] = $this->config->get('mobile_dashboard_layout');
    return $layout;
  }

  public function buildDashboard(Request $request) {
    // $block_instance = new DashboardBlockManager;
    $block_instance = \Drupal::service('plugin.manager.block');
    $blocks = ($this->isMobile) ? $this->getMobileBlocks() : $this->getBlocks();
    $theme = ($this->isMobile) ? 'gary_dashboard_mobile' : 'gary_dashboard';
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
    $build['#theme'] = $theme;
    return $build;
  }

  private function getAccess() {
    $user = \Drupal::currentUser();
    $roles = $user->getRoles();
    if (in_array('administrator', $roles) || in_array('ec_admin', $roles)) {
      return TRUE;
    }
    return FALSE;
  }
}
