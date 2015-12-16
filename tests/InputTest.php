<?php

namespace SparkTests;

use Psr\Http\Message\UploadedFileInterface;
use Spark\Input;
use Zend\Diactoros\ServerRequest;

class InputTest extends \PHPUnit_Framework_TestCase
{
    public function testCollectEmptyRequest()
    {
        $found = $this->execute(new ServerRequest);
        $this->assertEmpty($found);
    }

    public function testQueryParams()
    {
        $query = [
            'query' => 'string',
        ];

        $request = new ServerRequest;
        $request = $request->withQueryParams($query);

        $found = $this->execute($request);
        $this->assertSame($query, $found);
    }

    public function testParsedBody()
    {
        $body = [
            'body' => 'parsed',
        ];

        $request = new ServerRequest;
        $request = $request->withParsedBody($body);

        $found = $this->execute($request);
        $this->assertSame($body, $found);
    }

    public function testUploadedFiles()
    {
        $files = [
            'file' => $this->getMock(UploadedFileInterface::class),
        ];

        $request = new ServerRequest;
        $request = $request->withUploadedFiles($files);

        $found = $this->execute($request);
        $this->assertSame($files, $found);
    }

    public function testCookieParams()
    {
        $cookies = [
            'cookie' => 'nomnomnom',
        ];

        $request = new ServerRequest;
        $request = $request->withCookieParams($cookies);

        $found = $this->execute($request);
        $this->assertSame($cookies, $found);
    }

    public function testAttributes()
    {
        $attrs = [
            'attr' => 'stored',
        ];

        $request = new ServerRequest;
        foreach ($attrs as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $found = $this->execute($request);
        $this->assertSame($attrs, $found);
    }

    public function testMerge()
    {
        $request = new ServerRequest;

        $value = [
            'merge' => 'query',
        ];
        $request = $request->withQueryParams($value);
        $this->assertSame($value, $this->execute($request));

        $value = [
            'merge' => 'body',
        ];
        $request = $request->withParsedBody($value);
        $this->assertSame($value, $this->execute($request));

        $value = [
            'merge' => $this->getMock(UploadedFileInterface::class),
        ];
        $request = $request->withParsedBody($value);
        $this->assertSame($value, $this->execute($request));

        $value = [
            'merge' => 'cookie',
        ];
        $request = $request->withCookieParams($value);
        $this->assertSame($value, $this->execute($request));

        $value = [
            'merge' => 'attr',
        ];
        $request = $request->withAttribute(key($value), current($value));
        $this->assertSame($value, $this->execute($request));
    }

    /**
     * Collect input from the request
     *
     * @param ServerRequest $request
     *
     * @return array
     */
    private function execute(ServerRequest $request)
    {
        return call_user_func(new Input, $request);
    }
}
