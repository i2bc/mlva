<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Databases_model extends CI_Model {

	protected $table = 'databases';
	protected $table_users = 'users';
	protected $table_strains = 'strains';

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

	// = GET PUBLIC =====
	function getPublic() {
		return $this->db->select('databases.id AS id, databases.created_at, COUNT(strains.id) AS strains_nb, databases.name, username AS creator_name')
				->from($this->table)
				->join($this->table_users, 'users.id = user_id')
				->join($this->table_strains, 'databases.id = database_id')
				->group_by('databases.id')
				->where('state', self::PUBLIC_STATE)
				->get()
				->result_array();
	}

	// = GET USER =====
	function getUser($id) {
		return $this->db->select('*')
				->from($this->table)
				->where('user_id', $id)
				->get()
				->result_array();
	}

	// = GET GROUP =====
	function getGroup($id) {
		return $this->db->select('databases.id AS id, databases.created_at, COUNT(strains.id) AS strains_nb, databases.name, username AS creator_name')
				->from($this->table)
				->join($this->table_users, 'users.id = user_id')
				->join($this->table_strains, 'databases.id = database_id')
				->group_by('databases.id')
				->where('group_id', $id)
				->get()
				->result_array();
	}

	// = GET ALL =====
	function getAll() {
		return $this->db->select('*')
				->from($this->table)
				->get()
				->result_array();
	}

	// = ADD =====
	function add($data) {
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	// = CREATE =====
	function create($data) {
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	// = DELETE =====
	function delete($id) {
		$this->db->where('id', $id)
			->delete($this->table);
	}

	// = UPDATE =====
	public function update(array $newValues, $where = array())
	{
		$this->db->set($newValues)->where($where)->update($this->table);
	}

}
