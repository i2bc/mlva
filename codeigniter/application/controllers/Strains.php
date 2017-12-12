<?php
class Strains extends CI_Controller {

	public function __construct () {
    parent::__construct();
		$this->load->model('databases_model', 'database');
		$this->load->model('strains_model', 'strain');
		$this->load->model('panels_model', 'panel');
    // if (!$this->input->is_ajax_request()) show_403();
	}

  public function add ($base_id) {
		if (authLevel($this->database->get($base_id)) < 2) return show_403();
		$this->load->helper('json');
	  $handle = fopen('php://input', 'r');
    while (($strain = getBlockJSON($handle)) !== false) {
			$this->strain->add([
				'name' => $strain['name'],
				'database_id' => $base_id,
				'metadata' => json_encode($strain['metadata']),
				'data' => json_encode($strain['data']),
			]);
    }
    fclose($handle);
  }

  public function update ($base_id) {
    if (authLevel($this->database->get($base_id)) < 2) return show_403();
		$this->load->helper('json');
	  $handle = fopen('php://input', 'r');
    while (($strain = getBlockJSON($handle)) !== false) {
			$this->strain->update($base_id, $strain['name'], [
				'metadata' => json_encode($strain['metadata']),
				'data' => json_encode($strain['data']),
			]);
    }
    fclose($handle);
  }

  public function delete ($base_id) {
    if (authLevel($this->database->get($base_id)) < 3) return show_403();
		$this->load->helper('json');
	  $handle = fopen('php://input', 'r');
    while (($strain = getBlockJSON($handle)) !== false) {
			$this->strain->delete($base_id, $strain_id);
    }
    fclose($handle);
  }

}
