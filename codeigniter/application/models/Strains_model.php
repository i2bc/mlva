<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Strains_model extends CI_Model {

	protected $table = 'strains';
	protected $limit = 100;

	const PUBLIC_STATE = 1;

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

	// = GET =====
	//	 <- $base_id (Int), $name (String)
	//	-> Array ( all ), get the strain $name of the database $base_id
	function get($base_id, $name) {
		$strain = $this->db->select('*')
				->from($this->table)
				->where('database_id', $base_id)
				->where('name', $name)
				->get()
				->row_array();
		if ($strain) {
			$strain['data'] = json_decode($strain['data'], true);
			$strain['metadata'] = json_decode($strain['metadata'], true);
		}
		return $strain;
	}

	// = GET BASE =====
	//	 <- $base_id (Int)
	//	-> List of Arrays ( all ), get all the strains from database $base_id
	function getBase($base_id, $offset) {
		$strains = $this->db->select('*')
				->from($this->table)
				->limit($this->limit, $offset)
				->where('database_id', $base_id)
				->get()
				->result_array();
		foreach ($strains as &$strain) {
			$strain['data'] = json_decode($strain['data'], true);
			$strain['metadata'] = json_decode($strain['metadata'], true);
		}
		return $strains;
	}

	// = GET BASE KEYS =====
	//	 <- $base_id (Int)
	//	-> List of Arrays ( key ), get all the strains from database $base_id
	function getBaseKeys($base_id) {
		return $this->db->select('name')
				->from($this->table)
				->where('database_id', $base_id)
				->get()
				->result_array();
	}

	// = ADD =====
	//	 <- $data (Array)
	//	 -> $id of the strain created with $data
	function add($data) {
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	// = UPDATE =====
	//	 <- $id (Int), $data (Array)
	//	 -> update the strain $id with $data
	function update($base_id, $name, $data) {
		// $this->db->set($newValues)->where($where)->update($table);
		$this->db
				->where('database_id', $base_id)
				->where('name', $name)
				->set($data)
				->update($this->table);
	}

	// = REPLACE =====
	//	 <- $id (Int), $data (Array)
	//	 -> insert or update the strain $id with $data
	function replace($base_id, $name, $data) {
		$strain = $this->get($base_id, $name);
		if ($strain) {
			$this->update($base_id, $name, $data);
			return $strain['id'];
		} else {
			$this->add($data);
			return $this->db->insert_id();
		}
	}

	// = DELETE DATABASE =====
	//	 <- $base_id (Int)
	//	 -> delete all the strains of the database $base_id
	function deleteDatabase($base_id) {
		$this->db->where('database_id', $base_id)
			->delete($this->table);
	}

	// = DELETE =====
	//	 <- $base_id (Int), $name (String)
	//	 -> delete the strain $name of the database $base_id
	function delete($base_id, $name) {
		$this->db->where('database_id', $base_id)
				->where('name', $name)
				->delete($this->table);
	}

}
