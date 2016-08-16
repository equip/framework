<?php
namespace EquipTests\Handler;

use Equip\Contract\ActionInterface;
use Equip\Configuration\AurynConfiguration;
use Equip\Handler\ActionHandler;
use EquipTests\Configuration\ConfigurationTestCase;
use EquipTests\Fake\FakeDomain;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class ActionHandlerTest extends ConfigurationTestCase
{
    protected function getConfigurations()
    {
        return [
            new AurynConfiguration,
        ];
    }

    public function testHandle()
    {
        $request = $this->injector->make(ServerRequest::class);
        $response = $this->injector->make(Response::class);
        $handler = $this->injector->make(ActionHandler::class);

        $action = $this->createMock(ActionInterface::class);

        $action
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn($response);

        $request = $request->withAttribute(ActionHandler::ACTION_ATTRIBUTE, $action);

        $response = $handler($request, $response, function ($request, $response) {
            $this->assertInstanceOf(Response::class, $response);
            return $response;
        });
    }
}
