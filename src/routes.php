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

$app->get('/', function (Request $request, Response $response, array $args) {
    return $this->renderer->render($response, 'board.phtml', $args);
});

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});
