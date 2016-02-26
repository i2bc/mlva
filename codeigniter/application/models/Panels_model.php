<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Panels_model extends CI_Model {

	protected $table = 'panels';
	protected $genonum = 'genotype_number';
	
	const PUBLIC_STATE = 1;

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

	// = GET (BY ID) =====
	function get($id) {
		return $this->db->select('*')
				->from($this->table)
				->where('id', $id)
				->get()
				->row_array();
	}
	
	// = GET BASE =====
	//	 <- $base_id (Int)
	//	-> List of Arrays ( all ), get all the panels from database $base_id
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
	
	// = DELETE DATABASE =====
	//	 <- $base_id (Int)
	//	 -> delete all the panels of the database $base_id
	function deleteDatabase($base_id) {
		$this->db->where('database_id', $base_id)
			->delete($this->table);
	}
	
	// = DELETE =====
	//	 <- $id (Int)
	//	 -> delete database $id
	function delete($id) {
		$this->db->where('id', $id)
			->delete($this->table); 
	}
	
	// = UPDATE =====
	//	 <- $id (Int), $data (Array)
	//	 -> update the panel $id with $data
	function update($id, $data) {
		$this->db->where('id', $id)
			->update($this->table, $data); 
	}
	
	// = EXIST =====
	//	 <- $where (Array)
	//	 -> List of ( id ) of panels $where
	function exist($where) {
		return $this->db->select('id')
				->from($this->table)
				->where($where)
				->get()
				->result_array();
	}
	
	// = GET GN =====
	//	 <- $panel_id (Int)
	//	-> List of Arrays ( all ), get all the genotype numbers of panel $base_id
	function getGN($panel_id) {
		return $this->db->select('*')
				->from($this->genonum)
				->where('panel_id', $panel_id)
				->get()
				->result_array();
	}
	
	// = ADD GN =====
	//	 <- $data (Array)
	//	 -> $id of the genotype number created with $data
	function addGN($data) {
		$this->db->insert($this->genonum, $data);
		return $this->db->insert_id();
	}

}