<?php

namespace EquipTests\Handler;

use EquipTests\DirectoryTestCase;
use Equip\Directory;
use Equip\Handler\ActionHandler;
use Equip\Handler\DispatchHandler;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

class DispatchHandlerTest extends DirectoryTestCase
{
    /**
     * @var Directory
     */
    private $directory;

    protected function setUp()
    {
        $this->directory = new Directory;
    }

    public function testHandle()
    {
        $action = $this->getMockAction();
        $directory = $this->directory->get('/[{name}]', $action);
        $request = $this->getRequest('GET', '/tester');
        $response = new Response;

        $next = function (ServerRequest $request, Response $response) use ($action) {
            $this->assertSame($action, $request->getAttribute(ActionHandler::ACTION_ATTRIBUTE));
            $this->assertSame('tester', $request->getAttribute('name'));
            return $response;
        };

        $this->dispatch($directory, $request, $response, $next);
    }

    /**
     * @expectedException \Equip\Exception\HttpException
     * @expectedExceptionRegExp /cannot find any resource at/i
     */
    public function testNotFoundException()
    {
        $handler = new DispatchHandler($this->directory);
        $request = $this->getRequest('GET', '/');
        $response = new Response;

        return $this->dispatch(
            $this->directory,
            $request,
            $response,
            function ($request, $response) {
                return $response;
            }
        );
    }

    /**
     * @expectedException \Equip\Exception\HttpException
     * @expectedExceptionRegExp /cannot access resource .* using method/i
     */
    public function testMethodNotAllowedException()
    {
        $handler = new DispatchHandler($this->directory);
        $request = $this->getRequest('POST');
        $response = new Response;

        $directory = $this->directory->get('/', $this->getMockAction());

        return $this->dispatch(
            $directory,
            $request,
            $response,
            function ($request, $response) {
                return $response;
            }
        );
    }

    /**
     * @return Response
     */
    private function dispatch(
        Directory $directory,
        ServerRequest $request,
        Response $response,
        callable $next
    ) {
        $dispatcher = new DispatchHandler($directory);
        return $dispatcher($request, $response, $next);
    }

    /**
     * @param string $method
     * @param string $path
     *
     * @return ServerRequest
     */
    private function getRequest($method = 'GET', $path = '/')
    {
        return (new ServerRequest)
            ->withMethod($method)
            ->withUri(new Uri($path));
    }
}
