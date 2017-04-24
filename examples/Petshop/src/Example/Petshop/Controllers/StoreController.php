<?php

namespace Example\Petshop\Controllers;

use Example\Petshop\ApiModels\Order;
use Rest\Lib\AbstractController;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class StoreController
 * 
 * Handle /v2/store
 * 
 * @package Example\Petshop\Controllers
 */
class StoreController extends AbstractController {

	/**
	 * Delete purchase order by ID
	 * 
	 * For valid response try integer IDs with positive integer value. Negative or non-integer values will generate API errors
	 * 
	 * @api DELETE /store/order/{orderId}
	 * @internal int $orderId ID of the order that needs to be deleted
	 * @api-response:400 Invalid ID supplied
	 * @api-response:404 Order not found
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
	 * @api GET /store/order/{orderId}
	 * @internal int $orderId ID of pet that needs to be fetched
	 * @api-response:200 Example\Petshop\ApiModels\Order successful operation
	 * @api-response:400 Invalid ID supplied
	 * @api-response:404 Order not found
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
	 * @api POST /store/order
	 * @internal Order $order
	 * @api-response:200 Example\Petshop\ApiModels\Order successful operation
	 * @api-response:400 Invalid Order
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