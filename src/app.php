<?php

use Slim\Http\Request;
use Slim\Http\Response;

require __DIR__ . '/utilities/utils.inc.php';
$mongo = (new MongoDB\Client)->waddle;

$app->get('/', function (Request $request, Response $response, array $args) {
    $template = firstRun();
    return $this->renderer->render($response, $template . '.phtml', $args);
});

$app->get('/mail/{id}', function(Request $request, Response $response, array $args) {
    $mailId = $request->getAttribute('id');
    $args['mail'] = getMailsFromJson()->$mailId;
    return $this->renderer->render($response->withJson($args), 'empty');
});

$app->post('/newuser', function(Request $request, Response $response, array $args) {
    global $mongo;
    $data = $request->getParsedBody();
    $mongo->user->insertOne([
        '_id' => 0,
        'email' => $data['email'],
        'password' => $data['password']
    ]);
    return $response->withRedirect('/');
});

$app->get('/syncmails', function(Request $request, Response $response, array $args) {
    global $mongo;
    $user = $mongo->user->find()->toArray()[0];
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

    $mongo->mails->drop();
    $result = $mongo->mails->insertMany($mails);

    $mongo->columns->updateOne(
        ['name' => 'inbox'],
        ['$set' => ['mails' => $result->getInsertedIds()]],
        ['upsert' => true]
    );

    $args['mails'] = $mails;

    return $this->renderer->render($response->withJson($args), 'empty');
});

$app->get('/getmails', function(Request $request, Response $response, array $args) {
    global $mongo;
    $mailsDecoded = $mongo->mails->find()->toArray();
    $columnsDecoded = setInboxIfEmptyBoard($mongo->columns->find()->toArray(), $mailsDecoded);

    foreach ($columnsDecoded as $value) {
        $mails = [];
        if (count($value['mails']) > 0) foreach ($value['mails'] as $id) {
            $mails[$id] = $mongo->mails->findOne(['_id' => $id]);
        }
        $args['columns'][] = ['title' => $value['name'], 'cards' => $mails];
    }
    return $this->renderer->render($response->withJson($args), 'empty');
});

$app->get('/savedata', function(Request $request, Response $response, array $args){
    $args['debug'] = json_decode($request->getQueryParams());
    return $this->renderer->render($response->withJson($args), 'empty');
});
