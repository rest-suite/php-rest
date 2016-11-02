<?php

namespace Swagger\PetShop\ApiModels;

/**
 * Class Order
 * 
 * @package Swagger\PetShop\ApiModels
 */
class Order {

	/**
	 * @var bool
	 */
	private $complete;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var int
	 */
	private $petId;

	/**
	 * @var int
	 */
	private $quantity;

	/**
	 * @var string
	 */
	private $shipDate;

	/**
	 * Order Status
	 * 
	 * @var string
	 */
	private $status;

	/**
	 * Order constructor
	 * 
	 * @param array $data Initial object data
	 */
	public function __construct(array $data) {
		if(isset($data['id'])) $this->id = $data['id'];
		if(isset($data['petId'])) $this->petId = $data['petId'];
		if(isset($data['quantity'])) $this->quantity = $data['quantity'];
		if(isset($data['shipDate'])) $this->shipDate = $data['shipDate'];
		if(isset($data['status'])) $this->status = $data['status'];
		if(isset($data['complete'])) $this->complete = $data['complete'];
	}

	/**
	 * Get complete property value
	 * 
	 * @return bool
	 */
	public function getComplete() {
		return $this->complete;
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
	 * Get petId property value
	 * 
	 * @return int
	 */
	public function getPetId() {
		return $this->petId;
	}

	/**
	 * Get quantity property value
	 * 
	 * @return int
	 */
	public function getQuantity() {
		return $this->quantity;
	}

	/**
	 * Get shipDate property value
	 * 
	 * @return string
	 */
	public function getShipDate() {
		return $this->shipDate;
	}

	/**
	 * Get status property value
	 * 
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Set complete property new value
	 * 
	 * @param bool $complete
	 */
	public function setComplete($complete) {
		$this->complete = $complete;
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
	 * Set petId property new value
	 * 
	 * @param int $petId
	 */
	public function setPetId($petId) {
		$this->petId = $petId;
	}

	/**
	 * Set quantity property new value
	 * 
	 * @param int $quantity
	 */
	public function setQuantity($quantity) {
		$this->quantity = $quantity;
	}

	/**
	 * Set shipDate property new value
	 * 
	 * @param string $shipDate
	 */
	public function setShipDate($shipDate) {
		$this->shipDate = $shipDate;
	}

	/**
	 * Set status property new value
	 * 
	 * @param string $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}
}