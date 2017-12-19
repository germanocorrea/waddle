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

function setInboxIfEmptyBoard($columns, $mailsDecoded) {
    $empty = true;
    foreach ($columns as $key => $mailIds) {
        if (count($mailIds) > 0 ) $empty = false;
    }
    if ($empty) foreach ($mailsDecoded as $key => $value) {
        $columns->inbox[] = $key;
    }
    return $columns;
}

function firstRun() {
    global $database;
    if (empty($database->user->find()->toArray())) {
        $database->columns->insertMany([
            [
                'name' => 'inbox',
                'mails' => []
            ],
            [
                'name' => 'todo',
                'mails' => []
            ],
            [
                'name' => 'doing',
                'mails' => []
            ],
            [
                'name' => 'done',
                'mails' => []
            ],
        ]);

        return 'config';
    } else {
        return 'board';
    }
}
