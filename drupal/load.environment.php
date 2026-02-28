<?php

/**
 * This file is included very early. See autoload.files in composer.json and
 * https://getcomposer.org/doc/04-schema.md#files
 */

use Dotenv\Dotenv;

/**
 * Load any .env file. See /.env.example.
 */
$dotenv = Dotenv::createImmutable(__DIR__);
try {
  $dotenv->load();
}
catch (\Exception $e) {
  // Do nothing. Production environments rarely use .env files.
}
