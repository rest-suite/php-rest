<?php

namespace Swagger\PetShop\ApiModels;

/**
 * Class Category
 * 
 * @package Swagger\PetShop\ApiModels
 */
class Category {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * Category constructor
	 * 
	 * @param array $data Initial object data
	 */
	public function __construct(array $data) {
		if(isset($data['id'])) $this->id = $data['id'];
		if(isset($data['name'])) $this->name = $data['name'];
	}

	/**
	 * Get id property value
	 * 
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Get name property value
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set id property new value
	 * 
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * Set name property new value
	 * 
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}
}