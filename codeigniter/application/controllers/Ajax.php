<?php
class Ajax extends CI_Controller {

  const NB_USERS_JSON = 5;
  const NB_STRAINS_PER_PAGE = 10;

	public function __construct()
	{
		parent::__construct();
    if (!$this->input->is_ajax_request())
    {
      show_403();
    }
    $this->load->library('Twig');
    $this->load->library('form_validation');
	}

  private function checkAndSearch($what, $type, $query, $nb)
  {
      $this->load->model('search_model', 'search');

      if(isLogged() || ($this->input->get('token') == $this->session->key))
      {
        $this->writeJson($this->search->basicSearch($what, $type, $query, $nb));
      }
  }

  private function writeJson($data)
  {
    $this->output->set_content_type('application/json')->set_output(json_encode($data));
  }

  public function username()
  {
    $this->checkAndSearch('id, username AS text', 'users', $this->input->get('username'), self::NB_USERS_JSON);
  }

  public function mask()
  {
    $col = md5($this->input->get('col'));
    if(isset($_SESSION['currentDatabase']['col_masked'][$col]))
    {
      $_SESSION['currentDatabase']['col_masked'][$col] = !$_SESSION['currentDatabase']['col_masked'][$col];
    }
    else
    {
      $_SESSION['currentDatabase']['col_masked'][$col] = 1;
    }
  }
  
	public function getStrains() {
		$this->load->library('pagination');
		$strains = $_SESSION['currentStrains'];
		$page = $_SESSION['currentPage'];
		$perPage = self::NB_STRAINS_PER_PAGE;
		
		if ( getStart($page + 1, $perPage) < count($strains)) {
			$page = $page + 1;
			list($page, $start) = getPageAndStart($page, $perPage);
			$pageContent = array_slice($strains, $start, $perPage);
			$_SESSION['currentPage'] = $page + 1;
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode($pageContent));
		}
	}
}
