<!DOCTYPE html>
<html>
<head>
    <title>Logs par Date</title>
</head>
<body>
    <h1>Logs par Date</h1>
    <form method="get" action="<?= site_url('collaborateur/logs_par_date') ?>">
        <label for="date">Sélectionnez une date:</label>
        <input type="date" name="date" id="date" required>
        <button type="submit">Rechercher</button>
    </form>
    <table border="1">
        <tr>
            <th>Nom Collaborateur</th>
            <th>Cartes Utilisées</th>
            <th>Première Entrée</th>
            <th>Dernière Sortie</th>
            <th>Nombre de Pauses</th>
            <th>Volume de Pause (heures)</th>
        </tr>
        <?php if(isset($logs) && count($logs) > 0): ?>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= $log->nom ?></td>
                <td><?= $log->cartes ?></td>
                <td><?= $log->premiere_entree ?></td>
                <td><?= $log->derniere_sortie ?></td>
                <td><?= $log->nombre_pauses ?></td>
                <td><?= $log->volume_pause ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6">Aucun log trouvé pour la date sélectionnée.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
