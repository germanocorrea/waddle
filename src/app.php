<?php

use Slim\Http\Request;
use Slim\Http\Response;

$database = (new MongoDB\Client)->waddle;
require __DIR__ . '/utilities/utils.inc.php';

$app->get('/', function (Request $request, Response $response, array $args) {
    $template = firstRun();
    return $this->renderer->render($response, $template . '.phtml', $args);
});

$app->get('/mail/{id}', function(Request $request, Response $response, array $args) {
    global $database;
    $mailId = $request->getAttribute('id');
    $args['mail'] = $database->mails->findOne(['_id' => (int) $mailId]);
    return $this->renderer->render($response->withJson($args), 'empty');
});

$app->post('/newuser', function(Request $request, Response $response, array $args) {
    global $database;
    $data = $request->getParsedBody();
    $database->user->insertOne([
        '_id' => 0,
        'email' => $data['email'],
        'password' => $data['password']
    ]);
    return $response->withRedirect('/');
});

$app->get('/syncmails', function(Request $request, Response $response, array $args) {
    global $database;
    $user = $database->user->find()->toArray()[0];
    $mailbox = new PhpImap\Mailbox('{imap.gmail.com:993/imap/ssl}INBOX', $user['email'], $user['password'], __DIR__ . '/../tmp/');
    $mailsIds = $mailbox->searchMailbox('ALL');
    if(!$mailsIds) {
        throw new Exception('Mailbox is empty');
    }

    foreach ($mailsIds as $key => $id) {
        $mail = $mailbox->getMail($mailsIds[$key]);
        $mails[] = [
            '_id' => $id,
            'subject' => $mail->subject,
            'from' => $mail->fromAddress,
            'html' => $mail->textHtml
        ];
    }

    $database->mails->drop();
    $result = $database->mails->insertMany($mails);

    $database->columns->updateOne(
        ['name' => 'inbox'],
        ['$set' => ['mails' => $result->getInsertedIds()]],
        ['upsert' => true]
    );

    $args['mails'] = $mails;

    return $this->renderer->render($response->withJson($args), 'empty');
});

$app->get('/getmails', function(Request $request, Response $response, array $args) {
    global $database;
    $mailsDecoded = $database->mails->find()->toArray();
    $columnsDecoded = setInboxIfEmptyBoard($database->columns->find()->toArray(), $mailsDecoded);

    foreach ($columnsDecoded as $value) {
        $mails = [];
        if (count($value['mails']) > 0) foreach ($value['mails'] as $id) {
            $mails[$id] = $database->mails->findOne(['_id' => $id]);
        }
        $args['columns'][] = ['title' => $value['name'], 'cards' => $mails];
    }
    return $this->renderer->render($response->withJson($args), 'empty');
});

$app->get('/savedata', function(Request $request, Response $response, array $args){
    $args['debug'] = json_decode($request->getQueryParams());
    return $this->renderer->render($response->withJson($args), 'empty');
});
