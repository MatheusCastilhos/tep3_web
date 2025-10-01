<?php
// SQL INJECTION: 
// nome','email'); DROP TABLE usuarios; --
// nome','email'); UPDATE usuarios SET nome = 'InfoBio Esteve Aqui', email = 'hackers@ufcspa.edu.br' WHERE email = 'maria@email.com'; --

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // receber valores (sem validação intencionalmente)
    $nome  = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';

    // Monta a query
    $sql = "INSERT INTO usuarios (nome, email) VALUES ('$nome', '$email')";

    // Mostra na tela exatamente o que será enviado ao MySQL
    echo "<p><strong>SQL gerada:</strong></p>";
    echo "<pre>" . htmlspecialchars($sql, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</pre>";

    // Executa a query
    $resultado = $pdo->query($sql);

}
?>

