<?php

namespace Example\Api\Controllers;

use Example\Api\ApiModels\Item;
use Rest\Lib\AbstractController;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class ItemController
 * 
 * Handle /item
 * 
 * @package Example\Api\Controllers
 */
class ItemController extends AbstractController {

	/**
	 * add new item
	 * 
	 * @api POST /item
	 * @internal Item $item
	 * @api-response:201 Example\Api\ApiModels\Item Created
	 * @api-response:400 Example\Api\ApiModels\Error invalid object
	 * @api-response:default Example\Api\ApiModels\Error generic error
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function addItem(Request $request, Response $response, array $args) {
		/** @var Item $item */
		$item = new Item($request->getParsedBody());

		if($item == null) {
		    echo "item is null";
		}

		return $response->withStatus(501, 'ItemController::addItem not implemented');
	}

	/**
	 * delete item
	 * 
	 * @api DELETE /item/{id}
	 * @internal int $id 
	 * @api-response:204 Deleted
	 * @api-response:404 Example\Api\ApiModels\Error item not found
	 * @api-response:default Example\Api\ApiModels\Error generic error
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
	 * @api GET /item/{id}
	 * @internal int $id 
	 * @api-response:200 Example\Api\ApiModels\Item item with specific id
	 * @api-response:404 Example\Api\ApiModels\Error item not found
	 * @api-response:default Example\Api\ApiModels\Error generic error
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
	 * @api PUT /item/{id}
	 * @internal int $id 
	 * @internal Item $item
	 * @api-response:200 Example\Api\ApiModels\Item Ok
	 * @api-response:400 Example\Api\ApiModels\Error invalid object
	 * @api-response:404 Example\Api\ApiModels\Error item not found
	 * @api-response:default Example\Api\ApiModels\Error generic error
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