<?php

namespace Example\Api\ApiModels;

/**
 * Class Error
 * 
 * error message
 * 
 * @package Example\Api\ApiModels
 */
class Error {

	/**
	 * @var int
	 */
	private $code;

	/**
	 * @var string
	 */
	private $exception;

	/**
	 * @required 
	 * @var string
	 */
	private $message;

	/**
	 * Error constructor
	 * 
	 * @param array $data Initial object data
	 */
	public function __construct(array $data) {
		if(!isset($data['message']))
			 throw new \InvalidArgumentException("Property 'message' is required", 400);

		if(isset($data['code'])) $this->code = $data['code'];
		if(isset($data['exception'])) $this->exception = $data['exception'];

		$this->message = $data['message'];
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
	 * Get exception property value
	 * 
	 * @return string
	 */
	public function getException() {
		return $this->exception;
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
	 * Set code property new value
	 * 
	 * @param int $code
	 */
	public function setCode($code) {
		$this->code = $code;
	}

	/**
	 * Set exception property new value
	 * 
	 * @param string $exception
	 */
	public function setException($exception) {
		$this->exception = $exception;
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
	 * Return object as array
	 * 
	 * @return array
	 */
	public function toArray() {
		return [
			'code' => $this->getCode(),
			'message' => $this->getMessage(),
			'exception' => $this->getException()
		];
	}
}