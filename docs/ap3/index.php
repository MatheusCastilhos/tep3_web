<?php
/*
  UNIVERSIDADE FEDERAL DE CIÊNCIAS DA SAÚDE DE PORTO ALEGRE
  CURSO DE INFORMÁTICA BIOMÉDICA
  DISCIPLINA DE TÓPICOS ESPECIAIS EM INFORMÁTICA BIOMÉDICA III
  NOMES: CARLISE VR SEBASTIANY E MATHEUS CASTILHOS
*/

// --- Sessão e diretório temporário ---
ini_set('session.save_path', __DIR__ . '/tmp');
if (!is_dir(__DIR__ . '/tmp')) mkdir(__DIR__ . '/tmp');
if (session_status() === PHP_SESSION_NONE) session_start();

// --- Dependências principais ---
require_once __DIR__ . '/src/functions.php';
require_once __DIR__ . '/src/layout.php';

// --- Determina página atual ---
$p = $_GET['p'] ?? 'home';

if ($p === 'login') {
  // Login via POST
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/src/routes/login.php';
    exit;
  }

  // Logout via GET (?action=logout)
  if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    require __DIR__ . '/src/routes/login.php';
    exit;
  }
}

page_header();

switch ($p) {
  // === ÁREAS PROTEGIDAS ===
  case 'pacientes':
    ensure_auth();
    require __DIR__ . '/src/routes/pacientes.php';
    break;

  case 'vacinacoes':
    ensure_auth();
    require __DIR__ . '/src/routes/vacinacoes.php';
    break;

  case 'atrasos':
    ensure_auth();
    require __DIR__ . '/src/routes/atrasos.php';
    break;

  // === ÁREAS PÚBLICAS ===
  case 'estatisticas':
    require __DIR__ . '/src/routes/estatisticas.php';
    break;

  case 'login':
    require __DIR__ . '/src/routes/login.php';
    break;

  // === PÁGINA INICIAL ===
  default:
    ?>
    <section class="card home-intro">
      <h2>Bem-vindo(a)!</h2>
      <p>
        Esta aplicação foi desenvolvida para o acompanhamento do 
        <strong>calendário de vacinação infantil</strong>, permitindo o registro de pacientes,
        o controle de doses aplicadas e a verificação de possíveis atrasos, 
        com base nas faixas etárias previstas pelo Ministério da Saúde.
      </p>

      <p class="info-tip">
        As seções <strong>“Início”</strong> e <strong>“Estatísticas”</strong> estão disponíveis para visualização pública.  
        Para acessar o restante das funcionalidades (como cadastrar pacientes, registrar vacinações ou verificar atrasos)
        é necessário realizar login no sistema.
      </p>

      <?php if (!empty($_SESSION['user'])): ?>
        <div class="login-status">
          <i class="bi bi-person-circle"></i>
          <span>Você está logado como <strong><?= htmlspecialchars($_SESSION['user']) ?></strong>.</span>
        </div>
      <?php else: ?>
        <div class="login-status">
          <i class="bi bi-lock"></i>
          <span>
            Modo visitante — <a href="index.php?p=login" class="link-login">faça login</a>
            para acessar as áreas administrativas do sistema.
          </span>
        </div>
      <?php endif; ?>
    </section>
    <?php
    break;
}

page_footer();