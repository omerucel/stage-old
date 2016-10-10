<?php

namespace {

    use Application\Controller\BaseController;
    use Application\Exception\PermissionDeniedException;
    use Application\Exception\UserRequiredException;
    use FastRoute\Dispatcher;
    use FastRoute\RouteCollector;
    use League\Container\Container;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\Session\Session;

    /**
     * @var $di Container
     */
    $di = include(realpath(__DIR__ . '/../') . '/configs/bootstrap.php');
    $request = Request::createFromGlobals();
    $di->share('request', $request);
    $di->share('response', new Response());
    $di->share('twig', function () use ($di) {
        $config = $di->get('config');
        $loader = new \Twig_Loader_Filesystem($config->twig->templates_path);
        $twig = new \Twig_Environment($loader, json_decode(json_encode($config->twig), true));
        return $twig;
    });
    $di->share('session', function () {
        $session = new Session();
        $session->start();
        return $session;
    });
    register_shutdown_function(function () use ($di) {
        if (is_array(($error = error_get_last()))) {
            $di->get('logger_helper')->getLogger()->critical('An Error Occurred', $error);
            $response = new Response('An Error Occurred', 500);
            $response->send();
            exit;
        }
    });
    $di->get('logger_helper')->getLogger()->info(
        'New request',
        [
            'uri' => $request->getRequestUri(),
            'method' => $request->getMethod(),
            'params' => $_REQUEST
        ]
    );
    try {
        $dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $collector) {
            $collector->addRoute('GET', '/', 'Application\Controller\IndexController');
            $collector->addRoute(['GET', 'POST'], '/login', 'Application\Controller\LoginController');
            $collector->addRoute('GET', '/logout', 'Application\Controller\LogoutController');
            // Projects
            $collector->addRoute('GET', '/projects', 'Application\Controller\ProjectsController');
            $collector->addRoute(['GET', 'POST'], '/projects/save', 'Application\Controller\ProjectSaveController');
            // Server
            $collector->addRoute(['GET', 'POST'], '/projects/server', 'Application\Controller\ServerController');
            $collector->addRoute(['GET', 'POST'], '/projects/server/setup', 'Application\Controller\ServerSetupController');
            $collector->addRoute(['GET', 'POST'], '/projects/server/start', 'Application\Controller\ServerStartController');
            $collector->addRoute(['GET', 'POST'], '/projects/server/stop', 'Application\Controller\ServerStopController');
            $collector->addRoute(['GET', 'POST'], '/projects/server/inspect', 'Application\Controller\ServerInspectController');
            // Vhost
            $collector->addRoute(['GET', 'POST'], '/projects/vhost', 'Application\Controller\VhostController');
            // Users
            $collector->addRoute('GET', '/users', 'Application\Controller\UsersController');
            $collector->addRoute('GET', '/users/activities', 'Application\Controller\UserActivitiesController');
            $collector->addRoute(['GET', 'POST'], '/users/save', 'Application\Controller\UserSaveController');
        });
        $requestUri = $request->getRequestUri();
        $pos = strpos($requestUri, '?');
        if ($pos > -1) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        $routeInfo = $dispatcher->dispatch($request->getMethod(), $requestUri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $response = new Response('Not found', 404);
                $di->get('logger_helper')->getLogger()->warning('Not found.');
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response = new Response('Method not allowed', 405);
                $di->get('logger_helper')->getLogger()->warning('Method not allowed');
                break;
            case Dispatcher::FOUND:
                /**
                 * @var $object BaseController
                 */
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $object = new $handler($di);
                $response = $object->handle($vars);
                break;
        }
    } catch (UserRequiredException $exception) {
        $response = new RedirectResponse('/login');
        $di->get('logger_helper')->getLogger()->warning($exception);
    } catch (PermissionDeniedException $exception) {
        $response = new RedirectResponse('/');
        $di->get('logger_helper')->getLogger()->warning($exception);
    } catch (\Exception $exception) {
        $response = new Response('An Error Occurred', 500);
        $di->get('logger_helper')->getLogger()->error($exception);
    }
    if (isset($response)) {
        $response->send();
    }
}
