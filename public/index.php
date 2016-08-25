<?php

namespace {

    use Phalcon\Di;
    use Phalcon\Http\Response;
    use Phalcon\Mvc\Application;
    use Phalcon\Mvc\Router;
    use Phalcon\Mvc\View;
    use Phalcon\Session\Adapter\Files;

    /**
     * @var $di Di
     */
    $di = include(realpath(__DIR__ . '/../') . '/configs/bootstrap.php');
    $di->setShared('router', function () {
        $router = new Router();
        $router->setDefaultNamespace('Application\Controller');
        return $router;
    });
    $di->setShared('view', function () use ($di) {
        $view = new View();
        $view->setViewsDir($di->get('config')->base_path . '/templates');
        $view->disable();
        return $view;
    });
    $di->setShared('twig', function () use ($di) {
        $config = $di->get('config');
        $loader = new \Twig_Loader_Filesystem($config->twig->templates_path);
        $twig = new \Twig_Environment($loader, json_decode(json_encode($config->twig), true));
        return $twig;
    });
    $di->setShared('session', function () {
        $session = new Files();
        $session->start();
        return $session;
    });
    $app = new Application($di);
    try {
        $pathInfo = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $response = $app->handle($pathInfo);
    } catch (\Exception $exception) {
        $response = new Response('An Error Occurred', 500);
        $di->get('logger_helper')->getLogger()->error($exception);
    }
    $response->send();
}
