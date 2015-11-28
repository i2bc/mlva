<?php
class Search_model extends CI_Model
{
  protected $what = array(
    'users' => array(
      'table' => 'users',
      'select' => 'id, username, email, created_at, last_login',
      'where' => array(),
      'join' => '',
      'like' => 'username',
      'orlike' => ['email']
      ),
  );

	public function __construct()
	{
	  parent::__construct();
	}
  /**
   * Helper function to build the SQL Query
   */
  private function getArrayLike($type='users', $query='')
  {
    $array_like = [];
    foreach ($this->what[$type]['orlike'] as $value)
    {
      $array_like[$value] = $query;
    }
    return $array_like;
  }
  /**
   * Very simple search method for ajax request
   */
  public function basicSearch($what, $type = 'users', $query='', $nb = 5, $start = 0)
  {
    return $this->db->select($what)
                    ->like($this->what[$type]['like'], $query)
                    ->get($this->what[$type]['table'], $nb, $start)
                    ->result_array();
  }

  public function countAll($type = 'users', $query='', $nb = 10, $start = 0)
  {
    return $this->db->select('id')
                    ->where($this->what[$type]['where'])
                    ->like($this->what[$type]['like'], $query)
                    ->or_like($this->getArrayLike($type, $query))
                    ->get($this->what[$type]['table'])
                    ->num_rows();
  }
  /**
   * Generic method to search for a ressource with a LIKE statement
   */
  public function searchAll($type = 'users', $query='', $nb = 10, $start = 0)
  {
    $db = $this->db->select($this->what[$type]['select'])->where($this->what[$type]['where']);
    if (!empty($this->what[$type]['join']))
    {
      $db = $db->join($this->what[$type]['join'][0], $this->what[$type]['join'][1]);
    }
    return $db->like($this->what[$type]['like'], $query)
              ->or_like($this->getArrayLike($type, $query))
              ->get($this->what[$type]['table'], $nb, $start)
              ->result_array();
  }

}
