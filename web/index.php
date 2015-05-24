<?php

require __DIR__ . '/../vendor/autoload.php';

define('APP_PATH',realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);

$app = new Spark\Application();

$app->addRoutes(function(\Spark\Router\Router $r)
{
    $r->get('/', '\Spark\Domain\Hello');
    $r->get('/{user_id}', '\Spark\Domain\Hello');
});

$app->run();