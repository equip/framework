<?php

namespace SparkTests\Responder;

use Spark\Responder\ChainedResponder;

class ChainedResponderTest extends \PHPUnit_Framework_TestCase
{
    private $responder;

    public function setUp()
    {
        $resolver = $this->getMockBuilder('Spark\Resolver\ResolverInterface')->getMock();
        $resolver->method('__invoke')
                 ->will($this->returnCallback(function () {
                       $responder = $this->getMockBuilder('Spark\Adr\ResponderInterface')->getMock();
                       $responder->expects($this->once())
                                 ->method('__invoke')
                                 ->with(
                                     $this->isInstanceOf('Psr\Http\Message\ServerRequestInterface'),
                                     $this->isInstanceOf('Psr\Http\Message\ResponseInterface'),
                                     $this->isInstanceOf('Spark\Adr\PayloadInterface')
                                 )->will($this->returnArgument(1));
                       return $responder;
                 }));

        // Doesn't matter what the responders actually are, since the mock
        // resolver will just return a new mock instance for any value.
        $this->responder = (new ChainedResponder($resolver))->withResponders(['a', 'b', 'c']);
    }

    public function testResponse()
    {
        $request  = $this->getMockBuilder('Psr\Http\Message\ServerRequestInterface')->getMock();
        $response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        $payload  = $this->getMockBuilder('Spark\Adr\PayloadInterface')->getMock();

        $response = call_user_func($this->responder, $request, $response, $payload);

        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);
    }
}
