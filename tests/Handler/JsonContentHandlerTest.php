<?php
namespace SparkTests\Handler;

use Spark\Exception\HttpBadRequestException;
use Spark\Handler\JsonContentHandler;
use Zend\Diactoros\Response;

class JsonContentHandlerTest extends ContentHandlerTestCase
{
    public function testInvokeWithApplicableMimeType()
    {
        $request = $this->getRequest(
            $mime = 'application/json',
            json_encode($body = (object) ['test' => 'json'])
        );
        $response = new Response;
        $handler = new JsonContentHandler;
        $resolved = $handler($request, $response, function ($req, $res) use ($mime, $body) {
            $this->assertSame($mime, $req->getHeaderLine('Content-Type'));
            $this->assertEquals($body, $req->getParsedBody());
            return $res;
        });
    }

    public function testInvokeWithMalformedBody()
    {
        $this->setExpectedException(
            HttpBadRequestException::class,
            'Error parsing JSON: Syntax error'
        );

        $request = $this->getRequest(
            $mime = 'application/json',
            $body = '{'
        );
        $response = new Response;
        $handler = new JsonContentHandler;
        $resolved = $handler($request, $response, function ($req, $res) {
            $this->fail('Handler callback unexpectedly invoked');
        });
    }

    public function testInvokeWithNonApplicableMimeType()
    {
        $request = $this->getRequest(
            $mime = 'application/x-www-form-urlencoded',
            $body = http_build_query(['test' => 'form'], '', '&')
        );
        $response = new Response;
        $handler = new JsonContentHandler;
        $resolved = $handler($request, $response, function ($req, $res) use ($mime) {
            $this->assertSame($mime, $req->getHeaderLine('Content-Type'));
            $this->assertNull($req->getParsedBody());
            return $res;
        });
    }
}
