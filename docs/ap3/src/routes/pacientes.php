<?php
/*
  UNIVERSIDADE FEDERAL DE CIÊNCIAS DA SAÚDE DE PORTO ALEGRE
  CURSO DE INFORMÁTICA BIOMÉDICA
  DISCIPLINA DE TÓPICOS ESPECIAIS EM INFORMÁTICA BIOMÉDICA III
  NOMES: CARLISE SEBASTIANY E MATHEUS CASTILHOS
*/

require_once __DIR__ . '/../functions.php';

/* --- Criar novo paciente --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
  $nome = trim($_POST['nome'] ?? '');
  $data = trim($_POST['data_nascimento'] ?? '');

  if ($nome && $data) {
    $st = $pdo->prepare("INSERT INTO pacientes (nome, data_nascimento) VALUES (?, ?)");
    $st->execute([$nome, $data]);
  }

  header("Location: index.php?p=pacientes");
  exit;
}

/* --- Excluir paciente e suas vacinações --- */
if (isset($_GET['del'])) {
  $id = (int)$_GET['del'];

  $pdo->prepare("DELETE FROM vacinacoes WHERE paciente_id = ?")->execute([$id]);
  $pdo->prepare("DELETE FROM pacientes WHERE id = ?")->execute([$id]);

  header("Location: index.php?p=pacientes");
  exit;
}

$rows = $pdo->query("SELECT * FROM pacientes ORDER BY id")->fetchAll();
?>

<section class="card">
  <h2>Pacientes</h2>

  <form method="post">
    <input type="hidden" name="action" value="create">

    <div class="row-3">
      <div>
        <label>Nome</label>
        <input name="nome" class="form-control" placeholder="Ex: Ana Silva" required>
      </div>
      <div>
        <label>Data de Nascimento</label>
        <input type="date" name="data_nascimento" class="form-control" required>
      </div>
    </div>

    <div style="margin-top:10px;">
      <button class="btn">Adicionar Paciente</button>
    </div>
  </form>
</section>

<section class="card">
  <h3>Lista de Pacientes</h3>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Nascimento</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= $r['id'] ?></td>
          <td><?= htmlspecialchars($r['nome']) ?></td>
          <td><?= htmlspecialchars($r['data_nascimento']) ?></td>
          <td>
            <a class="btn secondary" 
               href="index.php?p=pacientes&del=<?= $r['id'] ?>" 
               onclick="return confirm('Apagar paciente e todas as suas vacinações?');">
              Excluir
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>