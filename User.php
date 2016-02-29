<?php namespace App\Models\Mongo;

use App\Models\Mongo\Document;

class User extends Document {
	protected $collection = 'users';
}
