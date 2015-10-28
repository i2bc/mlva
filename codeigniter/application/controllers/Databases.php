<?php
class Databases extends CI_Controller {

	// = CONSTANTS =====
	const NB_USERS_PER_PAGE = 4;
	const NB_GROUPS_PER_PAGE = 20;

	// = CONSTRUCT =====
	public function __construct() {
		parent::__construct();
		$this->load->library('Twig');
		$this->load->model('databases_model', 'database');
		$this->load->model('strains_model', 'strain');
		$this->load->model('users_model', 'user');
	}
	
	// = REMAP =====
	function _remap( $method, $id ) { 
		if (in_array($method, $this->viewable())) {
			$this->view($method);
		} else {
			$this->viewPublic();
		}
	}

	// = INDEX =====
	public function index() {
	
	}
	
	// = VIEW PUBLIC =====
	public function viewPublic() {
		$data = array('bases' => $this->prepareList($this->database->getPublic()));
		$this->twig->render('databases/public', $data);
	}
	
	// = VIEW PUBLIC =====
	public function view($id) {
		$base = $this->database->get($id);
		$data = array( 
			'base' => $this->jsonExec($base),
			'group' => $this->user->getGroup($base['group_id']),
			'strains' => array_map(function($o){return $this->jsonExec($o);}, $this->strain->getBase($id))
		);
		$this->twig->render('databases/view', $data);
	}
	
	// = PREPARE LIST * =====
	function prepareList($bases) {
		foreach ($bases as &$base) {
			$base['creator_name'] = $this->user->get($base['user_id'])['username'];
			$base['panels_nb'] = 3;
			$base['strains_nb'] = 320;
			$base['disabled_strains_nb'] = 0;
		}
		return $bases;
	}
	
	// = VIEWABLE * =====
	function viewable() {
		$list = $this->database->getPublic();
		foreach($_SESSION['groups'] as &$group) {
			$list = array_merge($list, $this->database->getGroup($group));
		}
		return array_map(function($base){return $base['id'];}, $list);
	}
	
	// = JSON EXEC * =====
	function jsonExec($obj) {
		$obj['datas'] = json_decode($obj['datas'], true);
		$obj['metadatas'] = json_decode($obj['metadatas'], true);
		return $obj;
	}
}
