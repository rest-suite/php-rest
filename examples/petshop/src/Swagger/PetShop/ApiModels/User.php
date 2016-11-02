<?php

namespace Swagger\PetShop\ApiModels;

/**
 * Class User
 * 
 * @package Swagger\PetShop\ApiModels
 */
class User {

	/**
	 * @var string
	 */
	private $email;

	/**
	 * @var string
	 */
	private $firstName;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $lastName;

	/**
	 * @var string
	 */
	private $password;

	/**
	 * @var string
	 */
	private $phone;

	/**
	 * @var string
	 */
	private $username;

	/**
	 * User Status
	 * 
	 * @var int
	 */
	private $userStatus;

	/**
	 * User constructor
	 * 
	 * @param array $data Initial object data
	 */
	public function __construct(array $data) {
		if(isset($data['id'])) $this->id = $data['id'];
		if(isset($data['username'])) $this->username = $data['username'];
		if(isset($data['firstName'])) $this->firstName = $data['firstName'];
		if(isset($data['lastName'])) $this->lastName = $data['lastName'];
		if(isset($data['email'])) $this->email = $data['email'];
		if(isset($data['password'])) $this->password = $data['password'];
		if(isset($data['phone'])) $this->phone = $data['phone'];
		if(isset($data['userStatus'])) $this->userStatus = $data['userStatus'];
	}

	/**
	 * Get email property value
	 * 
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Get firstName property value
	 * 
	 * @return string
	 */
	public function getFirstName() {
		return $this->firstName;
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
	 * Get lastName property value
	 * 
	 * @return string
	 */
	public function getLastName() {
		return $this->lastName;
	}

	/**
	 * Get password property value
	 * 
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * Get phone property value
	 * 
	 * @return string
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 * Get username property value
	 * 
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * Get userStatus property value
	 * 
	 * @return int
	 */
	public function getUserStatus() {
		return $this->userStatus;
	}

	/**
	 * Set email property new value
	 * 
	 * @param string $email
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * Set firstName property new value
	 * 
	 * @param string $firstName
	 */
	public function setFirstName($firstName) {
		$this->firstName = $firstName;
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
	 * Set lastName property new value
	 * 
	 * @param string $lastName
	 */
	public function setLastName($lastName) {
		$this->lastName = $lastName;
	}

	/**
	 * Set password property new value
	 * 
	 * @param string $password
	 */
	public function setPassword($password) {
		$this->password = $password;
	}

	/**
	 * Set phone property new value
	 * 
	 * @param string $phone
	 */
	public function setPhone($phone) {
		$this->phone = $phone;
	}

	/**
	 * Set username property new value
	 * 
	 * @param string $username
	 */
	public function setUsername($username) {
		$this->username = $username;
	}

	/**
	 * Set userStatus property new value
	 * 
	 * @param int $userStatus
	 */
	public function setUserStatus($userStatus) {
		$this->userStatus = $userStatus;
	}
}