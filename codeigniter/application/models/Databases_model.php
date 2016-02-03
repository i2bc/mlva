<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Databases_model extends CI_Model {

	protected $table = 'databases';
	protected $table_users = 'users';
	protected $table_panels = 'panels';
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

	// = GET SHORT =====
	function getShort($where, $value) {
		return $this->db->select('databases.id AS id, databases.created_at, databases.name,
								COUNT(distinct strains.id) AS strains_nb,
								COUNT(distinct panels.id) AS panels_nb, 
								username AS creator_name')
				->from($this->table)
				->join($this->table_users, 'users.id = user_id', 'left')
				->join($this->table_panels, 'databases.id = panels.database_id', 'left')
				->join($this->table_strains, 'databases.id = strains.database_id', 'left')
				->group_by('databases.id')
				->where($where, $value)
				->get()
				->result_array();
	}

	// = GET PUBLIC =====
	function getPublic() {
		return $this->getShort('databases.state', self::PUBLIC_STATE);
	}

	// = GET USER =====
	function getUser($id) {
		return $this->getShort('user_id', $id);
	}

	// = GET GROUP =====
	function getGroup($id) {
		return $this->getShort('group_id', $id);
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
