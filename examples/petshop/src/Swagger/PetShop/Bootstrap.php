<?php

namespace Swagger\PetShop;

use HttpException;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Bootstrap
 * 
 * Creating routes and starting application
 * 
 * @package Swagger\PetShop
 */
class Bootstrap {

	const BAD_HTTP_CODES = [400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411,
	                            412, 413, 414, 415, 416, 417, 500, 501, 502, 503, 504, 505];

	/**
	 * Slim application
	 * 
	 * @var App
	 */
	private $app;

	/**
	 * Return generated info from specs
	 * 
	 * @return array
	 */
	public static function getInfo() {
		return array (
		  'version' => '1.0.0',
		  'title' => 'Swagger Petstore',
		  'description' => 'This is a sample server Petstore server.',
		  'termsOfService' => 'http://swagger.io/terms/',
		  'contact' => 
		  array (
		    'email' => 'apiteam@swagger.io',
		  ),
		  'license' => 
		  array (
		    'name' => 'Apache 2.0',
		    'url' => 'http://www.apache.org/licenses/LICENSE-2.0.html',
		  ),
		);
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param callable $next
	 * @return Response
	 */
	public static function processRequest(Request $request, Response $response, callable $next) {
		try {
		    /** @var Response $response */
		    $response = $next($request, $response);
		    if(in_array($response->getStatusCode(), self::BAD_HTTP_CODES)) {
		        throw new HttpException("Generic error", $response->getStatusCode());
		    }
		}
		catch(\Exception $e) {
		    $json = [
		                'message'   => $e->getMessage(),
		                'code'      => $e->getCode(),
		                'exception' => get_class($e)
		            ];
		    return $response
		        ->withStatus(
		            in_array($e->getCode(), self::BAD_HTTP_CODES) ? $e->getCode() : 500)
		        ->withJson($json);
		}
		return $response->withStatus(204, 'Request processed');
	}

	/**
	 * Bootstrap constructor
	 * 
	 * @param App $app
	 */
	public function __construct(App $app = null) {
		$this->app = is_null($app) ? new App() : $app;
		$this->app->add('Swagger\PetShop\Bootstrap::processRequest');
		$this->routeToPetController();
		$this->routeToStoreController();
		$this->routeToUserController();
	}

	/**
	 * Start application
	 */
	public function run() {
		$this->app->run();
	}

	/**
	 * Route to /v2/pet api group
	 */
	private function routeToPetController() {
		$settings = $this->app->getContainer()->get('settings');
		$apiConfig = array (
		  'addPet' => true,
		  'updatePet' => true,
		  'findPetsByStatus' => true,
		  'findPetsByTags' => true,
		  'getPetById' => true,
		  'updatePetWithForm' => true,
		  'deletePet' => true,
		  'uploadFile' => true,
		);
		if(isset($settings['api']) && isset($settings['api']['PetController'])) { 
			$apiConfig = array_merge($apiConfig, $settings['api']['PetController']);
		}
		$this->app->group('/v2/pet', function () use($apiConfig) {
			/** @var App $this */
			if($apiConfig['addPet'])
				$this->post('', '\Swagger\PetShop\Controllers\PetController:addPet');
			if($apiConfig['updatePet'])
				$this->put('', '\Swagger\PetShop\Controllers\PetController:updatePet');
			if($apiConfig['findPetsByStatus'])
				$this->get('/findByStatus', '\Swagger\PetShop\Controllers\PetController:findPetsByStatus');
			if($apiConfig['findPetsByTags'])
				$this->get('/findByTags', '\Swagger\PetShop\Controllers\PetController:findPetsByTags');
			if($apiConfig['getPetById'])
				$this->get('/{petId}', '\Swagger\PetShop\Controllers\PetController:getPetById');
			if($apiConfig['updatePetWithForm'])
				$this->post('/{petId}', '\Swagger\PetShop\Controllers\PetController:updatePetWithForm');
			if($apiConfig['deletePet'])
				$this->delete('/{petId}', '\Swagger\PetShop\Controllers\PetController:deletePet');
			if($apiConfig['uploadFile'])
				$this->post('/{petId}/uploadImage', '\Swagger\PetShop\Controllers\PetController:uploadFile');
		});
	}

	/**
	 * Route to /v2/store api group
	 */
	private function routeToStoreController() {
		$settings = $this->app->getContainer()->get('settings');
		$apiConfig = array (
		  'getInventory' => true,
		  'placeOrder' => true,
		  'getOrderById' => true,
		  'deleteOrder' => true,
		);
		if(isset($settings['api']) && isset($settings['api']['StoreController'])) { 
			$apiConfig = array_merge($apiConfig, $settings['api']['StoreController']);
		}
		$this->app->group('/v2/store', function () use($apiConfig) {
			/** @var App $this */
			if($apiConfig['getInventory'])
				$this->get('/inventory', '\Swagger\PetShop\Controllers\StoreController:getInventory');
			if($apiConfig['placeOrder'])
				$this->post('/order', '\Swagger\PetShop\Controllers\StoreController:placeOrder');
			if($apiConfig['getOrderById'])
				$this->get('/order/{orderId}', '\Swagger\PetShop\Controllers\StoreController:getOrderById');
			if($apiConfig['deleteOrder'])
				$this->delete('/order/{orderId}', '\Swagger\PetShop\Controllers\StoreController:deleteOrder');
		});
	}

	/**
	 * Route to /v2/user api group
	 */
	private function routeToUserController() {
		$settings = $this->app->getContainer()->get('settings');
		$apiConfig = array (
		  'createUser' => true,
		  'createUsersWithArrayInput' => true,
		  'createUsersWithListInput' => true,
		  'loginUser' => true,
		  'logoutUser' => true,
		  'getUserByName' => true,
		  'updateUser' => true,
		  'deleteUser' => true,
		);
		if(isset($settings['api']) && isset($settings['api']['UserController'])) { 
			$apiConfig = array_merge($apiConfig, $settings['api']['UserController']);
		}
		$this->app->group('/v2/user', function () use($apiConfig) {
			/** @var App $this */
			if($apiConfig['createUser'])
				$this->post('', '\Swagger\PetShop\Controllers\UserController:createUser');
			if($apiConfig['createUsersWithArrayInput'])
				$this->post('/createWithArray', '\Swagger\PetShop\Controllers\UserController:createUsersWithArrayInput');
			if($apiConfig['createUsersWithListInput'])
				$this->post('/createWithList', '\Swagger\PetShop\Controllers\UserController:createUsersWithListInput');
			if($apiConfig['loginUser'])
				$this->get('/login', '\Swagger\PetShop\Controllers\UserController:loginUser');
			if($apiConfig['logoutUser'])
				$this->get('/logout', '\Swagger\PetShop\Controllers\UserController:logoutUser');
			if($apiConfig['getUserByName'])
				$this->get('/{username}', '\Swagger\PetShop\Controllers\UserController:getUserByName');
			if($apiConfig['updateUser'])
				$this->put('/{username}', '\Swagger\PetShop\Controllers\UserController:updateUser');
			if($apiConfig['deleteUser'])
				$this->delete('/{username}', '\Swagger\PetShop\Controllers\UserController:deleteUser');
		});
	}
}