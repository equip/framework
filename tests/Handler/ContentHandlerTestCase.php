<?php
namespace SparkTests\Handler;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;

abstract class ContentHandlerTestCase extends TestCase
{
    /**
     * @param string $mime
     * @param string $body
     * @return ServerRequest
     */
    protected function getRequest($mime, $body)
    {
        $stream = new Stream('php://memory', 'w+');
        $stream->write($body);
        return new ServerRequest(
            $server  = [],
            $upload  = [],
            $path    = '/',
            $method  = 'POST',
            $body    = $stream,
            $headers = [
                'Content-Type' => $mime,
            ]
        );
    }
}
