<?php
/*
  UNIVERSIDADE FEDERAL DE CIÊNCIAS DA SAÚDE DE PORTO ALEGRE
  CURSO DE INFORMÁTICA BIOMÉDICA
  DISCIPLINA DE TÓPICOS ESPECIAIS EM INFORMÁTICA BIOMÉDICA III
  NOMES: CARLISE SEBASTIANY E MATHEUS CASTILHOS
*/

function page_header($title = 'Vacinação Infantil – AP3') { ?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Sistema acadêmico para acompanhamento de vacinação infantil.">
  <title><?= htmlspecialchars($title) ?></title>

  <!-- Google Fonts + Ícones -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Estilo principal -->
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <div class="container">
    <h1>Acompanhamento de Vacinação Infantil</h1>
    <p class="subtitle">Tópicos Especiais de Programação III | UFCSPA 2025/2</p>
    <nav class="nav">
      <a href="index.php">Início</a>
      <a href="index.php?p=pacientes">Pacientes</a>
      <a href="index.php?p=vacinacoes">Vacinações</a>
      <a href="index.php?p=atrasos">Atrasos</a>
      <a href="index.php?p=estatisticas">Estatísticas</a>
    </nav>
  </div>
</header>

<main class="container">
<?php }

function page_footer() { ?>
</main>

<footer class="footer">
  <div class="container">
    <small>by Carlise Sebastiany and Matheus Castilhos</small>
  </div>
</footer>
</body>
</html>
<?php }