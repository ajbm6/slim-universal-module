# Slim framework universal module

This package integrates the Slim framework (v3) in any [container-interop/service-provider](https://github.com/container-interop/service-provider) compatible framework/container.

## Installation

```
composer require thecodingmachine/slim-universal-module
```

Once installed, you need to register the [`TheCodingMachine\SlimServiceProvider`](src/SlimServiceProvider.php) into your container.

If your container supports Puli integration, you have nothing to do. Otherwise, refer to your framework or container's documentation to learn how to register *service providers*.

## Introduction

This service provider is meant to create a base `Slim/App` instance.
You can later play with this instance to add routes, etc...

## Expected values / services

This *service provider* expects the following configuration / services to be available:

| Name            | Compulsory | Description                            |
|-----------------|------------|----------------------------------------|
| `settings`       | *no*       | The Slim settings (see [Slim documentation about settings](http://www.slimframework.com/docs/objects/application.html#application-configuration) for more details) |


## Provided services

**Note**: Sadly, Slim uses containers as service locators instead of dependency injection containers. This means that the name of the instances is [dictated by Slim](http://www.slimframework.com/docs/concepts/di.html). The names could collide with some of your services! Be wary of this!

This *service provider* provides the following services:

| Service name                | Description                          |
|-----------------------------|--------------------------------------|
| `App::class`  | The Slim app   |
| `settings`  | The Slim default settings   |
| `environment`  |  Instance of `\Slim\Interfaces\Http\EnvironmentInterface`.  |
| `request`  |  Instance of `\Psr\Http\Message\ServerRequestInterface`.  |
| `response`  | Instance of `\Psr\Http\Message\ResponseInterface`.   |
| `router`  |  Instance of `\Slim\Interfaces\RouterInterface`.  |
| `foundHandler`  | Instance of `\Slim\Interfaces\InvocationStrategyInterface`.   |
| `phpErrorHandler`  | Callable invoked if a PHP 7 Error is thrown. See [Slim doc](http://www.slimframework.com/docs/concepts/di.html)   |
| `errorHandler`  | Callable invoked if an Exception is thrown. See [Slim doc](http://www.slimframework.com/docs/concepts/di.html)   |
| `notFoundHandler`  | Callable invoked if the current HTTP request URI does not match an application route. See [Slim doc](http://www.slimframework.com/docs/concepts/di.html)  |
| `notAllowedHandler`  | Callable invoked if an application route matches the current HTTP request path but not its method. See [Slim doc](http://www.slimframework.com/docs/concepts/di.html)   |
| `callableResolver`  |  Instance of `\Slim\Interfaces\CallableResolverInterface`. |


## Extended services

This *service provider* registers the `Slim\App` in the `MiddlewareListServiceProvider::MIDDLEWARES_QUEUE`.

| Service name                | Description                          |
|-----------------------------|--------------------------------------|
| `MiddlewareListServiceProvider::MIDDLEWARES_QUEUE`  | Adds the Slim app to this queue (to be used by external routers)  |
