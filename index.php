<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require 'vendor/autoload.php';

class MyDB extends SQLite3 {
    function __construct() {
        $this->open('friends.db');
    }
}

$db = new MyDB();
if (!$db) {
    echo $db->lastErrorMsg();
    exit();
}

$app = new \Slim\App;
$app->get(
    '/friends',
    function (Request $request, Response $response, array $args) use ($db) {
        $sql = "select * from friend";
        $ret = $db->query($sql);
        $friends = [];
        while ($friend = $ret->fetchArray(SQLITE3_ASSOC)) {
            $friends[] = $friend;
        }
        return $response->withJson($friends);
    }
);
$app->get(
    '/friends/{id}',
    function (Request $request, Response $response, array $args) use ($db) {
//        $sql = "select * from friend WHERE id = $args[id]";
//        $ret = $db->query($sql);
        $sql = "select * from friend WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue('id', $args['id']);
        $ret = $stmt->execute();
        $friend = $ret->fetchArray(SQLITE3_ASSOC);
        if ($friend) {
            return $response->withJson($friend);
        } else {
            return $response->withStatus(404)->withJson(['error' => 'Such friend does not exist.']);
        }
    }
);
$app->post(
    '/friends',
    function (Request $request, Response $response, array $args) use ($db) {
        $requestData = $request->getParsedBody();
        if (!isset($requestData['name']) || !isset($requestData['surname'])) {
            return $response->withStatus(400)->withJson(['error' => 'Name and surname are required.']);
        }
        $sql = "insert into 'friend' (name, surname, email) values (:name, :surname, :email)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue('name', $requestData['name']);
        $stmt->bindValue('surname', $requestData['surname']);
        $stmt->bindValue('email', isset($requestData['email']) ? $requestData['email'] : '');
        $stmt->execute();
        $newUserId = $db->lastInsertRowID();
        return $response->withStatus(201)->withHeader('Location', "/friends/$newUserId");
    }
);
$app->run();
