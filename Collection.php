<?php namespace App\Models\Mongo;

use ArrayAccess;
use MongoCursor;
use Iterator;
use IteratorAggregate;

class Collection implements Iterator, ArrayAccess {
    private $cursor;
    private $document;
    private $count;
    private $position;

    protected $items = [];

    public function __construct(MongoCursor $cursor, Document $document) {
        $this->cursor = $cursor;
        $this->document = $document;
        $this->count = null;
        $this->position = 0;

        $this->fill();
    }

    public function fill(){
        if(!empty($this->cursor)) {
            foreach($this->cursor as $c) {
                $this->items[] = new $this->document($c);
            }
        }
    }

    public function getIterator() {
        return $this->items;
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->items[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->items[$offset];
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        $this->items[$offset] = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset) {
        unset($this->items[$offset]);
    }

    public function gerCursor(){
        return $this->cursor;
    }

    public function toArray(){
        return array_map([$this, 'itemsToArray'], $this->items);
    }

    public function itemsToArray($item) {
        return $item->toArray();
    }

    public function toJson() {
        return json_encode(array_map([$this, 'itemsToArray'], $this->items));
    }

    public function itemsToJson($item) {
        return $item->toJson();
    }

    public function count() {
        if(is_null($this->count))
            $count = count($this->items);

        return $count;
    }

    public function lists($value, $key = null) {
        return array_pluck($this->toArray(), $value, $key);
    }

    public function first() {
        if(count($this->items))
            return $this->items[0];

        return null;
    }

    public function last() {
        if(count($this->items))
            return $this->items[$this->count() - 1];

        return null;
    }

    public function current() {
        return $this->items[$this->position];
    }

    public function next() {
        ++$this->position;
    }

    public function key() {
        return $this->position;
    }

    public function valid() {
        return array_key_exists($this->position, $this->items);
    }

    public function rewind() {
        $this->position = 0;
    }
}
