<?php

use Slim\Http\Request;
use Slim\Http\Response;

function debug($value) {
    echo '<pre>';
    print_r($value);
    echo '</pre>';
    die;
}

// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});
