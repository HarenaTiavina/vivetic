<!DOCTYPE html>
<html>
<head>
    <title>Liste des collaborateurs</title>
</head>
<body>
    <h1>Liste des collaborateurs</h1>
    <table border="1">
        <tr>
            <th>Nom</th>
            <th>Matricule</th>
            <th>Cartes</th>
        </tr>
        <?php foreach ($collaborateurs as $collaborateur): ?>
        <tr>
            <td><?= $collaborateur->nom ?></td>
            <td><?= $collaborateur->matricule ?></td>
            <td><?= $collaborateur->cartes ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
