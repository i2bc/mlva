<?php
class Databases extends CI_Controller {

	// = CONSTANTS =====
	const PUBLIC_STATE = 1;
	const NB_GROUPS_PER_PAGE = 20; // utile ?
	const NB_STRAINS_PER_PAGE = 100;
	const MATRIX_LIMIT = 20;

	// = CONSTRUCT =====
	public function __construct() {
		parent::__construct();
		$this->load->library('Twig');
		$this->load->model('databases_model', 'database');
		$this->load->model('strains_model', 'strain');
		$this->load->model('panels_model', 'panel');
		$this->load->model('users_model', 'user');
	}

	// = REMAP =====
	// Pages :
	//	- Global (Databases lists)
	//		~ databases/public	-> viewPublic : viewable by all (index for guests)
	//		~ databases/user	-> viewUser : if logged only (index for logged users)
	//		~ databases/create	-> create : if logged only
	//	- Specific to one database (databases/method/id)
	//		~ databases/1				-> view : viewable if authorized (depends on the database settings)
	//		~ databases/view/1			-> view : viewable if authorized (depends on the database settings)
	//		~ databases/map/1			-> map : viewable if authorized (depends on the database settings), also works with a query
	//		~ databases/exportCSV/1		-> exportCSV : viewable if authorized (depends on the database settings), also works with a query
	//		~ databases/query/1			-> query : viewable if authorized (depends on the database settings)
	//		~ databases/queryResult/1	-> queryResult : viewable if authorized (depends on the database settings), require a query
	//		~ databases/exportTree/1	-> exportTree : viewable if authorized (depends on the database settings), require a query
	//		~ databases/exportMatrix/1	-> exportMatrix : viewable if authorized (depends on the database settings), require a query
	//		~ databases/edit/1			-> edit : for the creator of the database only
	//		~ databases/import/1		-> import : for owners only
	//		~ databases/editPanels/1	-> editPanels : for owners only
	//		~ databases/delete/1		-> delete : for the creator of the database only
	// =============
	function _remap ($method, $ids) {
		list($method, $id, $page) = array_merge([$method], $ids, [0, 0]);
		if (!empty($id)) {
			$lvl = $this->authLevel($id);
			if ($lvl == -1) {
				show_404();
			} else { //  ID set
				switch ($method) {
					// API Kinda
					case "delete": ( $lvl >= 3 ? $this->delete($id) : show_403() ); break;
					case "strains": ( $lvl >= 1 ? $this->strains($id) : show_403() ); break;
					case "genonums": ( $lvl >= 1 ? $this->genonums($id) : show_403() ); break;
					case "addColumns": ( $lvl >= 2 ? $this->addColumns($id) : show_403() ); break;
					// Other routes
					case "view": ( $lvl >= 1 ? $this->view($id) : show_403() ); break;
					case "public": $this->viewPublic(); break;
					default: show_404(); break;
				}
			}
		} else {
			if (isLogged()) { // No ID / Logged
				switch ($method) {
					case "index":
					case "user": $this->viewUser(); break;
					case "public": $this->viewPublic(); break;
					case "create": $this->create(); break;
					case "createForm": $this->createForm(); break;
					default: show_404(); break;
				}
			} else { // No ID / Not Logged
				switch ($method) {
					case "index":
					case "public": $this->viewPublic(); break;
					default: show_404(); break;
				}
			}
		}
	}

	// = VIEW PUBLIC =====
	public function viewPublic () {
		$data = array('bases' => $this->database->getPublic(), 'session' => $_SESSION);
		$this->twig->render('databases/listPublic', $data);
	}

	// = VIEW GROUPS =====
	public function viewUser () {
		$group_data = array();
		foreach($_SESSION['groups'] as &$group) {
			$bases = $this->database->getGroup($group['id']);
			if (count($bases) >= 0) {
				$group_infos = $this->user->getGroup($group['id']);
				$group_data[$group['id']] = array(
					'bases' => $bases,
					'name' => $group_infos['name'],
					'description' => $group_infos['description'],
					'members' => $this->user->getUsersOfGroup($group['id']),
					'group_id' => $group['id']
				);
			}
		}
		$data = [
			'personal' => $this->database->getUserOnly($_SESSION['user']['id']),
		  'groups' => $group_data,
			'session' => $_SESSION,
		];
		$this->twig->render('databases/listUser', array_merge($data, getInfoMessages()));
	}

	// = VIEW =====
	public function view ($id) {
		$base = $this->database->get($id);
		if (!$base) return show_404();
		$this->twig->render('databases/view', array_merge([
			'base' => $base,
			'session' => $_SESSION,
			'panels' => $this->panel->getBase($id),
			'owner' => $this->getOwner($base['group_id'], $base['user_id']),
		], getInfoMessages()));
	}

	public function strains ($id) {
		if (!$this->input->is_ajax_request()) show_403();
		$offset = 0;
		if ($this->input->get('offset'))
			$offset = intval($this->input->get('offset'));
		$this->writeJson($this->strain->getBase($id, $offset));
	}

	public function genonums ($id) {
		if (!$this->input->is_ajax_request()) show_403();
		$panels = $this->panel->getBase($id);
		$genonums = [];
		// var_dump($panels);
		foreach ($panels as $panel) {
			$genonums[$panel['id']] = $this->panel->getGN($panel['id']);
		}
		$this->writeJson($genonums);
	}

	// = EDIT =====
	public function edit ($id) {
		if (!$this->input->is_ajax_request()) show_403();
		$base = $this->database->get($id);
		$this->load->library('form_validation');
		$this->form_validation->set_rules('name', 'Database name', 'trim|required|max_length[255]|alpha_dash_spaces');
		$this->form_validation->set_rules('description', 'Database Description', 'trim|max_length[1000]');
	  $this->form_validation->set_rules('groupId', 'Group', 'trim|required|integer');
	  $this->form_validation->set_rules('website', 'Website', 'trim|valid_url');
    if ($this->form_validation->run()) {
			$errors = '';
			$group_id = $this->input->post('groupId');
			$state = $this->input->post('state') === "true";
			if (($group_id != -1) && !inGroup($group_id, true)) {
				$errors .= "<p>You don't have the permission to add this database to this group</p>";
			} elseif (($group_id == -1) && !isOwnerById($base['user_id'])) {
				$errors .= "<p>You don't have the permission to set this database as personal</p>";
			} elseif (($state && $base['state'] != 1) && (!$this->database->isUnique($this->input->post('name')))) {
				$errors .= "<p>The name is already taken by another public database</p>";
			}
			if (empty($errors)) {
				// Send an email to admin if the state change from private to public
				if ($state && $base['state'] != 1) {
					$this->load->library('emailer');
					$database = $this->database->getShort(['databases.id' => $base['id']])[0];
					$this->emailer->notifyAdminDatabasePublic($database);
				}
				$this->database->update($id, [
					'name' => $this->input->post('name'),
					'description' => $this->input->post('description'),
					'website' => $this->input->post('website'),
					'group_id' => $group_id,
					'state' => $state,
				]);
				$this->writeJson($this->database->get($id)); // Show the updated data
			} else {
				$this->writeJson([ 'errors' => $errors ]);
			}
		} else {
			$this->writeJson([ 'errors' => validation_errors() ]);
		}
	}

	public function addColumns ($id) {
		if (!$this->input->is_ajax_request()) show_403();
		$base = $this->database->get($id);
		if (!$base['metadata']) $base['metadata'] = [];
		if (!$base['data']) $base['data'] = [];
		if ($this->input->post('metadata')) $base['metadata'] = array_merge($base['metadata'], $this->input->post('metadata'));
		if ($this->input->post('mlvadata')) $base['data'] = array_merge($base['data'], $this->input->post('mlvadata'));
		$this->database->update($id, [
			'marker_num' => $base['marker_num'] + count($this->input->post('mlvadata')),
			'metadata' => json_encode($base['metadata']),
			'data' => json_encode($base['data']),
		]);
	}

	// = CREATE =====
	public function create () {
		$this->twig->render('databases/create', getInfoMessages());
	}

	public function createForm () {
		if (!$this->input->is_ajax_request()) show_403();
		$this->load->library('form_validation');
		$this->form_validation->set_rules('key', 'Strain key/name', 'trim|required|max_length[255]|alpha_dash_spaces');
		$this->form_validation->set_rules('name', 'Database name', 'trim|required|max_length[255]|alpha_dash_spaces');
		$this->form_validation->set_rules('description', 'Database Description', 'trim|max_length[1000]');
		$this->form_validation->set_rules('groupId', 'Group', 'trim|required|integer');
		$this->form_validation->set_rules('website', 'Website', 'trim|valid_url');
		if ($this->form_validation->run()) {
			// Group ~
			if ($this->input->post('group') == -2) {
				$group_id = $this->createGroupWithDatabase($this->input->post('group_name'), $this->input->post('basename'));
			} else {
				//Make it personal db if the user has entered an invalid group_id
				$group_id = inGroup($this->input->post('group'), true) ? $this->input->post('group') : -1;
			}
			$state = $this->input->post('state') === "true";
			// Send an email to admin if the state change from private to public
			if ($state) {
				$this->load->library('emailer');
				$database = $this->database->getShort(['databases.id' => $base['id']])[0];
				$this->emailer->notifyAdminDatabasePublic($database);
			}
			$base_id = $this->database->create([
				'name' => $this->input->post('name'),
				'description' => $this->input->post('description'),
				'website' => $this->input->post('website'),
				'user_id' => $_SESSION['user']['id'],
				'group_id' => $group_id,
				'state' => $state,
				'marker_num' => count($this->input->post('mlvadata')),
				'metadata' => json_encode($this->input->post('metadata')),
				'data' => json_encode($this->input->post('mlvadata')),
			]);
			setFlash("success", "The database has been successfully created");
			$this->writeJson([ 'id' => $base_id ]);
		} else {
			$this->writeJson([ 'errors' => validation_errors() ]);
		}
	}


	// = DELETE =====
	public function delete($id) {
		//There is a missing check (to be sure that the user triggered this action) ~~~
		$base = $_SESSION['currentDatabase'];
		$this->load->helper('url');
		$this->strain->deleteDatabase($id);
		$this->database->delete($id);
		setFlash('info', 'The database '.$base['name'].' (n°'.$id.') has been deleted');
		redirect(base_url('databases/'));
	}

  private function writeJson ($data) {
    $this->output
      ->set_content_type('application/json')
      ->set_output(json_encode($data));
  }

	// ===========================================================================
	//  - DATABASES -
	// ===========================================================================

	// = AUTH LEVEL * =====
	// <- $id (Int)
	// -> Return the authorization level of the current user for the database $id.
	//	-1 = $id not found,		0 = Not allowed,
	// 	 1 = Public database,	2 = Member of the group (edit level)
	//	 3 = Creator/Owner, 	4 = Website Admin
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

	// = GET OWNER * =====
	function getOwner($group_id, $user_id) {
		if( $group_id == -1 ) {
			$owner = $this->user->get($user_id);
			return [ 'name' => $owner['username'], 'link' => 'users/profile/'.$owner["username"] ];
		} else {
			$owner = $this->user->getGroup($group_id);
			return [ 'name' => $owner['name'], 'link' => "" ]; // ~~~
		}
	}

	// = HANDLE PANELS * =====
	function handlePanels($base_id, $panels, $headers, $add = true) {
		$gn_cols = [];
		foreach($panels as $name => $panel) {
			$mvla = []; $gn = -1;
			foreach($headers as $i => $head) {
				if ($panel[$i] == 'X')
					{ array_push($mvla, $head); }
				if ($panel[$i] == 'GN') {
					if ($gn != -1) {
						$info['error'] = $info['error']."/n".$name." panel has more than one genotype number column";
					} else {
						$gn = $i;
					}
				}
			}
			// if (empty($mlva)) {
				// $info['error'] = $info['error']."/n".$name." panel is empty";
			// }
			$data = array (
				'name' => $name,
				'database_id' => $base_id,
				'data' => json_encode($mvla)
			);
			$existing_panel = $this->panel->exist($data);
			if ($add) {
				if (empty($existing_panel)) {
					$id = $this->panel->add($data);
				} else {
					$id = $existing_panel[0];
				}
				if ($gn != -1) {
					$gn_cols[$id] = $gn;
				}
			} else {
				if (!empty($existing_panel)) {
					$id = intval($existing_panel[0]['id']);
					if ($gn != -1) {
						$gn_cols[$id] = $gn;
					}
				}
			}
		}
		return $gn_cols;
	}

	// ===========================================================================

	// = CREATE GROUP WITH DATABASE * =====
	// Create a new group with the upload of a db and add the uploader to this group
	private function createGroupWithDatabase ($groupName, $databaseName='') {
		$groupName = !empty($groupName) && alpha_dash_spaces($groupName) ? removeAllSpaces($groupName) : $databaseName.'_Group';
		$inputs = ['name' => $groupName, 'permissions' => '{"database.view":1}'];
		$group_id = $this->user->createGroup($inputs);
		$this->user->addToGroup($user_id = $this->session->user['id'], $group_id);
		//Reload the user's groups
		$_SESSION['groups'] = $this->user->getUserGroups($user_id);
		setFlash('info', lang('auth_group_created'));
		return $group_id;
	}

}
