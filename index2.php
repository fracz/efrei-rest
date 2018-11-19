<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
require 'Customer.php';

require 'vendor/autoload.php';

$app = new \Slim\App(['settings' => [
  //  'displayErrorDetails' => true,
],]);
$app->post(
    '/customers',
    function (Request $request, Response $response, array $args) {
        $requestData = $request->getParsedBody();
        $customer = new Customer();
        $customer->name = $requestData['name'];
        $customer->save();
        return $response
            ->withStatus(Slim\Http\StatusCode::HTTP_CREATED)
            ->withHeader('Location', '/customers/' . $customer->id)
            ->withJson($customer);
    }
);

$app->get(
    '/customers/',
    function (Request $request, Response $response, array $args) {
        $customers = Customer::findAll();
        return $response->withJson($customers);
    }
);
$app->get(
    '/customers/{id}',
    function (Request $request, Response $response, array $args) {
        $customer = Customer::find($args['id']);
        if (!$customer) {
            return $response->withStatus(\Slim\Http\StatusCode::HTTP_NOT_FOUND);
        }
        return $response->withJson($customer);
    }
);
$app->put(
    '/customers/{id}',
    function (Request $request, Response $response, array $args) {
        $requestData = $request->getParsedBody();
        $customer = Customer::find($args['id']);
        $customer->name = $requestData['name'];
        $customer->save();
        return $response->withJson($customer);
    }
);
$app->delete(
    '/customers/{id}',
    function (Request $request, Response $response, array $args) {
        $requestData = $request->getParsedBody();
        $customer = Customer::find($args['id']);
//        $customer->delete();
        return $response->withStatus(Slim\Http\StatusCode::HTTP_NO_CONTENT);
    }
);
$app->patch(
    '/customers/{id}',
    function (Request $request, Response $response, array $args) {
        $customer = Customer::find($args['id']);
        $requestData = $request->getParsedBody();
        if ($requestData['action'] == 'ban') {
            $customer->banned = true;
        } elseif ($requestData['action'] == 'unban') {
            $customer->banned = false;
        } else {
            return $response->withStatus(\Slim\Http\StatusCode::HTTP_NOT_FOUND);
        }
        $customer->save();
        return $response->withJson($customer);
    }
);
$app->run();
