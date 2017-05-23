<?php
class Panels extends CI_Controller {

	public function __construct () {
    parent::__construct();
		$this->load->model('databases_model', 'database');
		$this->load->model('panels_model', 'panel');
    if (!$this->input->is_ajax_request()) show_403();
	}

  private function writeJson ($data) {
    $this->output
      ->set_content_type('application/json')
      ->set_output(json_encode($data));
  }

  public function make () {
    $this->load->library('form_validation');
    if ($this->form_validation->run("edit_panel")) {
      $baseId = $this->input->post('baseId');
      $name = $this->input->post('name');
      $mvla = $this->input->post('data');
      if (authLevel($this->database->get($baseId)) >= 2) {
        $panelId = $this->panel->add([
          'name' => $name,
          'database_id' => $baseId,
          'data' => json_encode($mvla)
        ]);
        $this->writeJson($this->panel->get($panelId));
      } else {
        show_403();
      }
    } else {
      $this->writeJson([ 'error' => 'wrong form' ]);
    }
  }

  public function delete ($id) {
    if ($panel = $this->panel->get($id)) {
      $baseId = $panel['database_id'];
      if (authLevel($this->database->get($baseId)) >= 2) {
        $this->panel->delete($id);
      } else {
        show_403();
      }
    } else {
      show_403();
    }
  }

  public function update ($id) {
    $this->load->library('form_validation');
    if ($this->form_validation->run("edit_panel")) {
      $name = $this->input->post('name');
      $mvla = $this->input->post('data');
      if ($base = $this->panel->get($id)) {
        $baseId = $base['database_id'];
        if (authLevel($this->database->get($baseId)) >= 2) {
          $this->panel->update($id, [
            'name' => $name,
            'database_id' => $baseId,
            'data' => json_encode($mvla)
          ]);
          $this->writeJson($this->panel->get($id));
        } else { show_403(); }
      } else { show_403(); }
    } else {
      $this->writeJson([ 'error' => 'wrong form' ]);
    }
  }

	public function addGN ($id) {
		if ($panel = $this->panel->get($id)) {
			$baseId = $panel['database_id'];
			if (authLevel($this->database->get($baseId)) >= 2) {
				foreach ($this->input->post('GN') as $gn) {
					$this->panel->addGN([
						'panel_id' => $id,
						'value' => $gn['value'],
						'data' => json_encode($gn['data']),
					]);
				}
				$this->writeJson($this->input->post('GN'));
			} else { show_403(); }
		} else { show_403(); }
	}

	public function updateGN ($id) {
		if ($panel = $this->panel->get($id)) {
			$baseId = $panel['database_id'];
			if (authLevel($this->database->get($baseId)) >= 2) {
				foreach ($this->input->post('GN') as $gn) {
					$this->panel->updateGN($id,
						$gn['nValue'],
						$gn['oValue'],
						$gn['data']
					);
				}
				// $this->writeJson($this->input->post('GN'));
			} else { show_403(); }
		} else { show_403(); }
	}

}
