<?php

namespace Example\Api\Controllers;

use Example\Api\ApiModels\Item;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class ItemController
 * 
 * Handle /item
 * 
 * @package Example\Api\Controllers
 */
class ItemController {

	/**
	 * Dependency injection container
	 * 
	 * @var Container
	 */
	private $ci;

	/**
	 * ItemController constructor
	 * 
	 * @param Container $ci
	 */
	public function __construct(Container $ci) {
		$this->ci = $ci;
	}

	/**
	 * add new item
	 * 
	 * @api-response:default Example\Api\ApiModels\Error generic error
	 * @api-response:400 Example\Api\ApiModels\Error invalid object
	 * @api-response:201 Example\Api\ApiModels\Item Created
	 * @api POST /item
	 * @internal Item $item
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function addItem(Request $request, Response $response, array $args) {
		/** @var Item $item */
		$item = new Item($request->getParsedBody());

		//TODO Method addItem not implemented

		return $response->withStatus(501, 'ItemController::addItem not implemented');
	}

	/**
	 * delete item
	 * 
	 * @api-response:default Example\Api\ApiModels\Error generic error
	 * @api-response:404 Example\Api\ApiModels\Error item not found
	 * @api-response:204 Deleted
	 * @api DELETE /item/{id}
	 * @internal int $id 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function deleteItem(Request $request, Response $response, array $args) {
		/** @var int $id */
		$id = $args['id'];

		//TODO Method deleteItem not implemented

		return $response->withStatus(501, 'ItemController::deleteItem not implemented');
	}

	/**
	 * get item by id
	 * 
	 * @api-response:default Example\Api\ApiModels\Error generic error
	 * @api-response:404 Example\Api\ApiModels\Error item not found
	 * @api-response:200 Example\Api\ApiModels\Item item with specific id
	 * @api GET /item/{id}
	 * @internal int $id 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getItem(Request $request, Response $response, array $args) {
		/** @var int $id */
		$id = $args['id'];

		//TODO Method getItem not implemented

		return $response->withStatus(501, 'ItemController::getItem not implemented');
	}

	/**
	 * update item
	 * 
	 * @api-response:404 Example\Api\ApiModels\Error item not found
	 * @api-response:default Example\Api\ApiModels\Error generic error
	 * @api-response:400 Example\Api\ApiModels\Error invalid object
	 * @api-response:200 Example\Api\ApiModels\Item Ok
	 * @api PUT /item/{id}
	 * @internal int $id 
	 * @internal Item $item
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function updateItem(Request $request, Response $response, array $args) {
		/** @var int $id */
		$id = $args['id'];
		/** @var Item $item */
		$item = new Item($request->getParsedBody());

		//TODO Method updateItem not implemented

		return $response->withStatus(501, 'ItemController::updateItem not implemented');
	}
}