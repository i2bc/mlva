<?php
class Videos_model extends CI_Model
{
	protected $table = 'videos';
	protected $cat_table = 'cat_videos';

	public function __construct()
	{
	  parent::__construct();
	}

	public function getAll($nb = 10, $start = 0, $order_by = 'id', $where = array(), $pub = 0)
	{
		return $this->db->select('videos.id AS id, cat_videos.nom AS category, titre, description, image, auteur, date, vues, nb_com, bien, bof, bad')
									->where('attente', $pub)->where($where)
									->join('cat_videos', 'cat_videos.id = videos.cat_id')
									->order_by($order_by, 'desc')
									->get($this->table, $nb, $start)
									->result_array();
	}

	public function get($id = 0)
	{
		$query = $this->db->select('videos.id AS id, cat_videos.nom AS category, titre, description, lien, image, auteur, date, vues, nb_com, bien, bof, bad, tags');
		return $query->where('videos.id', $id)
                  ->join('cat_videos', 'cat_videos.id = videos.cat_id')
									->join('user_infos', 'user_infos.user_id = videos.auteur_id')
									->limit(1, 0)
									->get($this->table, 1, 0)
									->row_array();
	}

	public function count($where = array())
	{
		return (int) $this->db->where($where)->count_all_results($this->table);
	}

}
