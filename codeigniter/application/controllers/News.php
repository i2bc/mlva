<?php
class News extends CI_Controller {

  const NB_NEWS_PER_PAGE = 4;

	public function __construct()
	{
		parent::__construct();
		$this->load->library('Twig');
    $this->load->model('news_model', 'news');
    $this->load->model('comments_model', 'comments');
	}
  public function index()
  {
    $this->last();
  }

  protected function showArticles($page, $url, $order='id', $page_infos = array(), $where = array())
  {
    $this->load->library('pagination');
    $count = $this->news->count($where);
    $this->pagination->initialize(arrayPagination(base_url() . $url, $count, self::NB_NEWS_PER_PAGE));

    list($page, $start)  = getPageAndStart($page, self::NB_NEWS_PER_PAGE);

    $data = array('session' => $_SESSION,
                  'count' => $count,
                  'news' => $this->news->getAll(self::NB_NEWS_PER_PAGE, $start, $order, $where),
                  'pagination' => $this->pagination->create_links(),
                  'page_infos' => $page_infos
                  );
		$this->twig->render('news/articles', $data);
  }

  public function cat($page = 1)
  {
    $where = array('cat_id' => $cat_id = getIntOrOne($this->input->get('cat_id')));
    $page_infos = array('title' => 'Articles de la catégorie '. $cat_id);

    $this->showArticles($page, '/news/cat/', 'id', $page_infos, $where);
  }

  public function most_viewed($page = 1)
  {
    $this->showArticles($page, '/news/most_viewed/', 'vues');
  }

  public function most_commented($page = 1)
  {
    $this->showArticles($page, '/news/most_commented/', 'nb_com');
  }

	public function last($page = 1)
	{
    $this->showArticles($page, '/news/last/');
	}

	public function voir($id = 0)
	{
		$id = intval($id);

		if ($id > 0)
		{
      if (!($article = $this->news->get($id)))
      {
        show_404();
      }
      $data = array('session' => $_SESSION,
                    'article' => $article,
                    'comments' => $this->comments->getAll(array('news_id' => $id))
                    );
      $this->twig->render('news/article', $data);

		}
		else
		{
			redirect(base_url('news'));
		}
	}
  public function edit($id)
  {
    if (!($id = getIntOrZero($id)))
    {
      show_404();
    }
    $input_array = $this->input->post(array('titre', 'intro', 'contenu', 'image', 'auteur', 'cat', 'tags', 'user_id', 'lien_video'));
  }
}
