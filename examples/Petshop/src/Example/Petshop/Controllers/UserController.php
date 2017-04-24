<?php

namespace Example\Petshop\Controllers;

use Example\Petshop\ApiModels\User;
use Rest\Lib\AbstractController;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class UserController
 * 
 * Handle /v2/user
 * 
 * @package Example\Petshop\Controllers
 */
class UserController extends AbstractController {

	/**
	 * Create user
	 * 
	 * This can only be done by the logged in user.
	 * 
	 * @internal User $user
	 * @api POST /user
	 * @api-response:default successful operation
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function createUser(Request $request, Response $response, array $args) {
		/** @var User $user */
		$user = new User($request->getParsedBody());

		//TODO Method createUser not implemented

		return $response->withStatus(501, 'UserController::createUser not implemented');
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

		return $response->withStatus(501, 'UserController::createUsersWithArrayInput not implemented');
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

		return $response->withStatus(501, 'UserController::createUsersWithListInput not implemented');
	}

	/**
	 * Delete user
	 * 
	 * This can only be done by the logged in user.
	 * 
	 * @api DELETE /user/{username}
	 * @internal string $username The name that needs to be deleted
	 * @api-response:400 Invalid username supplied
	 * @api-response:404 User not found
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function deleteUser(Request $request, Response $response, array $args) {
		/** @var string $username */
		$username = $args['username'];

		//TODO Method deleteUser not implemented

		return $response->withStatus(501, 'UserController::deleteUser not implemented');
	}

	/**
	 * Get user by user name
	 * 
	 * @api GET /user/{username}
	 * @internal string $username The name that needs to be fetched. Use user1 for testing. 
	 * @api-response:200 Example\Petshop\ApiModels\User successful operation
	 * @api-response:400 Invalid username supplied
	 * @api-response:404 User not found
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getUserByName(Request $request, Response $response, array $args) {
		/** @var string $username */
		$username = $args['username'];

		//TODO Method getUserByName not implemented

		return $response->withStatus(501, 'UserController::getUserByName not implemented');
	}

	/**
	 * Logs user into the system
	 * 
	 * @api GET /user/login
	 * @internal string $username The user name for login
	 * @internal string $password The password for login in clear text
	 * @api-response:200 successful operation
	 * @api-response:400 Invalid username/password supplied
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function loginUser(Request $request, Response $response, array $args) {
		/** @var string $username */
		$username = $request->getQueryParam('username', null);
		/** @var string $password */
		$password = $request->getQueryParam('password', null);

		//TODO Method loginUser not implemented

		return $response->withStatus(501, 'UserController::loginUser not implemented');
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

		return $response->withStatus(501, 'UserController::logoutUser not implemented');
	}

	/**
	 * Updated user
	 * 
	 * This can only be done by the logged in user.
	 * 
	 * @api PUT /user/{username}
	 * @internal string $username name that need to be updated
	 * @internal User $user
	 * @api-response:400 Invalid user supplied
	 * @api-response:404 User not found
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function updateUser(Request $request, Response $response, array $args) {
		/** @var string $username */
		$username = $args['username'];
		/** @var User $user */
		$user = new User($request->getParsedBody());

		//TODO Method updateUser not implemented

		return $response->withStatus(501, 'UserController::updateUser not implemented');
	}
}