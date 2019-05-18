<?php
/**
 * @file
 * Contains \Drupal\gary_google_maps_api\Controller\ProjectsMap.
 * Builds the maps page
 */
namespace Drupal\gary_google_maps_api\Controller;

use Drupal\Core\Controller\ControllerBase;
// use Drupal\Core\Ajax;
// use Drupal\Core\Ajax\InvokeCommand;
// use Drupal\views\Views;
// use Drupal\views\Plugin\Block\ViewsBlock;
// use Drupal\core\Url;
// use Symfony\Component\DependencyInjection\ContainerInterface;
// use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;
// use Symfony\Component\HttpFoundation\Request;

class ProjectsMap extends ControllerBase {

  public function buildMap() {

    // $build = [
    //   '#content' => '',
    //   '#theme' => 'gary_google_maps_api',
    //   '#attached' => [
    //     'library' => [
    //       'gary_google_maps_api/project_map'
    //     ]
    //   ]
    // ];

    $build = [
      '#type' => 'inline_template',
      '#theme' => 'gary_google_maps_api',
      '#attached' => [
        'library' => [
          'gary_google_maps_api/project_map'
        ]
      ]
    ];

    return $build;

  }

}
