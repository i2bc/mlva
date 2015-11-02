<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Strains_model extends CI_Model {

	protected $table = 'strains';
	
	const PUBLIC_STATE = 1;

    function __construct() {
        // Call the Model constructor
        parent::__construct();
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

}