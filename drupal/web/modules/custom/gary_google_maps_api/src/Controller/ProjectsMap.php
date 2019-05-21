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

    $config = $this->config('gary_google_maps_api.adminsettings');

    $build = [
      '#content' => '',
      '#theme' => 'gary_google_maps_api',
      '#attached' => [
        'library' => [
          'gary_google_maps_api/project_map'
        ]
      ]
    ];

    $build['#attached']['drupalSettings']['gary_google_maps_api']['project_xml_list'] = $this->getXmlOutput();
    $build['#attached']['drupalSettings']['gary_google_maps_api']['key'] = $config->get('map_api_key');

    return $build;

  }

  public function getAddressXml() {

  }

  private function getXmlOutput() {
    $doc = new \DOMDocument();
    $doc->encoding = 'utf-8';
    $doc->xmlVersion = '1.0';
    $doc->formatOutput = TRUE;
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
      $newnode->setAttribute("status", $geocode_array->tax_name);

    }

    return $doc->saveXML();
  }

  private function queryGeocodes() {

    //subquery for tax terd data
    $subquery = '(SELECT tax.name AS name
		FROM taxonomy_term_field_data tax
		WHERE (tax.tid = project_status.field_project_status_target_id))';

    //main query fetch projects and data
    $query = \Drupal::database()->select('node_field_data', 'project_node');
    $query->addField('project_node', 'nid');
    $query->addField('project_node', 'title');
    $query->addJoin('left','node__field_geolocation','g','project_node.nid=g.entity_id');
    $query->addJoin('left','node__field_project_status','project_status','project_node.nid=project_status.entity_id');
    $query->addJoin('left','node__field_account_reference','a','project_node.nid=a.entity_id');
    $query->addJoin('left','node_field_data','account_node','a.field_account_reference_target_id=account_node.nid');
    $query->addField('account_node', 'nid');
    $query->addField('account_node', 'title');
    $query->addExpression($subquery, 'tax_name');
    $query->addField('g', 'field_geolocation_lat');
    $query->addField('g', 'field_geolocation_lng');
    $query->addField('g', 'field_geolocation_lat_sin');
    $query->addField('g', 'field_geolocation_lat_cos');
    $query->addField('g', 'field_geolocation_lng_rad');
    $query->addField('project_status', 'field_project_status_target_id');
    $query->condition('project_node.type', 'projects');
    $query->condition('project_node.status', 1);
    $query->condition('g.bundle', 'projects');
    $results = $query->execute()->fetchAll();
    ksm($results);
    return $results;
  }

}
