<?php

namespace Example\Petshop\ApiModels;

/**
 * Class Tag
 * 
 * @package Example\Petshop\ApiModels
 */
class Tag {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * Tag constructor
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

	/**
	 * Return object as array
	 * 
	 * @return array
	 */
	public function toArray() {
		return [
			'id' => $this->getId(),
			'name' => $this->getName()
		];
	}
}