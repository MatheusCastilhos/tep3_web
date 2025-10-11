<?php
/* UNIVERSIDADE FEDERAL DE CIÊNCIAS DA SAÚDE DE PORTO ALEGRE
   CURSO DE INFORMÁTICA BIOMÉDICA
   DISCIPLINA DE TÓPICOS ESPECIAIS EM INFORMÁTICA BIOMÉDICA III
   NOMES: CARLISE SEBASTIANY E MATHEUS CASTILHOS
 */
require_once __DIR__ . '/src/layout.php';

$p = $_GET['p'] ?? 'home';

page_header();

switch ($p) {
  case 'pacientes':
    require __DIR__ . '/src/routes/pacientes.php';
    break;

  case 'vacinacoes':
    require __DIR__ . '/src/routes/vacinacoes.php';
    break;

  case 'atrasos':
    require __DIR__ . '/src/routes/atrasos.php';
    break;

  case 'estatisticas':
    require __DIR__ . '/src/routes/estatisticas.php';
    break;

  default:
    ?>
    <section class="card">
      <h2>Bem-vindo(a)!</h2>
      <p>
        Esta aplicação permite o <strong>cadastro e acompanhamento de vacinas infantis</strong>,
        com base no calendário oficial. É possível registrar pacientes, aplicar doses, verificar
        atrasos e visualizar estatísticas gerais.
      </p>
      <p style="margin-top:10px;">
        Escolha uma das opções acima para começar.
      </p>
    </section>
    <?php
    break;
}

page_footer();