<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Strains_model extends CI_Model {

	protected $table = 'strains';
	
	const PUBLIC_STATE = 1;

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }
	
	// = GET =====
	//	 <- $base_id (Int), $name (String)
	//	-> Array ( all ), get the strain $name of the database $base_id
	function get($base_id, $name) {
		return $this->db->select('*')
				->from($this->table)
				->where('database_id', $base_id)
				->where('name', $name)
				->get()
				->row_array();
	}
	
	// = GET BASE =====
	//	 <- $base_id (Int)
	//	-> List of Arrays ( all ), get all the strains from database $base_id
	function getBase($base_id) {
		return $this->db->select('*')
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
	function update($id, $data) {
		$this->db->update( $this->table, $data, "id = ".strval($id) );
	}
	
	// = DELETE DATABASE =====
	//	 <- $base_id (Int)
	//	 -> delete all the strains of the database $base_id
	function deleteDatabase($base_id) {
		$this->db->where('database_id', $base_id)
			->delete($this->table);
	}

}