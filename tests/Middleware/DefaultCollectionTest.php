<?php

namespace SparkTests\Middleware;

use PHPUnit_Framework_TestCase as TestCase;
use Spark\Middleware\DefaultCollection;

class DefaultCollectionTest extends TestCase
{
    public function testDefault()
    {
        $collection = new DefaultCollection;

        $expected = [
            'Relay\Middleware\ResponseSender',
            'Spark\Handler\ActionHandler',
            'Spark\Handler\ExceptionHandler',
            'Spark\Handler\FormContentHandler',
            'Spark\Handler\JsonContentHandler',
            'Spark\Handler\RouteHandler',
        ];

        foreach ($expected as $class) {
            $this->assertContains($class, $collection);
        }

        $this->assertCount(count($expected), $collection);
    }
}
