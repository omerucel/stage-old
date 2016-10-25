<?php

namespace {

    use Application\Database\MySQL\MapperContainer;
    use Application\Docker;
    use Application\Logger\LoggerHelper;
    use Application\Nginx;
    use Application\Pdo\Wrapper;
    use Application\Project\VhostUpdater;
    use League\Container\Container;

    $basePath = realpath(__DIR__ . '/..');
    $environment = strtolower(getenv('APPLICATION_ENV'));
    if (!$environment) {
        echo 'Please check your project configuration!';
        exit;
    }

    include($basePath . '/vendor/autoload.php');

    $di = new Container();
    $di->share('config', function () use ($basePath, $environment) {
        $configs = include($basePath . '/configs/env/' . $environment . '.php');
        $configs['environment'] = $environment;
        return json_decode(json_encode($configs));
    });
    $di->share('logger_helper', function () use ($di) {
        $config = $di->get('config');
        $helper = new LoggerHelper($config->logger->path, $config->environment);
        $helper->setReqId($config->req_id);
        return $helper;
    });
    $di->share('pdo', function () use ($di) {
        $config = $di->get('config');
        $pdo = new \PDO($config->pdo->dsn, $config->pdo->username, $config->pdo->password);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $pdo;
    });
    $di->share('pdo_wrapper', function () use ($di) {
        return new Wrapper($di);
    });
    $di->share('mapper_container', function () use ($di) {
        return new MapperContainer($di);
    });
    $di->share('docker', function () use ($di) {
        $config = $di->get('config');
        $logger = $di->get('logger_helper')->getLogger();
        return new Docker($config->docker_bin, $config->docker_compose_bin, $logger);
    });
    $di->share('nginx', function () use ($di) {
        $config = $di->get('config');
        $logger = $di->get('logger_helper')->getLogger();
        return new Nginx($config->nginx_bin, $logger);
    });
    $di->share('vhost_updater', function () use ($di) {
        $docker = $di->get('docker');
        $nginx = $di->get('nginx');
        $confPath = realpath($di->get('config')->base_path) . '/nginx.conf.d';
        return new VhostUpdater($docker, $nginx, $confPath);
    });

    return $di;
}
