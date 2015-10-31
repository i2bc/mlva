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
		if ($_SESSION['isLogged']) {
			switch ($method) {
				case "index":
				case "user":
					$this->viewUser();
				break;
				case "groups":
					$this->viewGroups();
				break;
				case "public":
					$this->viewPublic();
				break;
				case "view":
					if ($id[0] == "") {
						$this->viewUser();
					} else if (in_array($id[0], $this->viewable())) {
						$this->view($id[0]);
					} else {
						show_404();
					}
				break;
				case "create":
					$this->create();
				break;
				case "export":
					if (in_array($id[0], $this->viewable())) {
						$this->export($id[0]);
					} else {
						show_404();
					}
				break;
				default:
					if (in_array($method, $this->viewable())) {
						$this->view($method);
					} else {
						show_404();
					}
				break;
			}
		} else {
			switch ($method) {
				case "index":
				case "public":
					$this->viewPublic();
				break;
				case "view":
					if ($id[0] == "") {
						$this->viewPublic();
					} else if (in_array($id[0], $this->viewable())) {
						$this->view($id[0]);
					} else {
						show_404();
					}
				break;
				case "export":
					if (in_array($id[0], $this->viewable())) {
						$this->export($id[0]);
					} else {
						show_404();
					}
				break;
				default:
					if (in_array($method, $this->viewable())) {
						$this->view($method);
					} else {
						show_404();
					}
				break;
			}
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
	
	// = VIEW GROUPS =====
	public function viewGroups() {
		echo "groups";
		$data = array('bases' => $this->prepareList($this->database->getPublic()));
		$this->twig->render('databases/public', $data);
	}
	
	// = VIEW USER =====
	public function viewUser() {
		echo "user";
		$data = array('bases' => $this->prepareList($this->database->getPublic()));
		$this->twig->render('databases/public', $data);
	}
	
	// = VIEW =====
	public function view($id) {
		$base = $this->database->get($id);
		$data = array( 
			'base' => $this->jsonExec($base),
			'group' => $this->user->getGroup($base['group_id']),
			'strains' => array_map(function($o){return $this->jsonExec($o);}, $this->strain->getBase($id))
		);
		$this->twig->render('databases/view', $data);
	}
	
	// = CREATE =====
	public function create() {
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$info = array();
		if ($this->input->post('step') == '1') {
			if (isset($_FILES['csv_file']) && $_FILES['csv_file']['name'] != "") {
				if (($handle = fopen($_FILES['csv_file']['tmp_name'], "r")) !== FALSE) {
					$headers =  fgetcsv($handle, 0, $delimiter = ";", $enclosure = '"');
					$rows = array ();
					while (($data = fgetcsv($handle, 0, $delimiter = ";", $enclosure = '"')) !== FALSE) {
						array_push($rows, $data);
					}
					fclose($handle);
					$json = json_encode($rows);
					$name = explode('.', $_FILES['csv_file']['name'])[0];
					$this->twig->render('databases/create-2', array('basename' => $name, 'data' => $json, 'headers' => $headers, 'groups' => $_SESSION['groups']));
				} else {
					$info['error'] = "That file is not valid.";
					$this->twig->render('databases/create-1', $info);
				}
			} else {
				$info['error'] = "You must choose a CSV file to upload.";
				$this->twig->render('databases/create-1', $info);
			}
		} else if ($this->input->post('step') == '2') {
			if($this->form_validation->run("csv-create")) {
				$data = array (
					'name' => $this->input->post('basename'),
					'user_id' => $_SESSION['user']['id'],
					'group_id' => $this->input->post('group'),
					'marker_num' => count($this->input->post('mlvadata')),
					'metadatas' => json_encode($this->input->post('metadata')),
					'datas' => json_encode($this->input->post('mlvadata')),
					'state' => ($this->input->post('public') == 'on' ? 1 : 0)
				);
				$base_id = ($this->database->create($data));
				$strains = json_decode($this->input->post('data'), true);
				foreach($strains as &$strain) {
					$metadata = array ();
					$heads = $this->input->post('metadata');
					foreach($heads as &$head) {
						$metadata[$head] = $strain[array_search($head, $this->input->post('headers'))];
					}
					$mlvadata = array ();
					$heads = $this->input->post('mlvadata');
					foreach($heads as &$head) {
						$mlvadata[$head] = $strain[array_search($head, $this->input->post('headers'))];
					}
					$data = array (
						'name' => $strain[array_search($this->input->post('name'), $this->input->post('headers'))],
						'database_id' => $base_id,
						'metadatas' => json_encode($metadata),
						'datas' => json_encode($mlvadata)
					);
					$this->strain->add($data);
				}
				$this->view($base_id);
			} else {
				$data = array('basename' => $this->input->post('step'), 'data' => $this->input->post('step'), 'headers' =>$this->input->post('headers'));
				$this->twig->render('databases/create-2', $data);
			}
		} else {
			$this->twig->render('databases/create-1', $info);
		}
	}
	
	// = EXPORT =====
	public function export($id) {
		$base = $this->jsonExec($this->database->get($id));
		$strains = array_map(function($o){return $this->jsonExec($o);}, $this->strain->getBase($id));
		
		$rows = array( array_merge(array('name'), $base['metadatas'], $base['datas']) );
		foreach($strains as &$strain) {
			$row = array($strain['name']);
			foreach($base['metadatas'] as &$data) {
				if ( array_key_exists($data, $strain['metadatas'])) {
					array_push($row, $strain['metadatas'][$data]);
				} else {
					array_push($row, "");
				}
			}
			foreach($base['datas'] as &$data) {
				if ( array_key_exists($data, $strain['datas'])) {
					array_push($row, $strain['datas'][$data]);
				} else {
					array_push($row, "");
				}
			}
			array_push($rows, $row);
		}
		
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment;filename="'.$base['name'].'.csv"');
		$fp = fopen('php://output', 'w');
		foreach($rows as &$row) {
			fputcsv($fp, $row, $delimiter = ";", $enclosure = '"');
		}
		fclose($fp);
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
			$list = array_merge($list, $this->database->getGroup($group['id']));
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
