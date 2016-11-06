<?php

namespace Swagger\PetShop\Controllers;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Swagger\PetShop\ApiModels\Order;

/**
 * Class StoreController
 * 
 * Handle /v2/store
 * 
 * @package Swagger\PetShop\Controllers
 */
class StoreController {

	/**
	 * Dependency injection container
	 * 
	 * @var Container
	 */
	private $ci;

	/**
	 * StoreController constructor
	 * 
	 * @param Container $ci
	 */
	public function __construct(Container $ci) {
		$this->ci = $ci;
	}

	/**
	 * Delete purchase order by ID
	 * 
	 * For valid response try integer IDs with positive integer value. Negative or non-integer values will generate API errors
	 * 
	 * @api-response:404 Order not found
	 * @api-response:400 Invalid ID supplied
	 * @internal int $orderId ID of the order that needs to be deleted
	 * @api DELETE /store/order/{orderId}
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function deleteOrder(Request $request, Response $response, array $args) {
		/** @var int $orderId */
		$orderId = $args['orderId'];

		//TODO Method deleteOrder not implemented

		return $response->withStatus(501, 'StoreController::deleteOrder not implemented');
	}

	/**
	 * Returns pet inventories by status
	 * 
	 * Returns a map of status codes to quantities
	 * 
	 * @api-response:200 successful operation
	 * @api GET /store/inventory
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getInventory(Request $request, Response $response, array $args) {
		//TODO Method getInventory not implemented

		return $response->withStatus(501, 'StoreController::getInventory not implemented');
	}

	/**
	 * Find purchase order by ID
	 * 
	 * For valid response try integer IDs with value >= 1 and <= 10. Other values will generated exceptions
	 * 
	 * @api-response:404 Order not found
	 * @api-response:400 Invalid ID supplied
	 * @api-response:200 Swagger\PetShop\ApiModels\Order successful operation
	 * @api GET /store/order/{orderId}
	 * @internal int $orderId ID of pet that needs to be fetched
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getOrderById(Request $request, Response $response, array $args) {
		/** @var int $orderId */
		$orderId = $args['orderId'];

		//TODO Method getOrderById not implemented

		return $response->withStatus(501, 'StoreController::getOrderById not implemented');
	}

	/**
	 * Place an order for a pet
	 * 
	 * @api-response:400 Invalid Order
	 * @api-response:200 Swagger\PetShop\ApiModels\Order successful operation
	 * @internal Order $order
	 * @api POST /store/order
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function placeOrder(Request $request, Response $response, array $args) {
		/** @var Order $order */
		$order = new Order($request->getParsedBody());

		//TODO Method placeOrder not implemented

		return $response->withStatus(501, 'StoreController::placeOrder not implemented');
	}
}