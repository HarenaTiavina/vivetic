<?php
class Collaborateur_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function getCollaborateursAvecCartes() {
        $this->db->select('Name AS nom, MAX(id) AS matricule, GROUP_CONCAT(DISTINCT card_no SEPARATOR ", ") AS cartes');
        $this->db->from('log_portiques');
        $this->db->group_by('Name');
        return $this->db->get()->result();
    }

    public function getLogsParDate($date) {
        // Sous-requête pour obtenir la première entrée pour chaque collaborateur
        $this->db->select('Name AS nom, 
                           GROUP_CONCAT(DISTINCT card_no SEPARATOR ", ") AS cartes,
                           IFNULL(MIN(CASE WHEN SUBSTRING_INDEX(event_point_name, "-", -1) = "ENTREE" THEN time END), "Pas d\'entrée") AS premiere_entree');
        $this->db->from('log_portiques');
        $this->db->where('DATE(time)', $date);
        $this->db->group_by('Name');
        $entrees_first = $this->db->get_compiled_select();

        // Sous-requête pour obtenir la dernière sortie pour chaque collaborateur
        $this->db->select('Name AS nom, 
                           IFNULL(MAX(CASE WHEN SUBSTRING_INDEX(event_point_name, "-", -1) = "SORTIE" THEN time END), "Pas de sortie") AS derniere_sortie');
        $this->db->from('log_portiques');
        $this->db->where('DATE(time)', $date);
        $this->db->group_by('Name');
        $sorties_last = $this->db->get_compiled_select();

        // Requête principale pour les pauses
        $sql = "
        SELECT
            nom,
            SUM(is_pause) AS nombre_pauses,
            ROUND(SUM(volume_pause), 2) AS volume_pause
        FROM (
            SELECT
                Name AS nom,
                time,
                event_type,
                @prev_nom := @curr_nom,
                @curr_nom := Name,
                @prev_event_type := @curr_event_type,
                @curr_event_type := event_type,
                @prev_time := @curr_time,
                @curr_time := time,
                CASE
                    WHEN @prev_nom = Name
                         AND @prev_event_type = 'SORTIE'
                         AND event_type = 'ENTREE'
                         AND DATE(@prev_time) = DATE(time)
                    THEN 1
                    ELSE 0
                END AS is_pause,
                CASE
                    WHEN @prev_nom = Name
                         AND @prev_event_type = 'SORTIE'
                         AND event_type = 'ENTREE'
                         AND DATE(@prev_time) = DATE(time)
                    THEN TIMESTAMPDIFF(SECOND, @prev_time, time) / 3600
                    ELSE 0
                END AS volume_pause
            FROM (
                SELECT
                    Name,
                    time,
                    SUBSTRING_INDEX(event_point_name, '-', -1) AS event_type
                FROM
                    log_portiques
                WHERE
                    DATE(time) = ?
                ORDER BY
                    Name,
                    time
            ) AS sub
            CROSS JOIN (SELECT @prev_nom := NULL, @curr_nom := NULL, @prev_event_type := NULL, @curr_event_type := NULL, @prev_time := NULL, @curr_time := NULL) AS vars
        ) AS pauses
        GROUP BY
            nom
        ";

        // Exécution de la requête
        $query_pauses = $this->db->query($sql, array($date));

        // Récupération des résultats des pauses
        $pauses = $query_pauses->result(); // Utilise result() pour obtenir un tableau d'objets

        // Récupération des premières entrées et dernières sorties
        $this->db->reset_query(); // Réinitialise le query builder
        $this->db->select('entrees.nom, entrees.cartes, entrees.premiere_entree, sorties.derniere_sortie');
        $this->db->from("($entrees_first) entrees");
        $this->db->join("($sorties_last) sorties", 'entrees.nom = sorties.nom', 'left');
        $query_entrees_sorties = $this->db->get();
        $entrees_sorties = $query_entrees_sorties->result(); // Utilise result() pour obtenir un tableau d'objets

        // Fusion des résultats
        $result = array();
        foreach ($entrees_sorties as $es) {
            $nom = $es->nom;
            $result[$nom] = $es;
            // Initialiser nombre_pauses et volume_pause à 0
            $result[$nom]->nombre_pauses = 0;
            $result[$nom]->volume_pause = 0;
        }

        // Ajouter les données de pauses
        foreach ($pauses as $pause) {
            $nom = $pause->nom;
            if (isset($result[$nom])) {
                $result[$nom]->nombre_pauses = $pause->nombre_pauses;
                $result[$nom]->volume_pause = $pause->volume_pause;
            } else {
                // Si le collaborateur n'est pas dans les entrées/sorties, on l'ajoute
                $es = new stdClass();
                $es->nom = $nom;
                $es->cartes = '';
                $es->premiere_entree = 'Pas d\'entrée';
                $es->derniere_sortie = 'Pas de sortie';
                $es->nombre_pauses = $pause->nombre_pauses;
                $es->volume_pause = $pause->volume_pause;
                $result[$nom] = $es;
            }
        }

        // Retourner les résultats sous forme de tableau d'objets
        return array_values($result);
    }
}
?>
