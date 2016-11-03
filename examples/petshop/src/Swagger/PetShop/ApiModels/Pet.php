<?php

namespace Swagger\PetShop\ApiModels;

/**
 * Class Pet
 * 
 * @package Swagger\PetShop\ApiModels
 */
class Pet {

	/**
	 * @var Category
	 */
	private $category;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @required 
	 * @var string
	 */
	private $name;

	/**
	 * @required 
	 * @var array
	 */
	private $photoUrls;

	/**
	 * pet status in the store
	 * 
	 * @var string
	 */
	private $status;

	/**
	 * @var Tag[]
	 */
	private $tags;

	/**
	 * Pet constructor
	 * 
	 * @param array $data Initial object data
	 */
	public function __construct(array $data) {
		if(!isset($data['name']))
			 throw new \InvalidArgumentException('Property name is required');
		if(!isset($data['photoUrls']))
			 throw new \InvalidArgumentException('Property photoUrls is required');

		if(isset($data['id'])) $this->id = $data['id'];
		if(isset($data['category'])) $this->category = $data['category'];
		if(isset($data['tags'])) $this->tags = $data['tags'];
		if(isset($data['status'])) $this->status = $data['status'];

		$this->name = $data['name'];
		$this->photoUrls = $data['photoUrls'];
	}

	/**
	 * Get category property value
	 * 
	 * @return Category
	 */
	public function getCategory() {
		return $this->category;
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
	 * Get photoUrls property value
	 * 
	 * @return array
	 */
	public function getPhotoUrls() {
		return $this->photoUrls;
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
	 * Get tags property value
	 * 
	 * @return Tag[]
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 * Set category property new value
	 * 
	 * @param Category $category
	 */
	public function setCategory(Category $category) {
		$this->category = $category;
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
	 * Set photoUrls property new value
	 * 
	 * @param array $photoUrls
	 */
	public function setPhotoUrls(array $photoUrls) {
		$this->photoUrls = $photoUrls;
	}

	/**
	 * Set status property new value
	 * 
	 * @param string $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * Set tags property new value
	 * 
	 * @param mixed $tags
	 */
	public function setTags($tags) {
		$this->tags = $tags;
	}

	/**
	 * Return object as array
	 * 
	 * @return array
	 */
	public function toArray() {
		return [
			'id' => $this->getId(),
			'category' => $this->getCategory(),
			'name' => $this->getName(),
			'photoUrls' => $this->getPhotoUrls(),
			'tags' => $this->getTags(),
			'status' => $this->getStatus()
		];
	}
}