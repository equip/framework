<?php
namespace SparkTests\Handler;

use Spark\Handler\FormContentHandler;
use Zend\Diactoros\Response;

class FormContentHandlerTest extends ContentHandlerTestCase
{
    public function testInvokeWithApplicableMimeType()
    {
        $request = $this->getRequest(
            $mime = 'application/x-www-form-urlencoded',
            http_build_query($body = ['test' => 'form'], '', '&')
        );
        $response = new Response;
        $handler = new FormContentHandler;
        $resolved = $handler($request, $response, function ($req, $res) use ($mime, $body) {
            $this->assertSame($mime, $req->getHeaderLine('Content-Type'));
            $this->assertSame($body, $req->getParsedBody());
            return $res;
        });
    }

    public function testInvokeWithNonApplicableMimeType()
    {
        $request = $this->getRequest(
            $mime = 'application/json',
            $body = json_encode((object) ['test' => 'json'])
        );
        $response = new Response;
        $handler = new FormContentHandler;
        $resolved = $handler($request, $response, function ($req, $res) use ($mime) {
            $this->assertSame($mime, $req->getHeaderLine('Content-Type'));
            $this->assertNull($req->getParsedBody());
            return $res;
        });
    }
}
