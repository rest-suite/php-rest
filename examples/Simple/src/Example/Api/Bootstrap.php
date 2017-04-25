<?php

namespace Example\Api;

use Rest\Lib\AbstractBootstrap;

/**
 * Class Bootstrap
 * 
 * Creating routes and starting application
 * 
 * @package Example\Api
 */
class Bootstrap extends AbstractBootstrap {

	/**
	 * Return generated info from specs
	 * 
	 * @return array
	 */
	public static function getInfo() {
		return array (
		  'title' => 'ExampleApi',
		  'description' => 'Example Api Multi line description
		',
		);
	}

	/**
	 * @return array
	 */
	public function defaultSettings() {
		return array (
		  'Example\\Api\\Controllers\\ItemController' => 
		  array (
		    'addItem' => true,
		    'getItem' => true,
		    'updateItem' => true,
		    'deleteItem' => true,
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
		$this->routeToItemController();
	}

	/**
	 * Route to /item api group
	 */
	private function routeToItemController() {
		$bootstrap = $this;
		$this->getApp()->group('/item', function () use ($bootstrap) {
			$bootstrap->addRoute('post', '', '\Example\Api\Controllers\ItemController:addItem');
			$bootstrap->addRoute('get', '/{id:[0-9]+}', '\Example\Api\Controllers\ItemController:getItem');
			$bootstrap->addRoute('put', '/{id:[0-9]+}', '\Example\Api\Controllers\ItemController:updateItem');
			$bootstrap->addRoute('delete', '/{id:[0-9]+}', '\Example\Api\Controllers\ItemController:deleteItem');
		});
	}
}