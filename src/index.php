<?php
// Instantiate the app
$settings = require __DIR__ . '/slim-settings.php';
$app = new \Slim\App($settings);

require __DIR__ . '/dependencies.php';
require __DIR__ . '/middleware.php';
require __DIR__ . '/app.php';

// Run app
$app->run();
