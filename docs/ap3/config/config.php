<?php
/*
  UNIVERSIDADE FEDERAL DE CIÊNCIAS DA SAÚDE DE PORTO ALEGRE
  CURSO DE INFORMÁTICA BIOMÉDICA
  DISCIPLINA DE TÓPICOS ESPECIAIS EM INFORMÁTICA BIOMÉDICA III
  NOMES: CARLISE SEBASTIANY E MATHEUS CASTILHOS
*/

/* ==============================================================
   VERSÃO COMPLETA (usando Composer + .env)
   -------------------------------------------------------------- */
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
  $pdo = new PDO(
    "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
  );
} catch (PDOException $e) {
  die("<b>Erro de conexão ao banco de dados:</b> " . htmlspecialchars($e->getMessage()));
}


/* ==============================================================
   VERSÃO SIMPLIFICADA (sem Composer, sem .env)
   --------------------------------------------------------------
   ➤ Descomente o bloco abaixo se quiser usar num servidor simples,
     como XAMPP ou um host que não suporte Composer.
   ➤ Lembre-se de preencher suas credenciais manualmente.
================================================================= */

/*
try {
  // Configuração manual do banco de dados
  $host = '127.0.0.1';        // ou 'localhost'
  $dbname = 'ap3_vacinas';    // nome do banco
  $user = 'root';             // usuário do MySQL
  $pass = '';                 // senha (padrão vazio no XAMPP)

  $pdo = new PDO(
    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
    $user,
    $pass,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
  );
} catch (PDOException $e) {
  die("<b>Erro de conexão ao banco de dados:</b> " . htmlspecialchars($e->getMessage()));
}
*/