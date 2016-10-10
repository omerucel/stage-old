<?php

namespace {

    use Application\Database\MySQL\MapperContainer;
    use Application\MonologHelper;
    use Application\Pdo\Wrapper;
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
        return new MonologHelper($di);
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

    return $di;
}
