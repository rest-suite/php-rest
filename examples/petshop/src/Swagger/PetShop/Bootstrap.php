<?php

namespace Swagger\PetShop;

use Slim\App;

/**
 * Class Bootstrap
 * 
 * Creating routes and starting application
 * 
 * @package Swagger\PetShop
 */
class Bootstrap {

	/**
	 * Slim application
	 * 
	 * @var App
	 */
	private $app;

	/**
	 * Bootstrap constructor
	 * 
	 * @param App $app
	 */
	public function __construct(App $app = null) {
		$this->app = is_null($app) ? new App() : $app;
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
		$this->app->group('/v2/pet', function () {
			/** @var App $this */
			$this->post('', '\Swagger\PetShop\Controllers\PetController:addPet');
			$this->put('', '\Swagger\PetShop\Controllers\PetController:updatePet');
			$this->get('/findByStatus', '\Swagger\PetShop\Controllers\PetController:findPetsByStatus');
			$this->get('/findByTags', '\Swagger\PetShop\Controllers\PetController:findPetsByTags');
			$this->get('/{petId}', '\Swagger\PetShop\Controllers\PetController:getPetById');
			$this->post('/{petId}', '\Swagger\PetShop\Controllers\PetController:updatePetWithForm');
			$this->delete('/{petId}', '\Swagger\PetShop\Controllers\PetController:deletePet');
			$this->post('/{petId}/uploadImage', '\Swagger\PetShop\Controllers\PetController:uploadFile');
		});
	}

	/**
	 * Route to /v2/store api group
	 */
	private function routeToStoreController() {
		$this->app->group('/v2/store', function () {
			/** @var App $this */
			$this->get('/inventory', '\Swagger\PetShop\Controllers\StoreController:getInventory');
			$this->post('/order', '\Swagger\PetShop\Controllers\StoreController:placeOrder');
			$this->get('/order/{orderId}', '\Swagger\PetShop\Controllers\StoreController:getOrderById');
			$this->delete('/order/{orderId}', '\Swagger\PetShop\Controllers\StoreController:deleteOrder');
		});
	}

	/**
	 * Route to /v2/user api group
	 */
	private function routeToUserController() {
		$this->app->group('/v2/user', function () {
			/** @var App $this */
			$this->post('', '\Swagger\PetShop\Controllers\UserController:createUser');
			$this->post('/createWithArray', '\Swagger\PetShop\Controllers\UserController:createUsersWithArrayInput');
			$this->post('/createWithList', '\Swagger\PetShop\Controllers\UserController:createUsersWithListInput');
			$this->get('/login', '\Swagger\PetShop\Controllers\UserController:loginUser');
			$this->get('/logout', '\Swagger\PetShop\Controllers\UserController:logoutUser');
			$this->get('/{username}', '\Swagger\PetShop\Controllers\UserController:getUserByName');
			$this->put('/{username}', '\Swagger\PetShop\Controllers\UserController:updateUser');
			$this->delete('/{username}', '\Swagger\PetShop\Controllers\UserController:deleteUser');
		});
	}
}