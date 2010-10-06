<?php
$conf = array (
  'datasources' => 
  array (
    'mipanel' => 
    array (
      'adapter' => 'pgsql',
      'connection' => 
      array (
        'dsn' => 'pgsql:host=${DB_HOST};dbname=${DB_NAME};user=${DB_USER};password=${DB_PASSWORD}',
      ),
    ),
    'default' => 'mipanel',
  ),
  'generator_version' => '1.5.4-dev',
);
$conf['classmap'] = include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classmap-mipanel-conf.php');
return $conf;
