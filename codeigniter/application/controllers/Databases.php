<?php
class Databases extends CI_Controller {

	// = CONSTANTS =====
	const PUBLIC_STATE = 1;
	const NB_GROUPS_PER_PAGE = 20; // utile ?
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
	//		~ databases/edit/1			-> edit : for owners only
	//		~ databases/import/1		-> import : for owners only
	//		~ databases/editPanels/1	-> editPanels : for owners only
	//		~ databases/delete/1		-> delete : for the creator of the database only
	// =============
	function _remap( $method, $id ) {
		if ( !empty($id) ) {
			$lvl = $this->authLevel($id[0]);
			if ( $lvl == -1 ) {
				show_404();
			} else {
				switch ($method) {
					case "view": ( $lvl >= 1 ? $this->view($id[0]) : show_403() ); break;
					case "query": ( $lvl >= 1 ? $this->query($id[0]) : show_403() ); break;
					case "queryResult": ( $lvl >= 1 ? $this->queryResult($id[0]) : show_403() ); break;
					case "exportCSV": ( $lvl >= 1 ? $this->exportCSV($id[0]) : show_403() ); break;
					case "exportTree": ( $lvl >= 1 ? $this->exportTree($id[0]) : show_403() ); break;
					case "exportMatrix": ( $lvl >= 1 ? $this->exportMatrix($id[0]) : show_403() ); break;
					case "exportMatrixMEGA": ( $lvl >= 1 ? $this->exportMatrixMEGA($id[0]) : show_403() ); break;
					case "map": ( $lvl >= 1 ? $this->map($id[0]) : show_403() ); break;
					case "edit": ( $lvl >= 2 ? $this->edit($id[0]) : show_403() ); break;
					case "import": ( $lvl >= 2 ? $this->import($id[0]) : show_403() ); break;
					case "editPanels": ( $lvl >= 2 ? $this->editPanels($id[0]) : show_403() ); break;
					case "delete": ( $lvl >= 3 ? $this->delete($id[0]) : show_403() ); break;
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
		$this->twig->render('databases/listPublic', $data);
	}

	// = VIEW GROUPS =====
	public function viewUser() {
		$group_data = array();
		foreach($_SESSION['groups'] as &$group) {
			$bases = $this->database->getGroup($group['id']);
			if (count($bases) > 0) {
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
		$data = array('personal' => $this->database->getUserOnly($_SESSION['user']['id']),
					  'groups' => $group_data, 'session' => $_SESSION);
		$this->twig->render('databases/listUser', array_merge($data, getInfoMessages()));
	}

	// = VIEW =====
	public function view($id) {
		$this->UpdateCurrentDatabase($id);
		$base = $_SESSION['currentDatabase'];
		$strains = $_SESSION['currentStrains'];

		$filter = $this->getFilter($id, $base['data']);
		if ($filter['id'] > 0) {
			$genonums = $this->panel->getGN($filter['id']);
			foreach($genonums as &$genonum)
				{ $genonum['data'] = json_decode($genonum['data'], true); }
			if (!empty($genonums)) {
				$showGN = true;
				foreach($strains as &$strain)
					{ $strain['genonum'] = $this->lookForGN($genonums, $filter['data'], $strain); }
			}
		}

		$data = array(
			'session' => $_SESSION,
			'level' => $this->authLevel($id),
			'base' => $base,
			'strains' => $strains,
			'panels' => $this->panel->getBase($id),
			'filter' => $filter,
			'owner' => $this->getOwner($base['group_id'], $base['user_id']),
			'showGN' => isset($showGN),
		);

		$this->twig->render('databases/view', array_merge($data, getInfoMessages()));
	}

	// = QUERY =====
	public function query($id) {
		$this->load->library('form_validation');
		$this->UpdateCurrentDatabase($id);
		$base = $_SESSION['currentDatabase'];

		if($this->form_validation->run('query')) {
			$all_strains = $_SESSION['currentStrains'];
			$strains = array ();
			$ref = $this->input->post('data');
			$max_dist = $this->input->post('max_dist');
			$nbMaxStrains = getIntOrZero($this->input->post('max_amount'));
			foreach($all_strains as &$strain) {
				if ($this->dataDistance($ref, $strain['data'], true) <= $max_dist) {
					$strain['dist_to_ref'] = $this->dataDistance($ref, $strain['data'], true);
					array_push( $strains, $strain );
				}
			}
			//Sort by hamming distance to reference
			usort($strains, "compareStrainByDistance");
			$strains =  array_slice($strains, 0, $nbMaxStrains);//Keep only the $nbMaxStrains first elements
			$this->load->helper('newick'); //Load the helper to compute the newick tree
			list($keys, $matrixDistance) = computeMatrixDistance($ref, $strains);
			$_SESSION['currentDatabase']['queried'] = true;
			$_SESSION['currentStrains'] = $strains;
			$_SESSION['currentRef'] = $ref;
			$_SESSION['currentDistKeys'] = $keys;
			$_SESSION['currentDistMat'] = $matrixDistance;
			
			$this->load->helper('newick');
			$filter = $this->getFilter($id, $base['data']);
			redirect(base_url('databases/queryResult/'.base_and_panel($id, $filter['id'])));
		} else {
			$data = array(
				'session' => $_SESSION,
				'base' => $base,
				'filter' => $this->getFilter($id, $base['data']),
				'owner' => $this->getOwner($base['group_id'], $base['user_id']),
			);

			$this->twig->render('databases/query', array_merge($data, getInfoMessages()));
		}

	}

	// = QUERY RESULT =====
	public function queryResult($id) {
		if ($this->CheckCurrentDatabase($id, true)) {
			$base = $_SESSION['currentDatabase'];
			$strains = $_SESSION['currentStrains'];
			$ref = $_SESSION['currentRef'];
			$keys = $_SESSION['currentDistKeys'];
			$matrixDistance = $_SESSION['currentDistMat'];

			$data = array(
				'session' => $_SESSION,
				'base' => $base,
				'strains' => $strains,
				'owner' => $this->getOwner($base['group_id'], $base['user_id']),
				'ref' => $ref,
				'filter' => $this->getFilter($id, $base['data']),
			);

			$this->twig->render('databases/queryResult', array_merge($data, getInfoMessages()));
		} else {
			setFlash('error', "You must have done a query to see that page.");
			redirect(base_url('databases/'.strval($base_id)));
		}
	}

	// = MAP =====
	public function map($id) {
		if ($this->CheckCurrentDatabase($id)) {
			$base = $_SESSION['currentDatabase'];
			$strains = $_SESSION['currentStrains'];

			$data = array(
				'session' => $_SESSION,
				'level' => $this->authLevel($id),
				'base' => $base,
				'strains' => $strains,
				'owner' => $this->getOwner($base['group_id'], $base['user_id']),
				'geoJson' => $this->createGeoJson($strains)
			);

			$this->twig->render('databases/map', array_merge($data, getInfoMessages()));
		} else {
			show_404();
		}
	}

	// = EDIT =====
	public function edit($id) {
		if ($this->CheckCurrentDatabase($id)) {
			$this->load->library('form_validation');
			$base = $_SESSION['currentDatabase'];

			if($this->form_validation->run('edit_db')) {
				$group_id = $this->input->post('group');
				if (($group_id != -1) && !inGroup($group_id, true)) {
					setFlash('error', "You don't have the permission to add this database to this group");
				} elseif (($group_id == -1) && !isOwnerById($base['user_id'])) {
					setFlash('error', "You don't have the permission to set this database as personal");
				} else {
					$updatedData = [
						'name' => $this->input->post('name'),
						'description' => $this->input->post('description'),
						'website' => $this->input->post('website'),
						'group_id' => $group_id,
						'state' => ($this->input->post('public') ? 1 : 0)
					];
					$this->database->update($id, $updatedData);
					setFlash('success', lang('auth_success_edit'));
					$base = $this->database->get($id);//Show the updated data
				}
			}

			$data = array(
				'session' => $_SESSION,
				'base' => $base,
				'owner' => $this->getOwner($base['group_id'], $base['user_id']),
			);
			$this->ForceUpdateCurrentDatabase($id);
			$this->twig->render('databases/edit', array_merge($data, getInfoMessages()));
		} else {
			show_404();
		}
	}

	// = EDIT PANELS =====
	public function editPanels($base_id) {
		if ($this->CheckCurrentDatabase($base_id)) {
			$this->load->library('form_validation');
			$base = $_SESSION['currentDatabase'];

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
						} elseif( $this->input->post('action') == "Delete" ) {
							$this->panel->delete($id);
						} elseif( $this->input->post('action') == "Generate" ) {
							$strains = array_map(function($o){return $this->jsonExec($o);}, $this->strain->getBase($base_id));
							$genonums = $this->panel->getGN($id);
							foreach($genonums as &$genonum)
								{ $genonum['data'] = json_decode($genonum['data'], true); }
							$values = array_map( function($genonum) { return $genonum['value']; }, $genonums );
							$filter = json_decode($panel['data']);
							foreach($strains as &$strain) {
								$gn = $this->lookForGN($genonums, $filter, $strain);
								if ($gn == "") {
									$value = max($values) + 1;
									$data = [
										'panel_id' => $id,
										'data' => $this->applyFilter($strain['data'], $filter),
										'value' => strval($value)."_temp",
									];
									array_push( $genonums, $data );
									array_push( $values, $value );
									$data['data'] = json_encode($data['data']);
									$this->panel->addGN($data);
								}
							}
							setFlash('success', 'The genotypes numbers have been successfully generated');
							redirect(base_url('databases/'.strval($base_id).'?panel='.$id));
						}
						redirect(base_url('databases/editPanels/'.strval($base_id)));
					}
				}
			}

			$data = array(
				'session' => $_SESSION,
				'base' => $base,
				'owner' => $this->getOwner($base['group_id'], $base['user_id']),
				'panels' => $this->panel->getBase($base_id)
			);
			$this->twig->render('databases/editPanels', array_merge($data, getInfoMessages()));

		} else {
			show_404();
		}
	}

	// = CREATE =====
	public function create() {
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$info = [ 'session' => $_SESSION ];
		if ($this->input->post('step') == '1') {
			if ($this->form_validation->run("csv-create1")) {
				$validity = $this->validCSV($_FILES['csv_file']);
				if ($validity[0]) {
					// === Step 1 ===
					list($headers, $rows) = $this->readCSV($validity[1], $this->input->post('csvMode'));
					list($struct, $panels, $rows) = $this->sortRows($rows);
					if (!empty($struct)) {
						list($key, $metadata, $mlvadata, $ignore) = $this->readStruct($headers, $struct);
					} else {
						list($key, $metadata, $mlvadata, $ignore) = [ "", [], [], [] ];
					}
					setFlash('data_csv_upload', $rows); //Save the data in a temporary session variable
					setFlash('head_csv_upload', $headers);
					setFlash('ignore_csv_upload', $ignore);
					setFlash('panel_csv_upload', $panels);
					$data = array(
						'session' => $_SESSION,
						'basename' => explode('.', $_FILES['csv_file']['name'])[0],
						'headers' => $headers,
						'groups' => $_SESSION['groups'],
						'metadata' => $metadata,
						'mlvadata' => $mlvadata,
						'key' => $key,
						'isPublic' => false,
						'location_key' => "location",
						'ignore' => $ignore,
					);
					$this->twig->render('databases/create/2', array_merge($data, getInfoMessages()));
				} else {
					$info['error'] = $validity[1];
					$this->twig->render('databases/create/1', $info);
				}
			} else {
				$this->twig->render('databases/create/1', $info);
			}
		} elseif ($this->input->post('step') == '2') {
			if ($this->form_validation->run("csv-create2")) {
				// === Step 2 ===
				$strains = getFlash('data_csv_upload');
				$headers = getFlash('head_csv_upload');
				$panels = getFlash('panel_csv_upload');
				// Group ~
				if ($this->input->post('group') == -2) {
					$group_id = $this->createGroupWithDatabase($this->input->post('group_name'), $this->input->post('basename'));
				} else {
					//Make it personal db if the user has entered an invalid group_id
					$group_id = inGroup($this->input->post('group'), true) ? $this->input->post('group') : -1;
				}
				// Base ~
				$base_id = $this->database->create( array (
					'name' => $this->input->post('basename'),
					'user_id' => $_SESSION['user']['id'],
					'group_id' => $group_id,
					'marker_num' => count($this->input->post('mlvadata')),
					'metadata' => json_encode($this->input->post('metadata')),
					'data' => json_encode($this->input->post('mlvadata')),
					'state' => ($this->input->post('public') == 'on' ? 1 : 0)
				));
				// Panels ~
				$gn_cols = $this->handlePanels($base_id, $panels, $headers);
				// Strains ~
				$this->addStrains($base_id, $strains, $headers, $this->input->post('metadata'), $this->input->post('mlvadata'), $gn_cols);
				// Geoloc ~
				if ($this->input->post('location_key')) {
					$strains = array_map(function($o){return $this->jsonExec($o);}, $this->strain->getBase($base_id));
					$this->getGeolocalisationFromLocation($strains, $this->input->post('location_key'));
				}
				setFlash("success", "The database has been successfully created");
				redirect(base_url('databases/'.strval($base_id)));
			} else {
				$data = array(
					'session' => $_SESSION,
					'basename' => $this->input->post('basename'),
					'headers' => getFlash('head_csv_upload'),
					'groups' => $_SESSION['groups'],
					'metadata' => $this->input->post('metadata'),
					'mlvadata' => $this->input->post('mlvadata'),
					'key' => $this->input->post('name'),
					'isPublic' => ($this->input->post('public') == 'on'),
					'location_key' => $this->input->post('location_key'),
					'ignore' => getFlash('ignore_csv_upload'),
				);
				$this->session->keep_flashdata('data_csv_upload');
				$this->session->keep_flashdata('panel_csv_upload');
				setFlash('head_csv_upload', $data['headers']);
				setFlash('ignore_csv_upload', $data['ignore']);
				$this->twig->render('databases/create/2', $data);
			}
		} else {
			$this->twig->render('databases/create/1', $info);
		}
	}

	// = IMPORT =====
	public function import($base_id) {
		if ($this->CheckCurrentDatabase($base_id)) {
			$this->load->library('form_validation');
			$this->load->helper(array('form', 'url'));
			$base = $_SESSION['currentDatabase'];
			$lvl = $this->authLevel($base_id);
			$info = [
				'session' => $_SESSION,
				'base' => $base,
				'owner' => $this->getOwner($base['group_id'], $base['user_id']),
				'level' => $lvl,
			];
			if ($this->input->post('step') == '1') {
				if ($this->form_validation->run("csv-create1")) {
					$validity = $this->validCSV($_FILES['csv_file']);
					if ($validity[0]) {
						list($headers, $strains) = $this->readCSV($validity[1], $this->input->post('csvMode'));
						list($struct, $panels, $strains) = $this->sortRows($strains);
						if (in_array("key", $headers)) {
							// === Step 1 ===
							if ( $lvl < 3 ) {
								$importGN = false;
								$deleteStrains = false;
							} else {
								$importGN = $this->input->post('importGN');
								$deleteStrains = $this->input->post('deleteStrains');
							}
							// Panels ~
							$gn_cols = $this->handlePanels($base_id, $panels, $headers, ($this->input->post('addPanels') == 'on'));
							if ( !$importGN ) { $gn_cols = []; }
							// Columns ~
							if (!empty($struct))
								{ list($key, $metadata, $mlvadata, $ignore) = $this->readStruct($headers, $struct); }
							else
								{ list($key, $metadata, $mlvadata, $ignore) = [ "", [], [], [] ]; }
							$newheaders = array_diff($headers, array_merge(array("key"), $base["metadata"], $base["data"], $ignore));
							if ($this->input->post('addColumns') && !empty($newheaders)) {
								setFlash('addStrains', $this->input->post('addStrains'));
								setFlash('updateStrains', $this->input->post('updateStrains'));
								setFlash('deleteStrains', deleteStrains);
								setFlash('head_csv_upload', $headers);
								setFlash('data_csv_upload', $strains);
								setFlash('gncol_csv_upload', $gn_cols);
								$data = array(
									'newheaders' => $newheaders,
									'metadata' => $metadata,
									'mlvadata' => $mlvadata,
								);
								$this->twig->render('databases/import/2', array_merge($data, $info, getInfoMessages()));
							} else {
								$this->handleStrains ($base_id, $strains, $headers,
										$this->input->post('updateStrains'), $this->input->post('addStrains'), $deleteStrains,
										$base["metadata"], $base["data"], $gn_cols);
								$this->ForceUpdateCurrentDatabase($base_id);
								redirect(base_url('databases/'.strval($base_id)));
							}
						} else {
							$info['error'] = "There must be a key column to recognize strains.";
							$this->twig->render('databases/import/1', $info);
						}
					} else {
						$info['error'] = $validity[1];
						$this->twig->render('databases/import/1', $info);
					}
				} else {
					$this->twig->render('databases/import/1', $info);
				}
			} elseif ($this->input->post('step') == '2') {
				// === Step 2 ===
				$base['metadata'] = array_merge( $this->input->post('metadata'), $base['metadata'] );
				$base['data'] = array_merge( $this->input->post('mlvadata'), $base['data'] );
				$data = array (
					'marker_num' => $base['marker_num'] + count($this->input->post('mlvadata')),
					'metadata' => json_encode($base['metadata']),
					'data' => json_encode($base['data']),
				);
				$this->database->update($base_id, $data);
				// === Step 1 ===
				$this->handleStrains ($base_id, getFlash('data_csv_upload'), getFlash('head_csv_upload'),
						getFlash('updateStrains'), getFlash('addStrains'), getFlash('deleteStrains'),
						$base["metadata"], $base["data"], getFlash('gncol_csv_upload'));
				$this->ForceUpdateCurrentDatabase($base_id);
				redirect(base_url('databases/'.strval($base_id)));
			} else {
				$this->twig->render('databases/import/1', $info);
			}
		} else {
			show_404();
		}
	}

	// = EXPORT CSV =====
	public function exportCSV($id) {
		if ($this->CheckCurrentDatabase($id)) {
			$this->load->library('form_validation');
			$base = $_SESSION['currentDatabase'];
			$strains = $_SESSION['currentStrains'];
			if($this->form_validation->run('export_db')) {
				if ( $this->input->post('panel') != -1 ) {
					$panel = $this->panel->get( $this->input->post('panel') );
					if ($panel['database_id'] == $id) {
						$mlvadata = json_decode($panel['data']);
						$panels = [ $panel ];
					} else {
						$mlvadata = $base['data'];
						$panels = $this->panel->getBase($id);
					}
				} else {
					$mlvadata = $base['data'];
					$panels = $this->panel->getBase($id);
				}
				if( !$this->input->post('advanced') ) {
					$panels = [];
				}
				$metadata = $this->input->post('metadata');
				// Header ~
				$gn_panels = array_map( function($panel) { return "genotype number ".$panel['name']; }, $panels );
				$rows = array( array_merge(array('key'), $metadata, $gn_panels, $mlvadata) );
				if( $this->input->post('advanced') ) {
					// Struct ~
					$row = array("[key]");
					foreach($metadata as &$data) { array_push($row, "info"); }
					foreach($panels as &$panel)  { array_push($row, "GN"); }
					foreach($mlvadata as &$data) { array_push($row, "mlva"); }
					array_push($rows, $row);
					// Panels ~
					$genonums = [];
					foreach($panels as &$panel) {
						$row = array("[panel] ".$panel['name']);
						$filter = json_decode($panel['data'], true);
						foreach($metadata as &$data)
							{ array_push($row, ""); }
						foreach($panels as &$panel2) {
							if ( $panel['name'] == $panel2['name'] ) { array_push($row, "GN"); }
							else { array_push($row, ""); }
						}
						foreach($mlvadata as &$data) {
							if (in_array($data, $filter) ) { array_push($row, "X"); }
							else { array_push($row, ""); }
						}
						array_push($rows, $row);
						// GN ~
						if( $this->input->post('tempGN') ) {
							$genonum = $this->panel->getGN($panel['id']);
						} else {
							$genonum = $this->panel->getValidGN($panel['id']);
						}
						foreach($genonum as $i => $gn) {
							$genonum[$i]['data'] = json_decode($gn['data'], true);
						}
						$genonums[$panel['id']] = ['filter' => $filter, 'GN' => $genonum];
					}
				}
				// Strains ~
				foreach($strains as &$strain) {
					$row = array($strain['name']);
					foreach($metadata as &$data) {
						if ( array_key_exists($data, $strain['metadata'])) { array_push($row, $strain['metadata'][$data]); }
						else { array_push($row, ""); }
					}
					foreach($panels as &$panel) {
						array_push($row, $this->lookForGN($genonums[$panel['id']]['GN'], $genonums[$panel['id']]['filter'], $strain));
					}
					foreach($mlvadata as &$data) {
						if ( array_key_exists($data, $strain['data'])) { array_push($row, $strain['data'][$data]); }
						else { array_push($row, ""); }
					}
					array_push($rows, $row);
				}
				header( 'Content-Type: text/csv' );
				header( 'Content-Disposition: attachment;filename="'.$base['name'].'.csv"');
				$fp = fopen('php://output', 'c');
				foreach($rows as &$row) {
					if ( $this->input->post('csvMode') == 'fr' ) { fputcsv($fp, $row, $delimiter = ";", $enclosure = '"'); }
					else { fputcsv($fp, $row, $delimiter = ",", $enclosure = '"'); }
				}
				fclose($fp);
			} else {
				$data = array(
					'session' => $_SESSION,
					'panels' => $this->panel->getBase($id),
					'base' => $base,
					'owner' => $this->getOwner($base['group_id'], $base['user_id']),
				);
				$this->twig->render('databases/export/csv', array_merge($data, getInfoMessages()));
			}
		} else {
			show_404();
		}
	}

	// = EXPORT TREE =====
	public function exportTree($id) {
		if ($this->CheckCurrentDatabase($id, true)) {
			$this->load->helper('newick');//Load the helper to compute the newick tree
			$base = $_SESSION['currentDatabase'];
			$data = array(
				'session' => $_SESSION,
				'base' => $base,
				'strains' => $_SESSION['currentStrains'],
				'newickTree' => getNewickTree($_SESSION['currentDistKeys'], $_SESSION['currentDistMat']),
				'owner' => $this->getOwner($base['group_id'], $base['user_id']),
			);
			$this->twig->render('databases/export/tree', array_merge($data, getInfoMessages()));
		} else {
			setFlash('error', "You must have done a query to see that page.");
			redirect(base_url('databases/'.strval($base_id)));
		}
	}

	// = EXPORT MATRIX =====
	public function exportMatrix($id) {
		if ($this->CheckCurrentDatabase($id, true)) {
			$this->load->helper('newick');//Load the helper to compute the newick tree
			$base = $_SESSION['currentDatabase'];
			$data = array(
				'session' => $_SESSION,
				'base' => $base,
				'strains' => $_SESSION['currentStrains'],
				'matrixAndKeys' => [$_SESSION['currentDistKeys'], $_SESSION['currentDistMat']],
				'owner' => $this->getOwner($base['group_id'], $base['user_id']),
			);
			$this->twig->render('databases/export/matrix', array_merge($data, getInfoMessages()));
		} else {
			setFlash('error', "You must have done a query to see that page.");
			redirect(base_url('databases/'.strval($base_id)));
		}
	}

	 // = EXPORT MATRIX =====
	 // Export the matrix distance to the MEGA format
	public function exportMatrixMEGA($id) {
		if ($this->CheckCurrentDatabase($id, true))
		{
			$matrixAndKeys = [$_SESSION['currentDistKeys'], $_SESSION['currentDistMat']];
			$description = 'Distance Matrix of queried database : '. $_SESSION['currentDatabase']['name'];
			exportToMEGAFormat($matrixAndKeys, $_SESSION['currentDatabase']['name'], $description);
		}
		else
		{
			setFlash('error', "You must have done a query to see that page.");
			redirect(base_url('databases/'.strval($base_id)));
		}
	}

	// = DELETE =====
	public function delete($id) {
		//There is a missing check (to be sure that the user triggered this action) ~~~
		$this->UpdateCurrentDatabase($id);
		$base = $_SESSION['currentDatabase'];
		$this->load->helper('url');
		$this->strain->deleteDatabase($id);
		$this->database->delete($id);
		setFlash('info', 'The database '.$base['name'].' (n°'.$id.') has been deleted');
		redirect(base_url('databases/'));
	}

	// ===========================================================================
	//  - DATABASES -
	// ===========================================================================

	// = UPDATE CURRENT DATABASE * =====
	// <- $id (Int), $queried (Bool)
	// Set $_SESSION['currentDatabase'] to the database $id and $_SESSION['currentStrains'] to its strains if needed.
	// Called in Query and View.
	function UpdateCurrentDatabase($id, $queried = false) {
		if ( !$this->CheckCurrentDatabase($id, $queried) )
			{ $this->ForceUpdateCurrentDatabase($id, $queried); }
	}

	// = FORCE UPDATE CURRENT DATABASE * =====
	// <- $id (Int), $queried (Bool)
	// Set $_SESSION['currentDatabase'] to the database $id and $_SESSION['currentStrains'] to its strains.
	// Called in Edit and Import.
	function ForceUpdateCurrentDatabase($id, $queried = false) {
		$_SESSION['currentDatabase'] = $this->jsonExec($this->database->get($id));
		$_SESSION['currentDatabase']['queried'] = $queried;
		$_SESSION['currentStrains'] = array_map(function($o){return $this->jsonExec($o);}, $this->strain->getBase($id));
	}

	// = Check CURRENT DATABASE * =====
	// <- $id (Int), $queried (Bool)
	// -> Return true if the current database has ID = $id and if $queried have been given, also check the query status.
	function CheckCurrentDatabase($id, $queried = null) {
		if ( empty($_SESSION['currentDatabase']) ) {
			return false;
		} else {
			if ( $queried === null ) {
				return ($_SESSION['currentDatabase']['id'] == $id);
			} else {
				return ($_SESSION['currentDatabase']['id'] == $id and $_SESSION['currentDatabase']['queried'] == $queried);

			}
		}
	}

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

	// = GET FILTER * =====
	// <- $base_id (Int), $default_data (Array)
	// -> Return [data, name, id] where data is the current datas (taking into account the current panel if needed), name is the name of the name and id its id.
	// id = -1 if there is not panel, id = -2 is the panel is not for the the current database.
	function getFilter($base_id, $default_data) {
		if ($this->input->get('panel')) {
			$panel = $this->panel->get( $this->input->get('panel') );
			if ($panel['database_id'] == $base_id) {
				return array (
					'data' => json_decode($panel['data']),
					'name' => $panel['name'],
					'id' => $panel['id'],
				);
			} else {
				return [ 'data' => $default_data, 'name' => "", 'id' => -2 ];
			}
		} else {
			return [ 'data' => $default_data, 'name' => "", 'id' => -1 ];
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
				if ($panel[$i] == 'GN')
					{ $gn = $i; }
			}
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
	//  - STRAINS  -
	// ===========================================================================

	// = DATA DISTANCE * =====
	function dataDistance($ref, $data, $ignore = false) {
		$geno = array();
		$dist = 0;
		foreach ($ref as $key => $value) {
			if( !in_array($value, ["", -1]) ) {
				if(array_key_exists($key, $data)) {
					if ( $value != $data[$key] ) {
						if ( in_array($value, ["", -1]) )
							{ $dist += $ignore ? 0 : 1 ; }
						else
							{ $dist += 1; }
					}
				} else {
					$dist += $ignore ? 0 : 1 ;
				}
			}
		}
		return $dist;
	}

	// = APPLY FILTER * =====
	function applyFilter($data, $filter) {
		$fdata = array();
		foreach($filter as &$head)
			{ $fdata[$head] = $data[$head]; }
		return $fdata;
	}

	// = LOOK FOR GN * =====
	function lookForGN($genonums, $filter, $strain) {
		$geno = $this->applyFilter($strain['data'], $filter);
		foreach ($genonums as $genonum) {
			$samplegeno = $genonum['data'];
			if ( $this->dataDistance($geno, $samplegeno) == 0 )
				{ return $genonum['value']; }
		}
		return "";
	}

	// = ADD STRAINS * =====
	function handleStrains ($base_id, $strains, $headers, $update, $add, $delete, $metaheads, $mlvaheads, $gn_cols) {
		$toAdd = array (); $toUpdate = array ();
		$key_col = array_search("key", $headers);
		foreach($strains as &$strain) {
			$base_strain = $this->strain->get($base_id, $strain[$key_col]);
			if ($base_strain && $update)
				{ array_push($toUpdate, [$base_strain, $strain]); }
			elseif (!$base_strain && $add)
				{ array_push($toAdd, $strain); }
		}
		$this->addStrains($base_id, $toAdd, $headers, $metaheads, $mlvaheads, $gn_cols);
		$this->updateStrains($base_id, $toUpdate, $headers, $metaheads, $mlvaheads, $gn_cols);
		if ($delete) {
			$existingStrains = array_map(function($strain) { return $strain['name']; }, $this->strain->getBaseKeys($base_id));
			$importedStrains = array_map(function($strain) use ($key_col){ return $strain[$key_col]; }, $strains);
			$oldStrains = array_diff($existingStrains, $importedStrains);
			foreach($oldStrains as &$key) {
				$this->strain->delete($base_id, $key);
			}
		}
	}

	// = ADD STRAINS * =====
	function addStrains ($base_id, $strains, $headers, $metaheads, $mlvaheads, $gn_cols) {
		# Panels and GN ~
		$filters = [];
		foreach($gn_cols as $id => $col) {
			$panel = $this->panel->get($id);
			$filters[$id] = json_decode($panel['data'], true);
		}
		# Strains ~
		foreach($strains as &$strain) {
			$strain_name = $strain[array_search($this->input->post('name'), $headers)];
			$metadata = array (); $heads = $metaheads;
			foreach($heads as &$head)
				{ $metadata[$head] = utf8_encode(strval($strain[array_search($head, $headers)])); }
			$mlvadata = array (); $heads = $mlvaheads;
			foreach($heads as &$head)
				{ $mlvadata[$head] = intval($strain[array_search($head, $headers)]); }
			$data = array (
				'name' => $strain_name,
				'database_id' => $base_id,
				'metadata' => json_encode($metadata),
				'data' => json_encode($mlvadata)
			);
			$this->strain->add($data);
			foreach($gn_cols as $id => $col) {
				if ( $strain[$col] != "" ) {
					$this->panel->setGN([
						'panel_id' => $id,
						'data' => json_encode($this->applyFilter($mlvadata, $filters[$id])),
					], $strain[$col]);
				}
			}
		}
	}

	// = UPDATE STRAINS * =====
	function updateStrains ($base_id, $strains, $headers, $metaheads, $mlvaheads, $gn_cols) {
		# Panels and GN ~
		$filters = [];
		foreach($gn_cols as $id => $col) {
			$panel = $this->panel->get($id);
			$filters[$id] = json_decode($panel['data'], true);
		}
		# Strains ~
		foreach($strains as &$strain_data) {
			list($base_strain, $strain) = $strain_data;
			$new_strain = $this->jsonExec($base_strain);
			$metadata = array (); $heads = $metaheads;
			foreach($heads as &$head)
				{ $new_strain['metadata'][$head] = utf8_encode(strval($strain[array_search($head, $headers)])); }
			$mlvadata = array (); $heads = $mlvaheads;
			foreach($heads as &$head)
				{ $new_strain['data'][$head] = intval($strain[array_search($head, $headers)]); }
			$this->strain->update($new_strain['id'], array(
				'metadata' => json_encode($new_strain['metadata']),
				'data' => json_encode($new_strain['data'])
			));
			foreach($gn_cols as $id => $col) {
				if ( $strain[$col] != "" ) {
					$this->panel->setGN([
						'panel_id' => $id,
						'data' => json_encode($this->applyFilter($new_strain['data'], $filters[$id])),
					], $strain[$col]);
				}
			}
		}
	}

	// ===========================================================================
	//  - CSV -
	// ===========================================================================

	// = VALID CSV * =====
	// <- $file (File)
	// -> Return [is_valid? (Bool), other]
	// 	is is_valid? = true, other = $handle of the $file
	// 	is is_valid? = false, other (String), error message
	function validCSV($file) {
		$mimes = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv');
		if ( $file['name'] != "" && in_array($file['type'], $mimes) ) {
			if ( ($handle = fopen($file['tmp_name'], "r")) !== FALSE ) {
				return [true, $handle];
			} else {
				return [false, "That file is not valid."];
			}
		} else {
			return [false, "You must choose a CSV file to upload."];
		}
	}

	// = READ CSV * =====
	// <- $hanlde (File Handle), $mode (String = "fr" or "en")
	// -> Return [ $headers, $rows ] where $headers if the first row of the csv and rows the rest of them.
	// also close the used handle.
	function readCSV($handle, $mode) {
		$delimiter = ($mode == 'fr') ? ";" : ",";
		$headers =  fgetcsv($handle, 0, $delimiter=$delimiter, $enclosure='"');
		$rows = array ();
		while (($data = fgetcsv($handle, 0, $delimiter=$delimiter, $enclosure='"')) !== FALSE) {
			array_push($rows, $data);
		}
		fclose($handle);
		return [ $headers, $rows ];
	}

	// = SORT ROWS * =====
	function sortRows ($rows) {
		$coltype = []; $panels = []; $strains = [];
		foreach($rows as &$row) {
			if (($key = array_search('[key]', $row)) !== false) {
				$coltype = $row;
				break;
			}
		}
		foreach($rows as &$row) {
			if (substr($row[$key], 0, 1) == "[") {
				if (substr($row[$key], 0, 8) == "[panel] ") {
					$panels[substr($row[$key], 8)] = $row;
				}
			} else {
				array_push($strains, $row);
			}
		}
		return [ $coltype, $panels, $strains ];
	}

	// = READ STRUCT * =====
	function readStruct ($headers, $struct) {
		$metadata = []; $mlvadata = []; $ignore = [];
		foreach($headers as $i => $head) {
			switch ($struct[$i]) {
				case "[key]":
					$key = $head;
				break;
				case "info":
					array_push($metadata, $head);
				break;
				case "mlva":
					array_push($mlvadata, $head);
				break;
				case "GN":
					array_push($ignore, $head);
				break;
			}
		}
		return [$key, $metadata, $mlvadata, $ignore];
	}

	// ===========================================================================

	// = JSON EXEC * =====
	function jsonExec($obj) {
		$obj['data'] = json_decode($obj['data'], true);
		$obj['metadata'] = json_decode($obj['metadata'], true);
		return $obj;
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

	/**
	 * Get the latitude and longitude from simple location (ex: Paris)
	 */
	private function getGeolocalisationFromLocation($strains, $locationKey)
	{
		$this->load->helper('curl');
		$url = 'http://nominatim.openstreetmap.org/search.php?format=json&limit=1&q=';
		$knownLocations = [];
		foreach ($strains as &$strain)
		{
			if ((empty($strain['metadata']['lon']) && empty($strain['metadata']['lat'])) && !empty($strain['metadata'][$locationKey]))
			{
				list($lat, $lon) = ['', ''];
				$location = $strain['metadata'][$locationKey];
				if (empty($knownLocations[md5($location)]))
				{
					if($response = json_decode(curl_get($url.urlencode($location))))
					{
						$knownLocations[md5($location)] = [$response[0]->lat, $response[0]->lon];
					}
					else
					{
						$knownLocations[md5($location)] = ["", ""];
					}
				}
				list($lat, $lon) = $knownLocations[md5($location)];

				$strain['metadata']['lat'] = $lat;
				$strain['metadata']['lon'] = $lon;
				$this->strain->update($strain['id'], ['metadata' => json_encode($strain['metadata'])]);
			}
		}
	}
}