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
		$base = $this->db->select('*')
				->from($this->table)
				->where('id', $id)
				->get()
				->row_array();
		if ($base) {
			$base['data'] = json_decode($base['data'], true);
			$base['metadata'] = json_decode($base['metadata'], true);
		}
		return $base;
	}

	// = GET SHORT =====
	//	-> List of Arrays (id, created_at, name, strains_nb, strains_amount, panels_amount, creator_name)
	function getShort($where) {
		return $this->db->select('databases.id AS id, databases.created_at,
								databases.name, databases.website, databases.description,
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

	// = COUNT =====
	//	 <- $where (Array)
	//	 -> number of entries
	public function count($where = array())
	{
		return (int) $this->db->where($where)->count_all_results($this->table);
	}

	// = IS UNIQUE PUBLIC =====
	//	 <- $where (Array)
	//	 -> true if the database has a unique name
	public function isUnique($name)
	{
		$where = ['name' => $name, 'state' => self::PUBLIC_STATE];
		return $this->db->where($where)->count_all_results($this->table) == 0;
	}

	// = GET Informations for listing the databases (admin part)=====
	//	 <- sevral parameters to paginate and select databases
	//	 -> database (array)
	public function getAllDatabases($nb =-1, $start = 0, $order_by = 'id', $where = array(), $order = 'asc')
	{
		$query = $this->db->select('databases.id AS id, databases.created_at,
								databases.name, databases.last_update, username AS creator_name')
							->where($where)
							->join($this->table_users, 'users.id = user_id', 'left')
							->order_by($order_by, $order);
		if ($nb > 0)
		{
			$query = $query->limit($nb, $start);
		}
		return $query->get($this->table)->result_array();
	}

}
