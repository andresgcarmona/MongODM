<?php

namespace App\Models\Mongo;

use MongoClient;
use App\Models\Mongo\DatabaseCollection;

class Database{
	protected $conn;
	protected $db;

	private static $_instance = null;

	public function __construct() {
		$this->conn = new MongoClient();
		$this->db = $this->conn->{env('DB_MONGO')}; //TODO: Change this
	}

	final private function __clone() {}

	public static function getInstance() {
		if(!self::$_instance instanceof self) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function getConnection() {
		return $this->conn;
	}

	public function getDatabase() {
		return $this->db;
	}

	public static function collection($name) {
		return new DatabaseCollection(Database::getInstance()->db->{$name});
	}
}
