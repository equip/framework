<?php

namespace Equip\Handler;

use Equip\Directory;
use Equip\Exception\HttpException;
use Equip\Handler\ActionHandler;
use FastRoute;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DispatchHandler
{
    /**
     * @var Directory
     */
    private $directory;

    /**
     * @param Directory $directory
     */
    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        /**
         * @var $action Equip\Action
         */
        list($action, $args) = $this->dispatch(
            $this->dispatcher(),
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        $request = $request->withAttribute(ActionHandler::ACTION_ATTRIBUTE, $action);

        foreach ($args as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        return $next($request, $response);
    }

    /**
     * @return Dispatcher
     */
    protected function dispatcher()
    {
        return FastRoute\simpleDispatcher(function (RouteCollector $collector) {
            foreach ($this->directory as $request => $action) {
                list($method, $path) = explode(' ', $request, 2);

                $collector->addRoute(
                    $method,
                    $this->directory->prefix($path),
                    $action
                );
            }
        });
    }

    /**
     * @throws HttpNotFound
     * @throws HttpMethodNotAllowed
     *
     * @param Dispatcher $dispatcher
     * @param string $method
     * @param string $path
     *
     * @return array [Action, $arguments]
     */
    private function dispatch(Dispatcher $dispatcher, $method, $path)
    {
        $route = $dispatcher->dispatch($method, $path);
        $status = array_shift($route);

        if (Dispatcher::FOUND === $status) {
            return $route;
        }

        if (Dispatcher::METHOD_NOT_ALLOWED === $status) {
            $allowed = array_shift($route);
            throw HttpException::methodNotAllowed($path, $method, $allowed);
        }

        throw HttpException::notFound($path);
    }
}
