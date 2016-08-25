<?php

namespace {

    use Doctrine\DBAL\DriverManager;
    use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand;
    use Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand;
    use Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand;
    use Doctrine\DBAL\Migrations\Tools\Console\Command\LatestCommand;
    use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand;
    use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand;
    use Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand;
    use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
    use Phalcon\Di;
    use Symfony\Component\Console\Application;

    /**
     * @var $di Di
     */
    $di = include(realpath(__DIR__ . '/../') . '/configs/bootstrap.php');
    $di->get('config')->logger->default_name = 'console';

    $consoleApp = new Application('CLI', '1.0');
    $doctrineConn = DriverManager::getConnection(
        array(
            'driver' => 'pdo_mysql',
            'pdo' => $di->get('pdo')
        )
    );
    $consoleApp->getHelperSet()->set(new ConnectionHelper($doctrineConn));
    $consoleApp->add(new DiffCommand());
    $consoleApp->add(new MigrateCommand());
    $consoleApp->add(new ExecuteCommand());
    $consoleApp->add(new GenerateCommand());
    $consoleApp->add(new LatestCommand());
    $consoleApp->add(new StatusCommand());
    $consoleApp->add(new VersionCommand());
    $consoleApp->run();
}
