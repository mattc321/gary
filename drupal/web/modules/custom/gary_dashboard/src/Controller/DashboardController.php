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
    $this->isMobile = $this->_isMobile();
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

  public function _isMobile() {
    if (!isset($_SERVER['HTTP_USER_AGENT'])) {
      $is_mobile = FALSE;
    } else {
      /**
       *  check http://detectmobilebrowsers.com for updates
       */
      $useragent=$_SERVER['HTTP_USER_AGENT'];

      $is_mobile = (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)
      ||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) ? TRUE : FALSE;
    }
    return $is_mobile;
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
