<?php

namespace App\Models\Mongo;

use ArrayObject;
use ArrayAccess;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class Document implements ArrayAccess {
    protected $collection;
    protected $schema;

    /**
     * The dcuments's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['*'];

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = false;

    public $idField = '_id';

    public function __construct(array $attributes = []) {
       if(is_array($attributes)) {
            //Fill the attributes.
            $this->fill($attributes);
        }
    }

    public function fill(array $attributes) {
        foreach($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function setAttribute($key, $value) {
        if($this->hasSetMutator($key)) {
            $method = 'set' . Str::studly($key) . 'Attribute';

            return $this->{$method}($value);
        }

        $this->attributes[$key] = $value;
    }

    public function getAttribute($key) {
        return $this->attributes[$key];
    }

    public function __get($attribute) {
        if($this->hasGetMutator($attribute)) {
            $method = 'get' . Str::studly($attribute) . 'Attribute';
            return $this->{$method}();
        }
        else {
            if(isset($this->attributes[$attribute])) return $this->attributes[$attribute];
            else {
                if(method_exists($this, $attribute)) return $this->{$attribute}();
            }
        }
    }

    public function __set($attribute, $value) {
        $this->attributes[$attribute] = $value;
    }

    public function __isset($attribute) {
        return isset($this->attributes[$attribute]);
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasSetMutator($key) {
        return method_exists($this, 'set' . Str::studly($key) . 'Attribute');
    }

    public function hasGetMutator($key) {
        return method_exists($this, 'get' . Str::studly($key) . 'Attribute');
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->$offset);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->$offset;
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        $this->$offset = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset) {
        unset($this->$offset);
    }

    public function getCustomIdField() {
        return $this->idField;
    }

    public static function all(array $fields = []) {
        return static::query()->all($fields);
    }

    public static function lists($value, $key = null) {
        return static::all()->lists($value, $key);
    }

    public static function find($id, array $fields = []) {
        return new static(static::query()->find($id, $fields));
    }

    public static function where($field, $value = null, $boolean = 'and') {
        return (static::query()->where($field, $value, $boolean));
    }

    public static function select(array $fields = []) {
        return (static::query()->select($fields));
    }

    public static function query() {
        return (new static)->newQuery();
    }

    public static function getQuery() {
        return static::query();
    }

    public function newQuery() {
        $builder = $this->newQueryBuilder();
        return $builder;
    }

    public function newQueryBuilder() {
        return QueryBuilder::create($this);
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function toArray() {
        return $this->getAttributes();
    }

    public function toJson() {
        return json_encode($this->toArray());
    }

    public function getCollection() {
        return $this->collection;
    }

    public function save() {
        QueryBuilder::create($this)->save();
        return TRUE;
    }

    public static function create(array $data) {
        if(empty($data)) {
            //TODO: Throw exception.
        }

        return new static(static::query()->insert($data));
    }


    public function update($query = null, $update = null, array $options = []) {
        if(empty($query)) $query = ['_id' => $this->_id];
        if(empty($update)) $update = $this->toArray();

        return static::query()->update($query, $update, $options);
    }

    public function modify($update = null, array $options = []) {
        $query = ['_id' => $this->_id];

        return static::query()->findAndModify($query, $update, $options);
    }

    public static function upsert($query = null, $update = null, array $options = []) {
        if(!isset($options['upsert'])) $options['upsert'] = true;

        //TODO:: Add $query and $update params checks.

        return static::query()->update($query, $update, $options);
    }

    public function delete($options = array()) {
        return QueryBuilder::create($this)->delete($this->toArray(), $options);
    }

    public function pluck(array $fields = []) {
        $data = $this->toArray();
        $result = [];

        foreach($fields as $field) {
            if(array_key_exists($field, $data)) {
                $result[$field] = $data[$field];
            }
        }

        return $result;
    }
}