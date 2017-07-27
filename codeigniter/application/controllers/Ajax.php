<?php
class Ajax extends CI_Controller {

  const NB_USERS_JSON = 5;

	public function __construct () {
    parent::__construct();
    // if (!$this->input->is_ajax_request()) show_403();
		$this->load->model('databases_model', 'database');
		$this->load->model('strains_model', 'strain');
		$this->load->model('panels_model', 'panel');
	}

  private function writeJson ($data) {
    $this->output
      ->set_content_type('application/json')
      ->set_output(json_encode($data));
  }

  public function getStrains ($base_id) {
    $this->writeJson($this->strain->getBaseUnjsoned($base_id));
  }

  public function test () {
    $this->writeJson([ 'foo' => 'bar' ]);
  }

  public function user () {
    if (isLogged()) {
      $this->writeJson([ 'id' => $_SESSION['user']['id'], 'groups' => $_SESSION['groups'] ]);
    } else {
      $this->writeJson([ 'id' => -1, 'groups' => [] ]);
    }
  }

  public function authLevel ($id) {
    $this->writeJson([ 'level' => authLevel($this->database->get($id)) ]);
  }

  public function createDatabase () {
    $this->load->library('form_validation');
		if ($this->form_validation->run("csv-create2")) {
			// Group ~
			if ($this->input->post('group') == -2) {
				$group_id = $this->createGroupWithDatabase($this->input->post('group_name'), $this->input->post('basename'));
			} else {
				//Make it personal db if the user has entered an invalid group_id
				$group_id = inGroup($this->input->post('group'), true) ? $this->input->post('group') : -1;
			}
      $is_public = $this->input->post('public') == 'true';
			// Base ~
			$base_id = $this->database->create([
				'name' => $this->input->post('basename'),
				'user_id' => $_SESSION['user']['id'],
				'group_id' => $group_id,
				'marker_num' => count($this->input->post('mlvadata')),
				'metadata' => json_encode($this->input->post('metadata')),
				'data' => json_encode($this->input->post('mlvadata')),
				'state' => ($is_public ? 1 : 0)
			]);
			// Send an email to admin
			if ($is_public) {
        $this->load->library('emailer');
				$database = $this->database->getShort([ 'databases.id' => $base_id ])[0];
        $this->emailer->notifyAdminDatabasePublic($database);
			}
			setFlash("success", "The database has been successfully created");
      $this->writeJson([ 'id' => $base_id, 'test' => $is_public ]);
    } else {
      $this->writeJson([ 'id' => -1 ]);
    }
  }

  public function addStrains ($base_id) {
    if (authLevel($this->database->get($base_id)) < 2) return show_403();
    $strains = $this->input->post('strains');
    foreach ($strains as $strain) {
      $this->strain->add([
        'name' => $strain['name'],
        'database_id' => $base_id,
        'metadata' => json_encode($strain['metadata']),
        'data' => json_encode($strain['data'])
      ]);
    }
  }



  // OLD STUFF

  private function checkAndSearch($what, $type, $query, $nb)
  {
      $this->load->model('search_model', 'search');

      if(isLogged() || ($this->input->get('token') == $this->session->key))
      {
        $this->writeJson($this->search->basicSearch($what, $type, $query, $nb));
      }
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
}
