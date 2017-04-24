<?php

namespace Example\Petshop;

use Rest\Lib\AbstractBootstrap;

/**
 * Class Bootstrap
 * 
 * Creating routes and starting application
 * 
 * @package Example\Petshop
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
		  'Example\\Petshop\\Controllers\\PetController' => 
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
		  'Example\\Petshop\\Controllers\\StoreController' => 
		  array (
		    'getInventory' => true,
		    'placeOrder' => true,
		    'getOrderById' => true,
		    'deleteOrder' => true,
		  ),
		  'Example\\Petshop\\Controllers\\UserController' => 
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
		$result['api'] = [];
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
			$bootstrap->addRoute('post', '', '\Example\Petshop\Controllers\PetController:addPet');
			$bootstrap->addRoute('put', '', '\Example\Petshop\Controllers\PetController:updatePet');
			$bootstrap->addRoute('get', '/findByStatus', '\Example\Petshop\Controllers\PetController:findPetsByStatus');
			$bootstrap->addRoute('get', '/findByTags', '\Example\Petshop\Controllers\PetController:findPetsByTags');
			$bootstrap->addRoute('get', '/{petId}', '\Example\Petshop\Controllers\PetController:getPetById');
			$bootstrap->addRoute('post', '/{petId}', '\Example\Petshop\Controllers\PetController:updatePetWithForm');
			$bootstrap->addRoute('delete', '/{petId}', '\Example\Petshop\Controllers\PetController:deletePet');
			$bootstrap->addRoute('post', '/{petId}/uploadImage', '\Example\Petshop\Controllers\PetController:uploadFile');
		});
	}

	/**
	 * Route to /v2/store api group
	 */
	private function routeToStoreController() {
		$bootstrap = $this;
		$this->getApp()->group('/v2/store', function () use ($bootstrap) {
			$bootstrap->addRoute('get', '/inventory', '\Example\Petshop\Controllers\StoreController:getInventory');
			$bootstrap->addRoute('post', '/order', '\Example\Petshop\Controllers\StoreController:placeOrder');
			$bootstrap->addRoute('get', '/order/{orderId}', '\Example\Petshop\Controllers\StoreController:getOrderById');
			$bootstrap->addRoute('delete', '/order/{orderId}', '\Example\Petshop\Controllers\StoreController:deleteOrder');
		});
	}

	/**
	 * Route to /v2/user api group
	 */
	private function routeToUserController() {
		$bootstrap = $this;
		$this->getApp()->group('/v2/user', function () use ($bootstrap) {
			$bootstrap->addRoute('post', '', '\Example\Petshop\Controllers\UserController:createUser');
			$bootstrap->addRoute('post', '/createWithArray', '\Example\Petshop\Controllers\UserController:createUsersWithArrayInput');
			$bootstrap->addRoute('post', '/createWithList', '\Example\Petshop\Controllers\UserController:createUsersWithListInput');
			$bootstrap->addRoute('get', '/login', '\Example\Petshop\Controllers\UserController:loginUser');
			$bootstrap->addRoute('get', '/logout', '\Example\Petshop\Controllers\UserController:logoutUser');
			$bootstrap->addRoute('get', '/{username}', '\Example\Petshop\Controllers\UserController:getUserByName');
			$bootstrap->addRoute('put', '/{username}', '\Example\Petshop\Controllers\UserController:updateUser');
			$bootstrap->addRoute('delete', '/{username}', '\Example\Petshop\Controllers\UserController:deleteUser');
		});
	}
}