<?php

namespace SparkTests\Handler;

use PHPUnit_Framework_TestCase as TestCase;
use Spark\Handler\ExceptionHandlerPreferences;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\SoapHandler;
use Whoops\Handler\XmlResponseHandler;

class ExceptionHandlerPreferencesTest extends TestCase
{
    public function testConstruct()
    {
        $prefs = new ExceptionHandlerPreferences;

        $this->assertNotContains(SoapHandler::class, $prefs);

        $expected = [
            PrettyPageHandler::class,
            JsonResponseHandler::class,
            XmlResponseHandler::class,
            PlainTextHandler::class,
        ];

        foreach ($expected as $handler) {
            $this->assertContains($handler, $prefs);
        }

        $types = [
            'text/html',
            'application/javascript',
            'application/json',
            'applicaiton/ld+json',
            'application/vnd.api+json',
            'application/vnd.geo+json',
            'application/xml',
            'application/atom+xml',
            'application/rss+xml',
            'text/plain',
        ];

        foreach ($types as $type) {
            $this->assertArrayHasKey($type, $prefs);
        }
    }
}
