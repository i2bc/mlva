<?php
class Comments_model extends CI_Model
{
	protected $comments_news_table = 'com_news';
  protected $comments_videos_table = 'com_video';

	public function __construct()
	{
	  parent::__construct();
	}

	public function getAll($where = array(), $model = 'news', $order_by = 'id', $nb = 100, $start = 0)
	{
    $query = $this->db->where($where)->order_by($order_by, 'desc')->get($this->newsOrVideoTable($model), $nb, $start);
    return $query->result_array();
	}

	public function get($id = 0, $model = 'news')
	{
		$query = $this->db->join('user_infos', 'user_infos.user_id = '.$model.'.user_id')->where('id', $id)->get($this->newsOrVideoTable($model), 1, 0);
		return $query->row_array();
	}

	public function count($where = array(), $model = 'news')
	{
		return (int) $this->db->where($where)->count_all_results($this->newsOrVideoTable($model));
	}

  private function newsOrVideoTable($model='news')
  {
    return $model == 'news' ? $this->comments_news_table : $this->comments_videos_table;
  }

}
