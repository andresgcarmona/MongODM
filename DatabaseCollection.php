<?php

namespace App\Models\Mongo;

use ArrayAccess;
use MongoCollection;

class DatabaseCollection {
	protected $collection;

	public function __construct(MongoCollection $collection) {
		$this->collection = $collection;
	}

	public function drop() {
		$this->collection->drop();
	}
}