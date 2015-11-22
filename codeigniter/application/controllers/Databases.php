<?php
class Databases extends CI_Controller {

	// = CONSTANTS =====
	const PUBLIC_STATE = 1;
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
		if ( !empty($id) ) {
			$lvl = $this->authLevel($id[0]);
			if ( $lvl == -1 ) {
				show_404();
			} else {
				switch ($method) {
					case "view":
						( $lvl >= 1 ? $this->view($id[0]) : show_403() );
					break;
					case "export":
						( $lvl >= 1 ? $this->export($id[0]) : show_403() );
					break;
					case "delete":
						( $lvl >= 3 ? $this->delete($id[0]) : show_403() );
					break;
					case "public":
						$this->viewPublic();
					break;
					default:
						show_404();
					break;
				}
			}
		} else {
			if ( isLogged() ) {
				switch ($method) {
					case "index":
					case "groups":
						$this->viewGroups();
					break;
					case "public":
						$this->viewPublic();
					break;
					case "create":
						$this->create();
					break;
					default:
						$lvl = $this->authLevel($method);
						if ( $lvl >= 1 ) {
							$this->view($method);
						} else if ( $lvl >= 0 ) {
							show_403();
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
					default:
						$lvl = $this->authLevel($method);
						if ( $lvl >= 1 ) {
							$this->view($method);
						} else if ( $lvl >= 0 ) {
							show_403();
						} else {
							show_404();
						}
					break;
				}
			}
		}
	}
	
	// = VIEW PUBLIC =====
	public function viewPublic() {
		$data = array('bases' => $this->prepareList($this->database->getPublic()));
		$this->twig->render('databases/public', $data);
	}
	
	// = VIEW GROUPS =====
	public function viewGroups() {
		$group_data = array();
		foreach($_SESSION['groups'] as &$group) {
			$bases = $this->prepareList($this->database->getGroup($group['id']));
			if (count($bases) > 0) {
				$group_data[$group['id']] = array(
					'bases' => $bases,
					'name' => $group['name']
				);
			}
		}
		$data = array('groups' => $group_data);
		$this->twig->render('databases/group', $data);
	}
	
	// = VIEW USER =====
	public function viewUser() {
		echo "user";
		$data = array('bases' => $this->prepareList($this->database->getPublic()));
		$this->twig->render('databases/public', $data);
	}
	
	// = VIEW =====
	public function view($id) {
		$base = $this->jsonExec($this->database->get($id));
		$strains = array_map(function($o){return $this->jsonExec($o);}, $this->strain->getBase($id));
		list($orderBy, $order) = $this->getOrder($base['metadata']);
		if ($orderBy != "id") {
			usort($strains, $this->cmp($orderBy));
		}
		if ($order != "desc") {
			$strains = array_reverse($strains);
		}
		$data = array( 
			'base' => $base,
			'group' => $this->user->getGroup($base['group_id']),
			'strains' => $strains,
			'level' => $this->authLevel($id)
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
					'metadata' => json_encode($this->input->post('metadata')),
					'data' => json_encode($this->input->post('mlvadata')),
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
						$mlvadata[$head] = intval($strain[array_search($head, $this->input->post('headers'))]);
					}
					$data = array (
						'name' => $strain[array_search($this->input->post('name'), $this->input->post('headers'))],
						'database_id' => $base_id,
						'metadata' => json_encode($metadata),
						'data' => json_encode($mlvadata)
					);
					$this->strain->add($data);
				}
				redirect('/databases/'.strval($base_id));
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
		
		$rows = array( array_merge(array('name'), $base['metadata'], $base['data']) );
		foreach($strains as &$strain) {
			$row = array($strain['name']);
			foreach($base['metadata'] as &$data) {
				if ( array_key_exists($data, $strain['metadata'])) {
					array_push($row, $strain['metadata'][$data]);
				} else {
					array_push($row, "");
				}
			}
			foreach($base['data'] as &$data) {
				if ( array_key_exists($data, $strain['data'])) {
					array_push($row, $strain['data'][$data]);
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
	
	// = DELETE =====
	public function delete($id) {
		$this->load->helper('url');
		$this->strain->deleteDatabase($id);
		$this->database->delete($id);
		redirect('/databases/');
	}
	
	// = PREPARE LIST * =====
	function prepareList($bases) {
		foreach ($bases as &$base) {
			$base['creator_name'] = $this->user->get($base['user_id'])['username'];
			$base['panels_nb'] = 3; // ***
			$base['strains_nb'] = count($this->strain->getBase($base['id']));
			$base['disabled_strains_nb'] = 0;
		}
		return $bases;
	}
	
	// = AUTH LEVEL * =====
	function authLevel($id) {
		if ($base = $this->database->get($id)) {
			if ( isAdmin() ) {
				return 4; // Admin
			}
			if ( isLogged() ) {
				if ( isOwnerById($base['user_id']) ) {
					return 3; // Owner
				} else if ( inGroup($base['group_id']) ) {
					return 2; // Member
				}
			}
			if ($base['state'] == self::PUBLIC_STATE) {
				return 1; // Public
			}
			return 0; // Not Allowed
		} else {
			return -1; // Not Found
		}
	}
	
	// = JSON EXEC * =====
	function jsonExec($obj) {
		$obj['data'] = json_decode($obj['data'], true);
		$obj['metadata'] = json_decode($obj['metadata'], true);
		return $obj;
	}
	
	// = GET ORDER * =====
	function getOrder($allowedOrderBy = [], $allowedOrders = ['asc', 'desc'], $defaultOrder = 'asc') {
		if (!in_array($orderBy = $this->input->get('orderBy'), $allowedOrderBy)) {
			$orderBy = 'id';
		}

		if (!in_array($order = $this->input->get('order'), $allowedOrders)) {
			$order = $defaultOrder;
		}
		return [$orderBy, $order];
	}
	
	// = CMP ATTR * =====
	function cmp($attr) {
		return eval("return (function (\$a, \$b) { return strcmp(\$a['metadata']['$attr'], \$b['metadata']['".$attr."']); });");
	}
}
