<?php

namespace Example\Api\ApiModels;

/**
 * Class Item
 * 
 * simple item
 * 
 * @package Example\Api\ApiModels
 */
class Item {

	/**
	 * @required 
	 * @var string
	 */
	private $content;

	/**
	 * @var bool
	 */
	private $flag;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * Item constructor
	 * 
	 * @param array $data Initial object data
	 */
	public function __construct(array $data) {
		if(!isset($data['content']))
			 throw new \InvalidArgumentException("Property 'content' is required", 400);

		if(isset($data['id'])) $this->id = $data['id'];
		if(isset($data['flag'])) $this->flag = $data['flag'];

		$this->content = $data['content'];
	}

	/**
	 * Get content property value
	 * 
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Get flag property value
	 * 
	 * @return bool
	 */
	public function getFlag() {
		return $this->flag;
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
	 * Set content property new value
	 * 
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * Set flag property new value
	 * 
	 * @param bool $flag
	 */
	public function setFlag($flag) {
		$this->flag = $flag;
	}

	/**
	 * Set id property new value
	 * 
	 * @param int $id
	 */
	public function setId($id) {
		if(!is_null($this->id)) {
			throw new \LogicException('Property "id" is read only');
		}
		$this->id = $id;
	}

	/**
	 * Return object as array
	 * 
	 * @return array
	 */
	public function toArray() {
		return [
			'id' => $this->getId(),
			'content' => $this->getContent(),
			'flag' => $this->getFlag()
		];
	}
}