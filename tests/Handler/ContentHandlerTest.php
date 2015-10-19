<?php
namespace SparkTests\Handler;

use PHPUnit_Framework_TestCase as TestCase;
use Spark\Handler\ContentHandler;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;

class ContentHandlerTest extends TestCase
{
    private $stream;

    public function setUp()
    {
        $this->stream = new Stream('php://memory', 'w+');
    }

    public function testParseJsonBody()
    {
        $content = [
            'test' => 'json',
        ];

        $this->stream->write(json_encode($content));

        $mime = 'application/json';

        $request = new ServerRequest(
            $server  = [],
            $upload  = [],
            $path    = '/',
            $method  = 'POST',
            $body    = $this->stream,
            $headers = [
                'Content-Type' => $mime,
            ]
        );

        $response = new Response;

        $handler = new ContentHandler;

        $handler($request, $response, function ($req, $res) use ($content, $mime) {
            $this->assertSame($mime, $req->getHeaderLine('Content-Type'));
            $this->assertSame($content, $req->getParsedBody());

            return $res;
        });

        // Same test, slightly different content type
        $mime    = 'application/vnd.api+json';
        $request = $request->withHeader('Content-Type', $mime);

        $handler($request, $response, function ($req, $res) use ($content, $mime) {
            $this->assertSame($mime, $req->getHeaderLine('Content-Type'));
            $this->assertSame($content, $req->getParsedBody());

            return $res;
        });
    }

    public function testParseFormBody()
    {
        $content = [
            'test' => 'json',
        ];

        $this->stream->write(http_build_query($content, '', '&'));

        $mime = 'application/x-www-form-urlencoded';

        $request = new ServerRequest(
            $server  = [],
            $upload  = [],
            $path    = '/',
            $method  = 'POST',
            $body    = $this->stream,
            $headers = [
                'Content-Type' => $mime,
            ]
        );

        $response = new Response;

        $handler = new ContentHandler;

        $resolved = $handler($request, $response, function ($req, $res) use ($content, $mime) {
            $this->assertSame($mime, $req->getHeaderLine('Content-Type'));
            $this->assertSame($content, $req->getParsedBody());

            return $res;
        });
    }
}
