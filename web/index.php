<?php

require __DIR__ . '/../vendor/autoload.php';

define('APP_PATH',realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);

$app = new Spark\Application();

$app->addRoutes(function(\Spark\Router $r)
{
    $r->get('/', '\Spark\Action\Hello');
    $r->get('/{user_id}', '\Spark\Action\Hello');
});

$app->run();