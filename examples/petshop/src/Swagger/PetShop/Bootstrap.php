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