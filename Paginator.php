<?php namespace App\Models\Mongo;

class Paginator{
	private $query;
	private $request;
	private $page;
	private $limit;
	private $sortField;
	private $sortOrder;
	private $all;

	public function __construct(QueryBuilder $query, $request){
		$this->query = $query;
		$this->request = $request;
		$this->page = !empty($request['p']) ? $request['p'] : 1;
		$this->limit = !empty($request['l']) ? $request['l'] : 10;
		$this->all = !empty($request['a']) ? TRUE : FALSE;
		$this->sortField = !empty($request['s']) ? $request['s'] : '_id';
		$this->sortOrder = !empty($request['so']) ? $request['so'] : 'DESC';
	}

	public function get(){
		return $this->query->sort($this->sortField, $this->sortOrder)
						   ->limit($this->limit)
					  	   ->skip(($this->page - 1) * $this->limit)
						   ->get();
	}
}
