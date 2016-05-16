<?php

namespace TheCodingMachine;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Collection;
use Slim\Http\Environment;
use Slim\Interfaces\Http\EnvironmentInterface;
use Slim\Handlers\PhpError;
use Slim\Handlers\Error;
use Slim\Handlers\NotFound;
use Slim\Handlers\NotAllowed;
use Slim\Handlers\Strategies\RequestResponse;
use Slim\Http\Headers;
use Slim\Http\Response;
use Slim\Interfaces\RouterInterface;

class SlimServiceProvider implements ServiceProvider
{

    public function getServices()
    {
        return [
            App::class => [self::class,'createApp'],
            'settings' => [self::class,'getSettings'],
            'environment' => [self::class,'getEnvironment'],
            'request' => [self::class,'getRequest'],
            'response' => [self::class,'getResponse'],
            'router' => [self::class,'getRouter'],
            'foundHandler' => [self::class,'getFoundHandler'],
            'phpErrorHandler' => [self::class,'getPhpErrorHandler'],
            'errorHandler' => [self::class,'getErrorHandler'],
            'notFoundHandler' => [self::class,'getNotFoundHandler'],
            'notAllowedHandler' => [self::class,'getNotAllowedHandler'],
            'callableResolver' => [self::class,'getCallableResolver'],
            MiddlewareListServiceProvider::MIDDLEWARES_QUEUE => [self::class,'updatePriorityQueue']
        ];
    }
    public static function createApp(ContainerInterface $container) : App
    {
        return new App($container);
    }

    public static function getSettings(ContainerInterface $container, callable $getPrevious = null):Collection
    {
        $defaultSettings = [
            'httpVersion' => '1.1',
            'responseChunkSize' => 4096,
            'outputBuffering' => 'append',
            'determineRouteBeforeAppMiddleware' => false,
            'displayErrorDetails' => false,
            'addContentLengthHeader' => true,
            'routerCacheFile' => false,
        ];

        if ($getPrevious === null) {
            return $defaultSettings;
        } else {
            $userSettings = $getPrevious();
            return new Collection(array_merge($defaultSettings, $userSettings));
        }
    }

    public static function getEnvironment():EnvironmentInterface
    {
        return new Environment($_SERVER);
    }

    public static function getRequest(ContainerInterface $container):ResponseInterface
    {
        return Request::createFromEnvironment($container->get('environment'));
    }

    public static function getResponse(ContainerInterface $container):ResponseInterface
    {
        $headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
        $response = new Response(200, $headers);

        return $response->withProtocolVersion($container->get('settings')['httpVersion']);
    }

    public static function getRouter(ContainerInterface $container):RouterInterface
    {
        $routerCacheFile = false;
        if (isset($container->get('settings')['routerCacheFile'])) {
            $routerCacheFile = $container->get('settings')['routerCacheFile'];
        }

        return (new Router)->setCacheFile($routerCacheFile);
    }

    public static function getFoundHandler():RouterInterface
    {
        return new RequestResponse;
    }

    public static function getPhpErrorHandler(ContainerInterface $container):PhpError
    {
        return new PhpError($container->get('settings')['displayErrorDetails']);
    }

    public static function getErrorHandler(ContainerInterface $container):Error
    {
        return new Error($container->get('settings')['displayErrorDetails']);
    }

    public static function getNotFoundHandler():NotFound
    {
        return new NotFound;
    }

    public static function getNotAllowedHandler():NotFound
    {
        return new NotAllowed;
    }

    public static function getCallableResolver(ContainerInterface $container):NotFound
    {
        return new CallableResolver($container);
    }

    public static function updatePriorityQueue(ContainerInterface $container, callable $previous = null) : \SplPriorityQueue
    {
        if ($previous) {
            $priorityQueue = $previous();
            $priorityQueue->insert($container->get(App::class), MiddlewareOrder::ROUTER);
            return $priorityQueue;
        } else {
            throw new InvalidArgumentException("Could not find declaration for service '".MiddlewareListServiceProvider::MIDDLEWARES_QUEUE."'.");
        }
    }
}
