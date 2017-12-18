<?php


function debug($what) {
    ChromePhp::log($what);
}

function debugEcho($value) {
    echo '<pre>';
    print_r($value);
    echo '</pre>';
    die;
}
