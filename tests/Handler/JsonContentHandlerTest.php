<?php
namespace EquipTests\Handler;

use Equip\Exception\HttpBadRequestException;
use Equip\Handler\JsonContentHandler;
use Zend\Diactoros\Response;

class JsonContentHandlerTest extends ContentHandlerTestCase
{
    public function testInvokeWithApplicableMimeType()
    {
        $request = $this->getRequest(
            $mime = 'application/json',
            json_encode($body = ['test' => 'json'])
        );
        $response = new Response;
        $handler = new JsonContentHandler;
        $resolved = $handler($request, $response, function ($req, $res) use ($mime, $body) {
            $this->assertSame($mime, $req->getHeaderLine('Content-Type'));
            $this->assertEquals($body, $req->getParsedBody());
            return $res;
        });
    }

    /**
     * @expectedException \Equip\Exception\HttpException
     * @expectedExceptionCode 400
     * @expectedExceptionMessageRegExp /json syntax error/i
     */
    public function testInvokeWithMalformedBody()
    {
        $request = $this->getRequest(
            $mime = 'application/json',
            $body = '{not json}'
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
