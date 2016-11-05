<?php

namespace Example\Api;

use Slim\App;

/**
 * Class Bootstrap
 * 
 * Creating routes and starting application
 * 
 * @package Example\Api
 */
class Bootstrap {

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
		  'title' => 'ExampleApi',
		  'description' => 'Example Api Multi line description
		',
		);
	}

	/**
	 * Bootstrap constructor
	 * 
	 * @param App $app
	 */
	public function __construct(App $app = null) {
		$this->app = is_null($app) ? new App() : $app;
		$this->routeToItemController();
	}

	/**
	 * Start application
	 */
	public function run() {
		$this->app->run();
	}

	/**
	 * Route to /item api group
	 */
	private function routeToItemController() {
		$settings = $this->app->getContainer()->get('settings');
		$apiConfig = array (
		  'addItem' => true,
		  'getItem' => true,
		  'updateItem' => true,
		  'deleteItem' => true,
		);
		if(isset($settings['api']) && isset($settings['api']['ItemController'])) { 
			$apiConfig = array_merge($apiConfig, $settings['api']['ItemController']);
		}
		$this->app->group('/item', function () use($apiConfig) {
			/** @var App $this */
			if($apiConfig['addItem'])
				$this->post('', '\Example\Api\Controllers\ItemController:addItem');
			if($apiConfig['getItem'])
				$this->get('/{id}', '\Example\Api\Controllers\ItemController:getItem');
			if($apiConfig['updateItem'])
				$this->put('/{id}', '\Example\Api\Controllers\ItemController:updateItem');
			if($apiConfig['deleteItem'])
				$this->delete('/{id}', '\Example\Api\Controllers\ItemController:deleteItem');
		});
	}
}