<?php

namespace App\Models\Mongo;

use MongoId;

class QueryBuilder {
    private $query;
    private $idField = '_id';

    private $wheres = [];
    private $fields = [];
    private $limit;
    private $sort;
    private $skip;

    protected $db;
    protected $collection;
    protected $document;

    public function __construct(Document $document) {
        $this->document = $document;
        $this->collection = $this->document->getCollection();
        $this->db = Database::getInstance()->getDatabase();

        $this->query = $this->db->{$this->collection};
    }

    public static function create($model) {
        return new self($model);
    }

    public function all(array $fields = []) {
        return $this->get($this->query->find($this->wheres, $fields));
    }

    public function get($query = null) {
        return $this->executeQuery(!is_null($query) ? $query : $this->query->find($this->wheres, $this->fields));
    }

    public function executeQuery($query = null) {
        if(is_null($query)) $query = $this->query;
        
        if(!empty($this->limit)) $query->limit($this->limit);
        if(!empty($this->sort))  $query->sort($this->sort);
        if(!empty($this->skip))  $query->skip($this->skip);

        return new Collection($query, $this->document);
    }

    public function find($id, array $fields = []) {
        return $this->where($this->getIdField(), new MongoId($id))->first($fields);
    }

    public function first(array $fields = []) {
        return $this->query->findOne($this->wheres, $fields);
    }

    public function select(array $fields = []) {
        $this->fields = $fields;
        return $this;
    }

    public function where($field, $value = null, $boolean = 'and') {
        if($field == $this->getIdField()) $value = new MongoId($value);
        $this->wheres[$field] = $value;

        return $this;
    }

    public function orWhere($field, $value) {
        if($field == $this->getIdField()) $value = new MongoId($value);
        $this->wheres[$field] = $value;

        return $this;
    }

    public function getIdField() {
        $id = $this->document->getCustomIdField();
        return !is_null($id) ? $id : $this->idField;
    }

    public function limit($limit) {
        $this->limit = $limit;

        return $this;
    }

    public function sort($field, $order){
        $field = !empty($field) ? $field : '_id';
        $order = !empty($order) ? (strtoupper($order) == 'DESC' ? -1 : 1) : -1;

        $this->sort = [$field => $order];
        return $this;
    }

    public function orderBy($field, $order) {
        return $this->sort($field, $order);
    }

    public function skip($skip){
        $this->skip = !empty($skip) ? $skip : NULL;
        return $this;
    }

    public function insert(array $data) {
        $result = $this->query->insert($data);
        return $data;
    }

    public function update($conditions, $update, array $options = []){
        $result =  $this->query->update($conditions, $update, $options);
        return true;
    }

    public function findAndModify($conditions, $update, $options){
        return $this->query->findAndModify($conditions, $update, NULL, $options);
    }

    public function save() {
        $d = $this->document->toArray();

        if(isset($d['_id'])) $this->update(['_id' => $d['_id']], $d);
        else $this->insert($d);

        return true;
    }

    public function delete($conditions, $options = []) {
        return $this->query->remove($conditions, $options);
    }

    public function count() {
        return $this->get()->count();
    }
}