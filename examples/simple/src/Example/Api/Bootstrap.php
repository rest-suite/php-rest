<?php

namespace Example\Api;

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Bootstrap
 * 
 * Creating routes and starting application
 * 
 * @package Example\Api
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
		  'title' => 'ExampleApi',
		  'description' => 'Example Api Multi line description
		',
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
		        throw new \Exception($response->getReasonPhrase(), $response->getStatusCode());
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
		$this->app->add('Example\Api\Bootstrap::processRequest');
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
				$this->get('/{id:[0-9]+}', '\Example\Api\Controllers\ItemController:getItem');
			if($apiConfig['updateItem'])
				$this->put('/{id:[0-9]+}', '\Example\Api\Controllers\ItemController:updateItem');
			if($apiConfig['deleteItem'])
				$this->delete('/{id:[0-9]+}', '\Example\Api\Controllers\ItemController:deleteItem');
		});
	}
}