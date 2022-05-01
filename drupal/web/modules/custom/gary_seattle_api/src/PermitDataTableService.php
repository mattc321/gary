<?php
namespace Drupal\gary_seattle_api;

use Drupal\gary_seattle_api\Api\SeattleApiClient;
use Drupal\gary_seattle_api\View\PermitDataTable;

class PermitDataTableService
{
    const API_COLUMNS_TO_SELECT = [
      'permitnum' => ['alias' => 'Permit #', 'type' => 'link'],
      'applieddate' => ['alias' => 'Applied Date', 'type' => 'date'],
      'issueddate' => ['alias' => 'Issue Date', 'type' => 'date'],
      'completeddate' => ['alias' => 'Completed Date', 'type' => 'date'],
      'statuscurrent' => ['alias' => 'Status']
    ];
  /**
   * @var array
   */
  private $columnsToSelect;
  /**
   * @var SeattleApiClient
   */
  private $seattleApiClient;

  public function __construct(SeattleApiClient $seattleApiClient,array $columnsToSelect = [])
  {
    $this->columnsToSelect = $columnsToSelect ?: self::API_COLUMNS_TO_SELECT;
    $this->seattleApiClient = $seattleApiClient;
  }

  /**
   * @param array $permitNumbers
   * @return array
   */
  public function generatePermitDataTableRenderArray(array $permitNumbers = [])
  {
    if (! $permitNumbers) {
      return [];
    }

    $rows = [];
    foreach ($permitNumbers as $permitNumber) {
      $rows[] = $this->extractPermitInfoRow($permitNumber);
    }

    $permitDataTable = new PermitDataTable(array_values($this->columnsToSelect), $rows, 'Permit Data');

    return $permitDataTable->render();
  }

  /**
   * @param $permitNumber
   * @return array
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function extractPermitInfoRow($permitNumber)
  {
    $permitInfo = $this->seattleApiClient->getPermitInfo($permitNumber);

    $body = json_decode($permitInfo->getBody(), true);

    if (! isset($body[0])) {
      return [];
    }

    $row = [];
    foreach ($this->columnsToSelect as $key => $value) {
      if (isset($body[0][$key])) {
        $row[] = $this->prepareValue($this->columnsToSelect[$key], $body[0][$key], $body[0]['link']['url']);
      } else {
        $row[] = $this->prepareValue($this->columnsToSelect[$key], '');
      }
    }

    return $row;
  }

  /**
   * @param $column
   * @param $value
   * @param null $link
   * @return mixed|string
   * @throws \Exception
   */
  private function prepareValue($column, $value, $link = null)
  {
    if (! isset($column['type']) || $value === '') {
      return ['data' => ['#markup' => $value]];
    }

    switch ($column['type']) {
      case 'date':
        $date = new \DateTime($value);
        $value = ['data' => ['#markup' => $date->format('m-d-Y')]];
        break;
      case 'link':
        $value = [
          'data' => [
            '#markup' => '<a target="_blank" href="' . $link . '">' . $value . '</a>'
          ]
        ];
    }
    return $value;
  }
}
