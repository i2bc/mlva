<?php
class Strains extends CI_Controller {

	public function __construct () {
    parent::__construct();
		$this->load->model('databases_model', 'database');
		$this->load->model('strains_model', 'strain');
		$this->load->model('panels_model', 'panel');
    // if (!$this->input->is_ajax_request()) show_403();
	}

  private function writeJson ($data) {
    $this->output
      ->set_content_type('application/json')
      ->set_output(json_encode($data));
  }

  public function add ($base_id) {
		if (authLevel($this->database->get($base_id)) < 2) return show_403();
		$this->load->helper('json');
	  $handle = fopen('php://input', 'r');
    while (($strain = getBlockJSON($handle)) !== false) {
			if (isset($strain['name'])) {
				// var_dump($strain);
				$this->strain->add([
					'name' => $strain['name'],
					'database_id' => $base_id,
					'metadata' => json_encode($strain['metadata']),
					'data' => json_encode($strain['data']),
				]);
			}
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

  public function post ($base_id) {
    if (authLevel($this->database->get($base_id)) < 2) return show_403();
		$this->load->helper('json');
	  $handle = fopen('php://input', 'r');
		$feedbacks = [];
    while (($strain = getBlockJSON($handle)) !== false) {
			// var_dump($strain);
			$feedback = ['data' => $strain];
			if ($strain['name'] == null) {
				$feedback['status'] = 'no-name';
			} else {
				try {
					$strain_id = $this->strain->replace($base_id, $strain['name'], [
						'database_id' => $base_id,
						'name' => $strain['name'],
						'metadata' => json_encode($strain['metadata']),
						'data' => json_encode($strain['data']),
					]);
					$feedback['status'] = 'ok';
					$feedback['id'] = $strain_id;
				} catch (\Exception $e) {
					$feedback['status'] = 'error';
					$feedback['error'] = $e;
				}
			}
			array_push($feedbacks, $feedback);
    }
    fclose($handle);
		$this->writeJson($feedbacks);
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
