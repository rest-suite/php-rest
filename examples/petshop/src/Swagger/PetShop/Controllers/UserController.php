<?php

namespace Swagger\PetShop\Controllers;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Swagger\PetShop\ApiModels\User;

/**
 * Class UserController
 * 
 * Handle /v2/user
 * 
 * @package Swagger\PetShop\Controllers
 */
class UserController {

	/**
	 * Dependency injection container
	 * 
	 * @var Container
	 */
	private $ci;

	/**
	 * UserController constructor
	 * 
	 * @param Container $ci
	 */
	public function __construct(Container $ci) {
		$this->ci = $ci;
	}

	/**
	 * Create user
	 * 
	 * This can only be done by the logged in user.
	 * 
	 * @api-response:default successful operation
	 * @internal User $user
	 * @api POST /user
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function createUser(Request $request, Response $response, array $args) {
		$user = new User($request->getParsedBody());

		//TODO Method createUser not implemented
		$response = $response->withStatus(501, 'UserController::createUser not implemented');

		return $response;
	}

	/**
	 * Creates list of users with given input array
	 * 
	 * @api-response:default successful operation
	 * @api POST /user/createWithArray
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function createUsersWithArrayInput(Request $request, Response $response, array $args) {
		//TODO Method createUsersWithArrayInput not implemented
		$response = $response->withStatus(501, 'UserController::createUsersWithArrayInput not implemented');

		return $response;
	}

	/**
	 * Creates list of users with given input array
	 * 
	 * @api-response:default successful operation
	 * @api POST /user/createWithList
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function createUsersWithListInput(Request $request, Response $response, array $args) {
		//TODO Method createUsersWithListInput not implemented
		$response = $response->withStatus(501, 'UserController::createUsersWithListInput not implemented');

		return $response;
	}

	/**
	 * Delete user
	 * 
	 * This can only be done by the logged in user.
	 * 
	 * @api-response:404 User not found
	 * @api-response:400 Invalid username supplied
	 * @internal string $username The name that needs to be deleted
	 * @api DELETE /user/{username}
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function deleteUser(Request $request, Response $response, array $args) {
		$username = $args['username'];

		//TODO Method deleteUser not implemented
		$response = $response->withStatus(501, 'UserController::deleteUser not implemented');

		return $response;
	}

	/**
	 * Get user by user name
	 * 
	 * @api-response:404 User not found
	 * @api-response:400 Invalid username supplied
	 * @api-response:200 Swagger\PetShop\ApiModels\User successful operation
	 * @api GET /user/{username}
	 * @internal string $username The name that needs to be fetched. Use user1 for testing. 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getUserByName(Request $request, Response $response, array $args) {
		$username = $args['username'];

		//TODO Method getUserByName not implemented
		$response = $response->withStatus(501, 'UserController::getUserByName not implemented');

		return $response;
	}

	/**
	 * Logs user into the system
	 * 
	 * @api-response:400 Invalid username/password supplied
	 * @api-response:200 successful operation
	 * @internal string $username The user name for login
	 * @internal string $password The password for login in clear text
	 * @api GET /user/login
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function loginUser(Request $request, Response $response, array $args) {
		$username = $request->getQueryParam('username', null);
		$password = $request->getQueryParam('password', null);

		//TODO Method loginUser not implemented
		$response = $response->withStatus(501, 'UserController::loginUser not implemented');

		return $response;
	}

	/**
	 * Logs out current logged in user session
	 * 
	 * @api-response:default successful operation
	 * @api GET /user/logout
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function logoutUser(Request $request, Response $response, array $args) {
		//TODO Method logoutUser not implemented
		$response = $response->withStatus(501, 'UserController::logoutUser not implemented');

		return $response;
	}

	/**
	 * Updated user
	 * 
	 * This can only be done by the logged in user.
	 * 
	 * @api-response:404 User not found
	 * @api-response:400 Invalid user supplied
	 * @internal string $username name that need to be updated
	 * @internal User $user
	 * @api PUT /user/{username}
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function updateUser(Request $request, Response $response, array $args) {
		$username = $args['username'];
		$user = new User($request->getParsedBody());

		//TODO Method updateUser not implemented
		$response = $response->withStatus(501, 'UserController::updateUser not implemented');

		return $response;
	}
}