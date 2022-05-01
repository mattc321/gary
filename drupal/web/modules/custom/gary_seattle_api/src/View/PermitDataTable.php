<?php
namespace Drupal\gary_seattle_api\View;

class PermitDataTable
{
  /**
   * @var array
   */
  private $headers;
  /**
   * @var array
   */
  private $rows;
  /**
   * @var string
   */
  private $tableCaption;

  public function __construct(array $headers, array $rows, string $tableCaption)
  {
    $this->headers = $headers;
    $this->rows = $rows;
    $this->tableCaption = $tableCaption;
  }

  /**
   * @return array
   */
  public function render()
  {

    $headers = $this->prepareHeaders();

    return [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $this->rows,
    ];
  }

  /**
   * @return array
   */
  private function prepareHeaders()
  {
    $headers = [];
    foreach ($this->headers as $key => $header) {
      $headers[$key] = $header['alias'];
    }
    return $headers;
  }
}
