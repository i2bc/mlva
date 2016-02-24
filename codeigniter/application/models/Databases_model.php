<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Databases_model extends CI_Model {

	protected $table = 'databases';
	protected $table_users = 'users';
	protected $table_panels = 'panels';
	protected $table_strains = 'strains';

	const PUBLIC_STATE = 1; // Databases.state value for Public databases

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

	// = GET SHORT =====
	//	-> List of Arrays (id, created_at, name, strains_nb, strains_amount, panels_amount, creator_name)
	function getShort($where) {
		return $this->db->select('databases.id AS id, databases.created_at, databases.name,
								COUNT(distinct strains.id) AS strains_amount,
								COUNT(distinct panels.id) AS panels_amount, 
								username AS creator_name')
				->from($this->table)
				->join($this->table_users, 'users.id = user_id', 'left')
				->join($this->table_panels, 'databases.id = panels.database_id', 'left')
				->join($this->table_strains, 'databases.id = strains.database_id', 'left')
				->group_by('databases.id')
				->where($where)
				->get()
				->result_array();
	}

	// = GET PUBLIC =====
	//	 -> get short of all public databases
	function getPublic() {
		return $this->getShort(['databases.state' => self::PUBLIC_STATE]);
	}

	// = GET USER =====
	//	 <- $id (Int)
	//	 -> get short of all databases created_by user $id
	function getUser($id) {
		return $this->getShort(['user_id' => $id]);
	}

	// = GET USER ONLY =====
	//	 <- $id (Int)
	//	 -> get short of all personal databases created_by user $id
	function getUserOnly($id) {
		return $this->getShort(['user_id' => $id, 'group_id' => -1]);
	}

	// = GET GROUP =====
	//	 <- $id (Int)
	//	 -> get short of all databases owned by group $id
	function getGroup($id) {
		return $this->getShort(['group_id' => $id]);
	}

	// = GET ALL =====
	//	 -> get short of all databases
	function getAll() {
		return $this->db->select('*')
				->from($this->table)
				->get()
				->result_array();
	}

	// = ADD & CREATE =====
	//	 <- $data (Array)
	//	 -> $id of the database created with $data
	function add($data) {
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}
	function create($data) { return $this->add($data); }

	// = UPDATE =====
	//	 <- $id (Int), $data (Array)
	//	 -> update the database $id with $data
	public function update($id, $data) {
		$this->db->set($data)
			->where('id', $id)
			->update($this->table);
	}

	// = DELETE =====
	//	 <- $id (Int)
	//	 -> delete database $id
	function delete($id) {
		$this->db->where('id', $id)
			->delete($this->table);
	}

}
