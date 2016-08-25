<?php

$basePath = realpath(__DIR__ . '/../');
error_reporting(E_ALL);
ini_set('display_errors', false);
ini_set('error_log', $basePath . '/log/php_error.log');
date_default_timezone_set('UTC');

/**
 * Init
 */
$configs = array(
    'base_path' => $basePath,
    'tmp_path' => $basePath . '/tmp',
    'req_id' => uniqid('REQ-' . gethostname()),
    'php_bin' => '/usr/bin/php',
    'docker_compose_bin' => '/usr/local/bin/docker-compose',
    'docker_bin' => '/usr/local/bin/docker',
    'nginx_bin' => '/usr/local/bin/nginx'
);

/**
 * PDO Service Configs
 */
$configs['pdo'] = array(
    'dsn' => 'mysql:host=127.0.0.1;port=8889;dbname=stage;charset=utf8',
    'username' => 'root',
    'password' => 'root'
);

/**
 * Logger
 */
$configs['logger'] = array(
    'default_name' => 'default',
    'default_path' => realpath($basePath . '/log'),
    'default_level' => \Monolog\Logger::INFO
);
/**
 * supports different path and level for log name
 * $configs['logger']['app'] = array();
 * $configs['logger']['app']['path'] = realpath(BASE_PATH . '/logs');
 * $configs['logger']['app']['level'] = \Monolog\Logger::DEBUG;
 * $di->getLogger('app')->info('foo bar');
 */

/**
 * Twig
 */
$configs['twig'] = array(
    'templates_path' => $basePath . '/templates',
    'cache' => $basePath . '/tmp/twig',
    'auto_reload' => true
);

return $configs;
