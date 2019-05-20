<?php
/**
 * @file
 * Contains \Drupal\gary_google_maps_api\Controller\ProjectsMap.
 * Builds the maps page
 */
namespace Drupal\gary_google_maps_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
// use Drupal\Core\Ajax;
// use Drupal\Core\Ajax\InvokeCommand;
// use Drupal\views\Views;
// use Drupal\views\Plugin\Block\ViewsBlock;
// use Drupal\core\Url;
// use Symfony\Component\DependencyInjection\ContainerInterface;
// use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;
// use Symfony\Component\HttpFoundation\Request;

class ProjectsMap extends ControllerBase {


  private $geocodes;

  public function buildMap() {

    $build = [
      '#content' => '',
      '#theme' => 'gary_google_maps_api',
      '#attached' => [
        'library' => [
          'gary_google_maps_api/project_map'
        ]
      ]
    ];

    // $build = [
    //   '#type' => 'inline_template',
    //   '#theme' => 'gary_google_maps_api',
    //   '#attached' => [
    //     'library' => [
    //       'gary_google_maps_api/project_map'
    //     ]
    //   ]
    // ];
    $build['#attached']['drupalSettings']['gary_google_maps_api']['project_xml_list'] = $this->getXmlOutput();
    // ksm($this->getXmlOutput());
    return $build;

  }

  public function getAddressXml() {

  }

  private function getXmlOutput() {
    // ksm($response);
    $doc = new \DOMDocument();
    $doc->encoding = 'utf-8';
    $doc->xmlVersion = '1.0';
    $doc->formatOutput = TRUE;
    $xml_file_name = 'project-list.xml';
    $node = $doc->createElement('markers');
    $paranode = $doc->appendChild($node);
    $geocodes = $this->queryGeocodes();

    foreach ($geocodes as $key => $geocode_array) {
      $marker_node = $doc->createElement('marker');
      $newnode = $paranode->appendChild($marker_node);

      $newnode->setAttribute("id", $key);
      $newnode->setAttribute("name", $geocode_array->account_node_title);
      $newnode->setAttribute("address", $geocode_array->title);
      $newnode->setAttribute("lat", $geocode_array->field_geolocation_lat);
      $newnode->setAttribute("lng", $geocode_array->field_geolocation_lng);
      $newnode->setAttribute("type", "");
    }

    return $doc->saveXML();
    // return new Response(0);
  }

  private function queryGeocodes() {
    $query = \Drupal::database()->select('node_field_data', 'project_node');
    $query->addField('project_node', 'nid');
    $query->addField('project_node', 'title');
    $query->addJoin('left','node__field_geolocation','g','project_node.nid=g.entity_id');
    $query->addJoin('left','node__field_account_reference','a','project_node.nid=a.entity_id');
    $query->addJoin('left','node_field_data','account_node','a.field_account_reference_target_id=account_node.nid');
    $query->addField('account_node', 'nid');
    $query->addField('account_node', 'title');
    $query->addField('g', 'field_geolocation_lat');
    $query->addField('g', 'field_geolocation_lng');
    $query->addField('g', 'field_geolocation_lat_sin');
    $query->addField('g', 'field_geolocation_lat_cos');
    $query->addField('g', 'field_geolocation_lng_rad');
    $query->condition('project_node.type', 'projects');
    $query->condition('project_node.status', 1);
    $query->condition('g.bundle', 'projects');
    $results = $query->execute()->fetchAll();
    return $results;
  }

}
