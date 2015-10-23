<?php
class Search_model extends CI_Model
{
  protected $table_videos = 'videos';
  protected $table_news = 'news';
  protected $table_users = 'user';
  protected $what = array(
    'news' => array(
      'table' => 'news',
      'select' => 'news.id AS id, cat_news.nom AS category, titre, auteur, intro, date, vues, nb_com',
      'where' => array('published' => 1),
      'join' => array('cat_news', 'cat_news.id = news.cat_id'),
      'like' => 'titre',
      'orlike' => ['auteur', 'intro', 'tags']
    ),
    'videos' => array(
      'table' => 'videos',
      'select' => 'videos.id AS id, cat_videos.nom AS category, titre, auteur, description, tags, image, auteur, date, vues, nb_com, bien, bof, bad',
      'where' => array('attente' => 0),
      'join' => array('cat_videos', 'cat_videos.id = videos.cat_id'),
      'like' => 'titre',
      'orlike' => ['auteur', 'description', 'tags']
    ),
    'users' => array(
      'table' => 'user',
      'select' => 'id, pseudo, email, rang, date_register, last_activity',
      'where' => array(),
      'join' => '',
      'like' => 'pseudo',
      'orlike' => ['email', 'rang']
      ),
  );

	public function __construct()
	{
	  parent::__construct();
	}

  private function getArrayLike($type='videos', $query='')
  {
    $array_like = [];
    foreach ($this->what[$type]['orlike'] as $value)
    {
      $array_like[$value] = $query;
    }
    return $array_like;
  }
  public function countAll($type = 'videos', $query='', $nb = 10, $start = 0)
  {
    return $this->db->select('id')
                    ->where($this->what[$type]['where'])
                    ->like($this->what[$type]['like'], $query)
                    ->or_like($this->getArrayLike($type, $query))
                    ->get($this->what[$type]['table'])
                    ->num_rows();
  }

  public function searchAll($type = 'videos', $query='', $nb = 10, $start = 0)
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
