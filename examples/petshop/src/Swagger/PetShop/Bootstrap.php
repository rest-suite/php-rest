<?php

namespace Swagger\PetShop;

use Rest\Lib\AbstractBootstrap;

/**
 * Class Bootstrap
 * 
 * Creating routes and starting application
 * 
 * @package Swagger\PetShop
 */
class Bootstrap extends AbstractBootstrap {

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
	 * @return array
	 */
	public function defaultSettings() {
		return array (
		  'Swagger\\PetShop\\Controllers\\PetController' => 
		  array (
		    'addPet' => true,
		    'updatePet' => true,
		    'findPetsByStatus' => true,
		    'findPetsByTags' => true,
		    'getPetById' => true,
		    'updatePetWithForm' => true,
		    'deletePet' => true,
		    'uploadFile' => true,
		  ),
		  'Swagger\\PetShop\\Controllers\\StoreController' => 
		  array (
		    'getInventory' => true,
		    'placeOrder' => true,
		    'getOrderById' => true,
		    'deleteOrder' => true,
		  ),
		  'Swagger\\PetShop\\Controllers\\UserController' => 
		  array (
		    'createUser' => true,
		    'createUsersWithArrayInput' => true,
		    'createUsersWithListInput' => true,
		    'loginUser' => true,
		    'logoutUser' => true,
		    'getUserByName' => true,
		    'updateUser' => true,
		    'deleteUser' => true,
		  ),
		);
	}

	public function loadConfigs() {
		$result = [];
		$result['api'] = array_merge($result['api'], $this->loadConfig('config/api.php'));
		return $result;
	}

	/**
	 * Setup routes. Generated
	 */
	public function setUpRoutes() {
		$this->routeToPetController();
		$this->routeToStoreController();
		$this->routeToUserController();
	}

	/**
	 * Route to /v2/pet api group
	 */
	private function routeToPetController() {
		$bootstrap = $this;
		$this->getApp()->group('/v2/pet', function () use ($bootstrap) {
			$bootstrap->addRoute('post', '', '\Swagger\PetShop\Controllers\PetController:addPet');
			$bootstrap->addRoute('put', '', '\Swagger\PetShop\Controllers\PetController:updatePet');
			$bootstrap->addRoute('get', '/findByStatus', '\Swagger\PetShop\Controllers\PetController:findPetsByStatus');
			$bootstrap->addRoute('get', '/findByTags', '\Swagger\PetShop\Controllers\PetController:findPetsByTags');
			$bootstrap->addRoute('get', '/{petId}', '\Swagger\PetShop\Controllers\PetController:getPetById');
			$bootstrap->addRoute('post', '/{petId}', '\Swagger\PetShop\Controllers\PetController:updatePetWithForm');
			$bootstrap->addRoute('delete', '/{petId}', '\Swagger\PetShop\Controllers\PetController:deletePet');
			$bootstrap->addRoute('post', '/{petId}/uploadImage', '\Swagger\PetShop\Controllers\PetController:uploadFile');
		});
	}

	/**
	 * Route to /v2/store api group
	 */
	private function routeToStoreController() {
		$bootstrap = $this;
		$this->getApp()->group('/v2/store', function () use ($bootstrap) {
			$bootstrap->addRoute('get', '/inventory', '\Swagger\PetShop\Controllers\StoreController:getInventory');
			$bootstrap->addRoute('post', '/order', '\Swagger\PetShop\Controllers\StoreController:placeOrder');
			$bootstrap->addRoute('get', '/order/{orderId}', '\Swagger\PetShop\Controllers\StoreController:getOrderById');
			$bootstrap->addRoute('delete', '/order/{orderId}', '\Swagger\PetShop\Controllers\StoreController:deleteOrder');
		});
	}

	/**
	 * Route to /v2/user api group
	 */
	private function routeToUserController() {
		$bootstrap = $this;
		$this->getApp()->group('/v2/user', function () use ($bootstrap) {
			$bootstrap->addRoute('post', '', '\Swagger\PetShop\Controllers\UserController:createUser');
			$bootstrap->addRoute('post', '/createWithArray', '\Swagger\PetShop\Controllers\UserController:createUsersWithArrayInput');
			$bootstrap->addRoute('post', '/createWithList', '\Swagger\PetShop\Controllers\UserController:createUsersWithListInput');
			$bootstrap->addRoute('get', '/login', '\Swagger\PetShop\Controllers\UserController:loginUser');
			$bootstrap->addRoute('get', '/logout', '\Swagger\PetShop\Controllers\UserController:logoutUser');
			$bootstrap->addRoute('get', '/{username}', '\Swagger\PetShop\Controllers\UserController:getUserByName');
			$bootstrap->addRoute('put', '/{username}', '\Swagger\PetShop\Controllers\UserController:updateUser');
			$bootstrap->addRoute('delete', '/{username}', '\Swagger\PetShop\Controllers\UserController:deleteUser');
		});
	}
}