<?php
class Collaborateur_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Récupère la liste des collaborateurs avec un seul matricule et les cartes utilisées, séparées par des virgules
    public function getCollaborateursAvecCartes() {
        $this->db->select('Name AS nom, MAX(id) AS matricule, GROUP_CONCAT(DISTINCT card_no SEPARATOR ", ") AS cartes');
        $this->db->from('log_portiques');
        $this->db->group_by('Name');
        return $this->db->get()->result();
    }

    public function getLogsParDate($date) {
        $this->db->select('Name AS nom, GROUP_CONCAT(DISTINCT card_no SEPARATOR ", ") AS cartes, 
                           MIN(time) AS premiere_entree, MAX(time) AS derniere_sortie,
                           COUNT(CASE WHEN state = 1 THEN 1 END) AS nombre_pauses,
                           SUM(TIMESTAMPDIFF(HOUR, time, NOW())) AS volume_pause');
        $this->db->from('log_portiques');
        $this->db->where('DATE(time)', $date);
        $this->db->group_by('Name');
        return $this->db->get()->result();
    }
}
?>
