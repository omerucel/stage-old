<?php

namespace {

    use Phalcon\Config;
    use Phalcon\Di\FactoryDefault;
    use Phalcon\Loader;
    use Phalcon\Mvc\View;
    use Application\Database\MySQL\MapperContainer;
    use Application\MonologHelper;
    use Application\Pdo\Wrapper;

    $basePath = realpath(__DIR__ . '/..');
    $environment = strtolower(getenv('APPLICATION_ENV'));
    if (!$environment) {
        echo 'Please check your project configuration!';
        exit;
    }

    include($basePath . '/vendor/autoload.php');

    $di = new FactoryDefault();
    $di->setShared('config', function () use ($basePath, $environment) {
        $configs = include($basePath . '/configs/env/' . $environment . '.php');
        $configs['environment'] = $environment;
        return new Config($configs);
    });
    $di->setShared('logger_helper', function () use ($di) {
        return new MonologHelper($di);
    });
    $di->setShared('pdo', function () use ($di) {
        $config = $di->get('config');
        $pdo = new \PDO($config->pdo->dsn, $config->pdo->username, $config->pdo->password);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $pdo;
    });
    $di->setShared('pdo_wrapper', function () use ($di) {
        return new Wrapper($di);
    });
    $di->setShared('mapper_container', function () use ($di) {
        return new MapperContainer($di);
    });

    return $di;
}
