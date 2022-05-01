<?php

namespace Drupal\gary_seattle_api\Api;

use Drupal\Core\Site\Settings;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class SeattleApiClient
{
  const SETTINGS_KEY = 'seattle_gov';
  const PERMIT_RESOURCE = '76t5-zqzr.json';

  /**
   * @param $permitNumber
   * @return ResponseInterface
   * @throws GuzzleException
   * @throws Exception
   */
  public function getPermitInfo($permitNumber) : ResponseInterface
  {
    $settings = $this->getSettings();
    $credentials = base64_encode("{$settings['api_key']}:{$settings['api_secret']}");
    $client = new Client([
      'base_uri' => $settings['base_uri'],
      'headers' => [
        'Authorization' => 'Basic ' . $credentials,
      ],
    ]);

    return $client->request(
      'GET',
      self::PERMIT_RESOURCE,
      [
        'query' => [
          '$where' => "permitnum like '{$permitNumber}%'"
        ],
        ['debug' => true]
      ]
    );
  }

  /**
   * @param $permitNumber
   * @return string
   * @throws GuzzleException
   * @throws Exception
   */
  public function getPermitUrl($permitNumber) : string
  {
    $permitInfo = $this->getPermitInfo($permitNumber);

    $body = json_decode($permitInfo->getBody(), true);

    if (! isset($body[0]['link']['url'])) {
      throw new Exception('Missing url from response.');
    }

    return $body[0]['link']['url'];
  }

  /**
   * @return array
   * @throws Exception
   */
  private function getSettings() : array
  {
    $apiSettings =  Settings::get('api', []);

    $keysToValidate = [
      'api_key',
      'api_secret',
      'api_app_token',
      'base_uri'
    ];

    if (! $apiSettings) {
      throw new Exception('Missing api credentials.');
    }

    if (! isset($apiSettings[self::SETTINGS_KEY])) {
      throw new Exception('Missing api credentials.');
    }

    foreach ($keysToValidate as $keyToValidate) {
      if (! isset($apiSettings[self::SETTINGS_KEY][$keyToValidate]) || ! $apiSettings[self::SETTINGS_KEY][$keyToValidate]) {
        throw new Exception('Missing api credentials.');
      }
    }

    return $apiSettings[self::SETTINGS_KEY];
  }
}
