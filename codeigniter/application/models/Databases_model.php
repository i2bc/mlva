<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Billet_model extends CI_Model {

	protected $table = 'databases';

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
				->result();
	}
	
	// = GET ALL =====
	function get_all() {
		return $this->db->select('*')
				->from($this->table)
				->get()
				->result();
	}
	
	// = ADD =====
	function add($data) {
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

}