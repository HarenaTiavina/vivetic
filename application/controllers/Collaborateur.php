<?php
class Collaborateur extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->model('Collaborateur_model');
    }

    public function index() {
        $data['collaborateurs'] = $this->Collaborateur_model->getCollaborateursAvecCartes();
        $this->load->view('collaborateur_list', $data);
    }

    public function logs_par_date() {
        $date = $this->input->get('date');
        $data['logs'] = $this->Collaborateur_model->getLogsParDate($date);
        $this->load->view('logs_par_date', $data);
    }
}
?>
