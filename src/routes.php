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

$app->get('/mail/{id}', function(Request $request, Response $response, array $args) {
    $mailId = $request->getAttribute('id');
    $args['mail'] = getMailsFromJson()->$mailId;
    return $this->renderer->render($response->withJson($args), 'empty');
});

$app->get('/syncmails', function(Request $request, Response $response, array $args) {
    $this->logger->info("Começando o processo de salvar emails em arquivo JSON");
    $mailbox = new PhpImap\Mailbox('{imap.gmail.com:993/imap/ssl}INBOX', 'email@gmail.com', '', __DIR__ . '/../tmp/');
    $this->logger->info("IMAP conectado com sucesso");
    $mailsIds = $mailbox->searchMailbox('ALL');
    if(!$mailsIds) {
        throw new Exception('Mailbox is empty');
    }

    $this->logger->info("Emails encontrados");

    foreach ($mailsIds as $key => $id) {
        $this->logger->info("Capturando email " . $id);
        $mail = $mailbox->getMail($mailsIds[$key]);
        $this->logger->info("Email " . $id . " retornado com sucesso!");
        $mails[$id] = [
            'id' => $id,
            'subject' => $mail->subject,
            'from' => $mail->fromAddress,
            'html' => $mail->textHtml
        ];
    }
    $this->logger->info("Estrutura do array para salvar em JSON construída com sucesso");

    $file = fopen('data/mails.json', 'w+');
    fwrite($file, json_encode($mails));
    fclose($file);
    $this->logger->info("Arquivo JSON salvo");

    $args['mails'] = $mails;

    return $this->renderer->render($response->withJson($args), 'empty');
});

function getMailsFromJson() {
    $mails = fopen('data/mails.json', 'r');
    $mailsDecoded = json_decode(fread($mails, filesize('data/mails.json')));
    fclose($mails);
    return $mailsDecoded;
}

function getColumnsFromJson() {
    $columns = fopen('data/columns.json', 'r');
    $columnsDecoded = json_decode(fread($columns, filesize('data/columns.json')));
    fclose($columns);
    return $columnsDecoded;
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

$app->get('/getmails', function(Request $request, Response $response, array $args) {
    $mailsDecoded = getMailsFromJson();
    $columnsDecoded = setInboxIfEmptyBoard(getColumnsFromJson(), $mailsDecoded);

    foreach ($columnsDecoded as $title => $mailIds) {
        $mails = [];
        if (count($mailIds) > 0) foreach ($mailIds as $id) {
            $mails[$id] = $mailsDecoded->{$id};
        }

        $args['columns'][] = ['title' => $title, 'cards' => $mails];
    }
    return $this->renderer->render($response->withJson($args), 'empty');
});

$app->get('/savedata', function(Request $request, Response $response, array $args){
    // TODO: como eu faço pra verificar se o JSON foi retornado?
    $args['debug'] = $request->getBody();
    return $this->renderer->render($response->withJson($args), 'empty');
});
