<?php

/**
 * Database configuration.
 */
$databases['default']['default'] = array(
  'database' => 'drupal',
  'username' => 'drupal',
  'password' => 'drupal',
  'prefix' => '',
  'host' => 'mariadb',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

/**
 * Random generate hash salt. Please change this to something random.
 */
$settings['hash_salt'] = 'UFr4G-R2GUxaFJrgMuaC_kKOyykBq5f4wnAxKebl1eJphw1Nz25KXr24CLNGAafxwK4_Wua9fQ';

@include('settings.dev.php');
# @include('settings.stage.php');
# @include('settings.prod.php');
