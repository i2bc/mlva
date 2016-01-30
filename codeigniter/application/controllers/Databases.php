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
		$this->load->model('panels_model', 'panel');
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
					case "map":
						( $lvl >= 2 ? $this->map($id[0]) : show_403() );
					break;
					case "edit":
						( $lvl >= 2 ? $this->edit($id[0]) : show_403() );
					break;
					case "editPanels":
						( $lvl >= 2 ? $this->editPanels($id[0]) : show_403() );
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
					case "user":
						$this->viewUser();
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
		$data = array('bases' => $this->database->getPublic(), 'session' => $_SESSION);
		$this->twig->render('databases/public', $data);
	}

	// = VIEW GROUPS =====
	public function viewGroups() {
		$group_data = array();
		foreach($_SESSION['groups'] as &$group) {
			$bases = $this->database->getGroup($group['id']);
			if (count($bases) > 0) {
				$group_data[$group['id']] = array(
					'bases' => $bases,
					'name' => $group['name']
				);
			}
		}
		$data = array('groups' => $group_data, 'session' => $_SESSION);
		$this->twig->render('databases/group', array_merge($data, getInfoMessages()));
	}

	// = VIEW USER =====
	public function viewUser() {
		echo "user"; // ***
	}

	// = VIEW =====
	public function view($id) {
		$base = $this->jsonExec($this->database->get($id));
		$strains = array_map(function($o){return $this->jsonExec($o);}, $this->strain->getBase($id));
		$filter = $base['data'];
		$filtername = '';
		if ($this->input->get('panel')) {
			$panel = $this->panel->get( $this->input->get('panel') );
			if ($panel['database_id'] == $id) {
				$filter = json_decode($panel['data']);
				$filtername = $panel['name'];
			}
		}
		$data = array(
			'session' => $_SESSION,
			'base' => $base,
			'group' => $this->user->getGroup($base['group_id']),
			'owner' => $this->user->get($base['user_id']),
			'strains' => $strains,
			'level' => $this->authLevel($id),
			'panels' => $this->panel->getBase($id),
			'filter' => array( 'data' => $filter, 'name' => $filtername )
		);
		$this->twig->render('databases/view', array_merge($data, getInfoMessages()));
	}

	// = MAP =====
	public function map($id)
	{
		$base = $this->jsonExec($this->database->get($id));
		$strains = array_map(function($o){return $this->jsonExec($o);}, $this->strain->getBase($id));
		$data = array(
			'session' => $_SESSION,
			'base' => $base,
			'group' => $this->user->getGroup($base['group_id']),
			'owner' => $this->user->get($base['user_id']),
			'strains' => $strains,
			'geoJson' => $this->createGeoJson($strains),
			'level' => $this->authLevel($id),
		);
		$this->twig->render('databases/map', array_merge($data, getInfoMessages()));
	}


	// = EDIT =====
	public function edit($id) {
		$this->load->library('form_validation');
		$base = $this->database->get($id);

		if($this->form_validation->run('edit_db'))
		{
			$group_id = $this->input->post('group');
			if (($group_id != -1) && !inGroup($group_id, true))
			{
				setFlash('error', "You don't have the permission to add this database to this group");
			}
			elseif (($group_id == -1) && !isOwnerById($base['user_id']))
			{
				setFlash('error', "You don't have the permission to set this database as personal");
			}
			else
			{
				$updatedData = [
					'name' => $this->input->post('name'),
					'group_id' => $group_id,
					'state' => ($this->input->post('public') ? 1 : 0)
				];
				$this->database->update($updatedData, ['id' => $id]);
				setFlash('success', lang('auth_success_edit'));
				$base = $this->database->get($id);//Show the updated data
			}
		}

		$data = array(
			'session' => $_SESSION,
			'db' => $base,
		);
		$this->twig->render('databases/edit', array_merge($data, getInfoMessages()));
	}

	// = EDIT PANELS =====
	public function editPanels($base_id) {
		$this->load->library('form_validation');
		$base = $this->jsonExec($this->database->get($base_id));

		if($this->form_validation->run("edit_panel")) {
			$name = $this->input->post('name');
			$mvla = $this->input->post('data');
			$id = $this->input->post('id');
			if($id == -1) {
				$data = array (
					'name' => $name,
					'database_id' => $base_id,
					'data' => json_encode($mvla)
				);
				$this->panel->add($data);
				redirect(base_url('databases/editPanels/'.strval($base_id)));
			} else {
				$panel = $this->panel->get($id);
				if ($panel['database_id'] == $base_id) {
					$data = array (
						'name' => $name,
						'database_id' => $base_id,
						'data' => json_encode($mvla)
					);
					if( $this->input->post('action') == "Update" ) {
						$this->panel->update($id, $data);
					} else {
						$this->panel->delete($id);
					}
					redirect(base_url('databases/editPanels/'.strval($base_id)));
				}
			}
		}

		$data = array(
			'session' => $_SESSION,
			'base' => $base,
			'panels' => $this->panel->getBase($base_id)
		);
		$this->twig->render('databases/editPanels', array_merge($data, getInfoMessages()));
	}

	// = CREATE =====
	public function create() {
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$info = ['session' => $_SESSION];
		if ($this->input->post('step') == '1')
		{
			if (isset($_FILES['csv_file']) && $_FILES['csv_file']['name'] != "")
			{
				if (($handle = fopen($_FILES['csv_file']['tmp_name'], "r")) !== FALSE)
				{
					$headers =  fgetcsv($handle, 0, $delimiter = ";", $enclosure = '"');
					$rows = array ();
					while (($data = fgetcsv($handle, 0, $delimiter = ";", $enclosure = '"')) !== FALSE)
					{
						array_push($rows, $data);
					}
					fclose($handle);
					setFlash('data_csv_upload', $rows);//Save the data in a temporary session variable
					$name = explode('.', $_FILES['csv_file']['name'])[0];
					$data = array(
												'session' => $_SESSION,
												'basename' => $name,
												'headers' => $headers,
												'groups' => $_SESSION['groups']
												);
					$this->twig->render('databases/create-2', array_merge($data, getInfoMessages()));
				}
				else
				{
					$info['error'] = "That file is not valid.";
					$this->twig->render('databases/create-1', $info);
				}
			}
			else
			{
				$info['error'] = "You must choose a CSV file to upload.";
				$this->twig->render('databases/create-1', $info);
			}
		}
		else if ($this->input->post('step') == '2')
		{
			if($this->form_validation->run("csv-create"))
			{
				if ($this->input->post('group') == -2)
				{
					$group_id = $this->createGroupWithDatabase($this->input->post('group_name'), $this->input->post('basename'));
				}
				else
				{
					//Make it personal db if the user has entered an invalid group_id
					$group_id = inGroup($this->input->post('group'), true) ? $this->input->post('group') : -1;
				}
				$data = array (
					'name' => $this->input->post('basename'),
					'user_id' => $_SESSION['user']['id'],
					'group_id' => $group_id,
					'marker_num' => count($this->input->post('mlvadata')),
					'metadata' => json_encode($this->input->post('metadata')),
					'data' => json_encode($this->input->post('mlvadata')),
					'state' => ($this->input->post('public') == 'on' ? 1 : 0)
				);
				$base_id = $this->database->create($data);
				$strains = getFlash('data_csv_upload');
				foreach($strains as &$strain)
				{
					$metadata = array ();
					$heads = $this->input->post('metadata');
					foreach($heads as &$head)
					{
						$metadata[$head] = $strain[array_search($head, $this->input->post('headers'))];
					}
					$mlvadata = array ();
					$heads = $this->input->post('mlvadata');
					foreach($heads as &$head)
					{
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
				redirect(base_url('databases/'.strval($base_id)));
			}
			else
			{
				$data = array(
											'session' => $_SESSION,
											'basename' => $this->input->post('step'),
											'headers' =>$this->input->post('headers')
											);
				$this->session->keep_flashdata('data_csv_upload');
				$this->twig->render('databases/create-2', $data);
			}
		}
		else
		{
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
	public function delete($id)
	{
		//There is a missing check (to be sure that the user triggered this action)
		$this->load->helper('url');
		$this->strain->deleteDatabase($id);
		$this->database->delete($id);
		setFlash('info', 'The database n°'.$id.' has been deleted');
		redirect(base_url('databases/'));
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

	/**
	 * Create the json oject for displaying the strains on a map
	 */
	private function createGeoJson($strains)
	{
		$geoJson = [];
		$i = 0;
		foreach ($strains as $strain)
		{
			if (!empty($strain['metadata']['lon']) && !empty($strain['metadata']['lat'])) {
				$geoJson[$i]['name'] = $strain['name'];
				$geoJson[$i]['lat'] = $strain['metadata']['lat'];
				$geoJson[$i]['lng'] = $strain['metadata']['lon'];
				$i++;
			}
		}
		return json_encode($geoJson);
	}
	/**
	 * Create a new group with the upload of a db and add the uploader to this group
	 */
	private function createGroupWithDatabase($groupName, $databaseName='')
	{
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
