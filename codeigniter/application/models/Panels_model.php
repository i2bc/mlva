<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Panels_model extends CI_Model {

	protected $table = 'panels';
	
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
	function getBase($id) {
		return $this->db->select('*')
				->from($this->table)
				->where('database_id', $id)
				->get()
				->result_array();
	}
	
	// = ADD =====
	function add($data) {
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}
	
	// = DELETE DATABASE =====
	function deleteDatabase($id) {
		$this->db->where('database_id', $id)
			->delete($this->table);
	}
	
	// = UPDATE =====
	function update($id, $data) {
		$this->db->where('id', $id)
			->update($this->table, $data); 
	}
	
	// = DELETE =====
	function delete($id) {
		$this->db->where('id', $id)
			->delete($this->table); 
	}

}