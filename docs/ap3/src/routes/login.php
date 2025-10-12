<?php
/*
  UNIVERSIDADE FEDERAL DE CIÊNCIAS DA SAÚDE DE PORTO ALEGRE
  CURSO DE INFORMÁTICA BIOMÉDICA
  DISCIPLINA DE TÓPICOS ESPECIAIS EM INFORMÁTICA BIOMÉDICA III
  NOMES: CARLISE VR SEBASTIANY E MATHEUS CASTILHOS
*/
require_once __DIR__ . '/../functions.php';

// --- LOGOUT ---
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
  logout_user();
  header('Location: index.php');
  exit;
}

// --- LOGIN (POST) ---
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
  $user = trim($_POST['username'] ?? '');
  $pass = trim($_POST['password'] ?? '');

  if ($user === '') {
    $errors[] = 'Informe um nome de usuário.';
  } else {
    // login ilustrativo: aceita qualquer usuário/senha
    login_user($user);

    // Redirecionamento seguro e absoluto
    $redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php?p=pacientes';
    if (!str_starts_with($redirect, 'http')) {
      // garante que volta ao diretório raiz do app
      $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
      $redirect = $base . '/' . ltrim($redirect, '/');
    }

    header("Location: $redirect");
    exit;
  }
}

// --- SE JÁ ESTIVER LOGADO ---
if (is_logged_in()) {
  $user = htmlspecialchars($_SESSION['user']);
  ?>
  <section class="card">
    <h2>Você já está logado</h2>
    <p>Usuário: <strong><?= $user ?></strong></p>
    <p><a class="btn" href="index.php?p=login&action=logout">Sair</a></p>
  </section>
  <?php
  return;
}
?>

<section class="card">
  <h2>Login (modo de demonstração)</h2>
  <p>Use qualquer nome de usuário e senha (isso só demonstra a funcionalidade de bloqueio).</p>

  <?php if (!empty($errors)): ?>
    <div class="card" style="background:#fff3f3;color:#b00;">
      <?php foreach ($errors as $e) echo "<div>" . htmlspecialchars($e) . "</div>"; ?>
    </div>
  <?php endif; ?>

  <form method="post" action="index.php?p=login" class="login-form">
    <input type="hidden" name="action" value="login">
    <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? '') ?>">

    <div class="form-group">
      <label>Usuário</label>
      <input name="username" class="form-control" required placeholder="Digite seu nome de usuário">
    </div>

    <div class="form-group">
      <label>Senha</label>
      <input type="password" name="password" class="form-control" required placeholder="Digite sua senha">
    </div>

    <button class="btn fullwidth">Entrar</button>
  </form>

</section>