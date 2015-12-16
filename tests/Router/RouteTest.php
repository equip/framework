<?php
namespace SparkTests\Router;

use PHPUnit_Framework_TestCase as TestCase;
use Spark\Router\Route;

class RouteTest extends TestCase
{
    public function testGetSet()
    {
        $route = new Route('', 'Spark\Input', 'Spark\Responder\Responder');

        $domain = 'SparkTests\Fake\FakeDomain';
        $route->setDomain($domain);
        $this->assertEquals($domain, $route->getDomain());

        $input = 'SparkTests\Fake\FakeInput';
        $route->setInput($input);
        $this->assertEquals($input, $route->getInput());

        $responder = 'SparkTests\Fake\FakeResponder';
        $route->setResponder($responder);
        $this->assertEquals($responder, $route->getResponder());
    }
}
