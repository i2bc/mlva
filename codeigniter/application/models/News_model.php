<?php
class News_model extends CI_Model
{
	protected $table = 'news';
	protected $cat_table = 'cat_news';

	public function __construct()
	{
	  parent::__construct();
	}

	public function getAll($nb = 10, $start = 0, $order_by = 'id', $where = array(), $pub = 1)
	{
		return $this->db->select('news.id AS id, cat_news.nom AS category, titre, intro, image, auteur, date, vues, nb_com')
									->where('published', $pub)->where($where)
									->join('cat_news', 'cat_news.id = news.cat_id')
									->order_by($order_by, 'desc')
									->get($this->table, $nb, $start)
									->result_array();
	}

	public function get($id = 0)
	{
		$query = $this->db->select('news.id AS id, cat_news.nom AS category, titre, intro, lien_video, contenu, image, auteur, date, vues, nb_com, tags');
		return $query->where('news.id', $id)
									->join('cat_news', 'cat_news.id = news.cat_id')
									->join('user_infos', 'user_infos.user_id = news.user_id')
									->limit(1, 0)
									->get($this->table, 1, 0)
									->row_array();
	}

	public function count($where = array())
	{
		return (int) $this->db->where($where)->count_all_results($this->table);
	}

}
