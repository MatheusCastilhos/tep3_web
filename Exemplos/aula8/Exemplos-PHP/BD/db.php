<?php
$host = "localhost";
$dbname = "infobio";
$username = "root";
$password = ""; // senha do username, se tiver. Estamos usando em branco.

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>
