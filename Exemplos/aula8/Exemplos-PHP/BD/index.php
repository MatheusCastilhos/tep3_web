<?php
require 'db.php';

$stmt = $pdo->query("SELECT * FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Usuários</title>
</head>
<body>
    <h1>Usuários Cadastrados</h1>
    <a href="adicionar.php">Adicionar Usuário</a>
    <ul>
        <?php foreach ($usuarios as $u): ?>
            <li><?php echo $u['nome'] . " - " . $u['email']; ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
