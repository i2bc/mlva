<?php
class  Users_model extends CI_Model
{
	protected $table = 'users';
	protected $table_groups = 'groups';
	protected $user_has_group = 'user_has_group';
	const USER_GROUP_ID = 4;

	public function __construct()
	{
	  parent::__construct();
	}
	/**
	 * Combine the groups of one user in the users array in order to have one entrie for one user
	 */
	public function combineGroups($users)
	{
		$combinedUsers = [];

		foreach ($users as $user)
		{
			if (empty($combinedUsers[$user_id = $user['userId']]))
			{
				$combinedUsers[$user_id] = $user;
				unset($combinedUsers[$user_id]['group']);
				unset($combinedUsers[$user_id]['group_id']);
			}
			$combinedUsers[$user_id]['groups'][] = array('group_id' => $user['group_id'], 'name' => $user['group']);
		}
		return $combinedUsers;
	}
	/**
	 * Transform the sql concat output of groups to array in the $users array
	 */
	public function groupConcatToArray($users)
	{
		$combinedUsers = [];
		foreach ($users as $user)
		{
			//Copy the user data to the new array
			$combinedUsers[$id = $user['userId']] = $user;
			// [[groups => 'aa-bb-cc'], [groups_id => '2-4-7']] outputs [groups => [2 =>'aa', 4=>'bb', 7=>'cc']]
			$combinedUsers[$id]['groups'] = array_combine(explode("-", $user['groups_id']), explode("-", $user['groups']));
		}
		unset($users);
		return $combinedUsers;
	}

	public function getAll($nb = 10, $start = 0, $order_by = 'userId', $where = array(), $order = 'desc')
	{
		$select = 'users.id As userId, username, first_name, last_name, email, last_login, created_at';
		$group_concat = "GROUP_CONCAT(DISTINCT groups.name	ORDER BY groups.id ASC SEPARATOR '-') AS groups,	GROUP_CONCAT(DISTINCT groups.id ORDER BY groups.id ASC SEPARATOR '-') AS groups_id";
		return $this->db->select($select.','.$group_concat)
									->where($where)
									->join($this->user_has_group, 'user_has_group.user_id = users.id')
									->join($this->table_groups, 'groups.id = user_has_group.group_id')
									->group_by('username')
									->order_by($order_by, $order)
									->get($this->table, $nb, $start)
									->result_array();
	}

	public function get($id = 0)
	{
		return $this->db->where('id', $id)
									->get($this->table, 1, 0)
									->row_array();
	}

	public function authenticate(array $credentials)
	{
		extract($credentials);
		$result = $this->db->select('id, password')->where('username', $username)->get($this->table, 1, 0)->row_array();

		return password_verify($password, $result['password']) ? $result['id'] : 0;
	}

	public function getGroups($user_id = 0, $order_by = 'id')
	{
		return $this->db->select('id, name, permissions')
									->where('user_id', $user_id)
									->join($this->user_has_group, 'groups.id = group_id')
									->order_by($order_by, 'desc')
									->get($this->table_groups)
									->result_array();
	}

	public function getAllGroups($order_by = 'id', $where = array(), $order = 'asc')
	{
		return $this->db->where($where)
									->order_by($order_by, $order)
									->get($this->table_groups)
									->result_array();
	}

	public function update(array $newValues, $where = array())
	{
		$this->db->set($newValues)->where($where)->update($this->table);
	}

	public function updateGroups(array $groups, $user_id)
	{
		$this->removeFromAllGroups($user_id);
		foreach ($groups as $group => $group_id)
		{
			$this->addToGroup($user_id, $group_id);
		}
	}

	public function create(array $newUser)
	{
		$newUser['password'] = simpleHash($newUser['password']);
		$newUser = array_merge($newUser, ['created_at' => Carbon\Carbon::now(), 'last_login' => Carbon\Carbon::now()]);
		$this->db->insert($this->table, $newUser);
		$this->addToGroup($id = $this->db->insert_id(), self::USER_GROUP_ID);
		return $id;
	}
	public function addToGroup($user_id, $group_id = self::USER_GROUP_ID)
	{
		$this->db->insert($this->user_has_group, array('group_id' => $group_id, 'user_id' => $user_id));
	}

	public function removeFromAllGroups($user_id)
	{
		$this->db->where('user_id', $user_id)->delete($this->user_has_group);
	}
	public function count($where = array())
	{
		return (int) $this->db->where($where)->count_all_results($this->table);
	}

}