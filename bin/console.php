<?php

namespace {

    use Application\Console\TaskExecutorCommand;
    use Doctrine\DBAL\DriverManager;
    use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand;
    use Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand;
    use Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand;
    use Doctrine\DBAL\Migrations\Tools\Console\Command\LatestCommand;
    use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand;
    use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand;
    use Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand;
    use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
    use League\Container\Container;
    use Symfony\Component\Console\Application;

    /**
     * @var $di Container
     */
    $di = include(realpath(__DIR__ . '/../') . '/configs/bootstrap.php');
    $di->get('logger_helper')->setDefaultName('console');

    try {
        $consoleApp = new Application('CLI', '1.0');
        $consoleApp->setCatchExceptions(false);
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
        $consoleApp->add(new TaskExecutorCommand($di));
        $consoleApp->run();
    } catch (\Exception $exception) {
        $di->get('logger_helper')->getLogger()->error($exception);
    }
}
