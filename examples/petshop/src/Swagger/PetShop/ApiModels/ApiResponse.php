<?php

namespace Swagger\PetShop\ApiModels;

/**
 * Class ApiResponse
 * 
 * @package Swagger\PetShop\ApiModels
 */
class ApiResponse {

	/**
	 * @var int
	 */
	private $code;

	/**
	 * @var string
	 */
	private $message;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * ApiResponse constructor
	 * 
	 * @param array $data Initial object data
	 */
	public function __construct(array $data) {
		if(isset($data['code'])) $this->code = $data['code'];
		if(isset($data['type'])) $this->type = $data['type'];
		if(isset($data['message'])) $this->message = $data['message'];
	}

	/**
	 * Get code property value
	 * 
	 * @return int
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * Get message property value
	 * 
	 * @return string
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * Get type property value
	 * 
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Set code property new value
	 * 
	 * @param int $code
	 */
	public function setCode($code) {
		$this->code = $code;
	}

	/**
	 * Set message property new value
	 * 
	 * @param string $message
	 */
	public function setMessage($message) {
		$this->message = $message;
	}

	/**
	 * Set type property new value
	 * 
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}
}