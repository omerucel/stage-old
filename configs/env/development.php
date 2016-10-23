<?php

$configs = include(__DIR__ . '/../global.php');

$configs['nginx_bin'] = '/usr/sbin/nginx';
$configs['docker_bin'] = '/usr/bin/docker';
$configs['docker_compose_bin'] = '/usr/bin/docker-compose';
$configs['pdo']['dsn'] = 'mysql:host=localhost;port=3306;dbname=stage;charset=utf8';
$configs['pdo']['debug'] = true;

return $configs;
