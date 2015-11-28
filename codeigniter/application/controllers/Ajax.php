<?php
class Ajax extends CI_Controller {

  const NB_USERS_JSON = 5;

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
}
