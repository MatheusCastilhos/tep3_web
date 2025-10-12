<?php
/*
  UNIVERSIDADE FEDERAL DE CIÊNCIAS DA SAÚDE DE PORTO ALEGRE
  CURSO DE INFORMÁTICA BIOMÉDICA
  DISCIPLINA DE TÓPICOS ESPECIAIS EM INFORMÁTICA BIOMÉDICA III
  NOMES: CARLISE VR SEBASTIANY E MATHEUS CASTILHOS
*/

require_once __DIR__ . '/../functions.php';

/* --- Criar nova vacinação --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
  $paciente_id = (int)($_POST['paciente_id'] ?? 0);
  $vacina      = trim($_POST['vacina'] ?? '');
  $equipe      = trim($_POST['equipe'] ?? '');
  $data        = trim($_POST['data_aplicacao'] ?? '');
  $endereco    = trim($_POST['endereco'] ?? '');
  $posto       = trim($_POST['posto_saude'] ?? '');

  if ($paciente_id && $vacina && $equipe && $data && $endereco && $posto) {
    $pdo->prepare("
      INSERT INTO vacinacoes (paciente_id, vacina, equipe, data_aplicacao, endereco, posto_saude)
      VALUES (?, ?, ?, ?, ?, ?)
    ")->execute([$paciente_id, $vacina, $equipe, $data, $endereco, $posto]);
  }

  header("Location: index.php?p=vacinacoes");
  exit;
}

/* --- Excluir vacinação --- */
if (isset($_GET['del'])) {
  $id = (int)$_GET['del'];
  $pdo->prepare("DELETE FROM vacinacoes WHERE id = ?")->execute([$id]);
  header("Location: index.php?p=vacinacoes");
  exit;
}

/* --- Ordenação --- */
$order = $_GET['ordem'] ?? 'desc';
$order_sql = ($order === 'asc') ? 'ASC' : 'DESC';

/* --- Paginação --- */
$por_pagina = 10;
$total = $pdo->query("SELECT COUNT(*) FROM vacinacoes")->fetchColumn();
$paginas = ceil($total / $por_pagina);
$pagina_atual = max(1, (int)($_GET['pg'] ?? 1));
$offset = ($pagina_atual - 1) * $por_pagina;

/* --- Consultas --- */
$pacientes  = $pdo->query("SELECT id, nome FROM pacientes ORDER BY nome")->fetchAll();

$stmt = $pdo->prepare("
  SELECT v.*, p.nome 
  FROM vacinacoes v 
  JOIN pacientes p ON p.id = v.paciente_id 
  ORDER BY v.data_aplicacao $order_sql 
  LIMIT :limite OFFSET :offset
");
$stmt->bindValue(':limite', $por_pagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$vacinacoes = $stmt->fetchAll();
?>

<section class="card">
  <h2>Vacinações</h2>

  <form method="post">
    <input type="hidden" name="action" value="create">

    <div class="row-3">
      <div>
        <label>Paciente</label>
        <select name="paciente_id" class="form-control" required>
          <option value="">-- selecione --</option>
          <?php foreach ($pacientes as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Vacina</label>
        <input name="vacina" class="form-control" placeholder="Ex: Pentavalente" required>
      </div>
      <div>
        <label>Equipe</label>
        <input name="equipe" class="form-control" placeholder="Ex: Equipe Leste" required>
      </div>
    </div>

    <div class="row-3" style="margin-top:10px;">
      <div>
        <label>Data de Aplicação</label>
        <input type="date" name="data_aplicacao" class="form-control" required>
      </div>
      <div>
        <label>Endereço</label>
        <input name="endereco" class="form-control" placeholder="Rua, nº, bairro" required>
      </div>
      <div>
        <label>Posto de Saúde</label>
        <input name="posto_saude" class="form-control" placeholder="Unidade de Saúde Central" required>
      </div>
    </div>

    <div style="margin-top:10px;">
      <button class="btn">Adicionar Vacinação</button>
    </div>
  </form>
</section>

<section class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;">
    <h3>Lista de Vacinações</h3>

    <form method="get" style="display:flex;align-items:center;gap:8px;">
      <input type="hidden" name="p" value="vacinacoes">
      <label for="ordem">Ordenar por data:</label>
      <select id="ordem" name="ordem" class="form-control" onchange="this.form.submit()">
        <option value="desc" <?= $order === 'desc' ? 'selected' : '' ?>>Mais recentes</option>
        <option value="asc" <?= $order === 'asc' ? 'selected' : '' ?>>Mais antigas</option>
      </select>
    </form>
  </div>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Paciente</th>
        <th>Vacina</th>
        <th>Equipe</th>
        <th>Data</th>
        <th>Posto</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($vacinacoes as $v): ?>
        <tr>
          <td><?= $v['id'] ?></td>
          <td><?= htmlspecialchars($v['nome']) ?></td>
          <td><?= htmlspecialchars($v['vacina']) ?></td>
          <td><?= htmlspecialchars($v['equipe']) ?></td>
          <td><?= htmlspecialchars($v['data_aplicacao']) ?></td>
          <td><?= htmlspecialchars($v['posto_saude']) ?></td>
          <td>
            <a class="btn secondary"
               href="index.php?p=vacinacoes&del=<?= $v['id'] ?>&pg=<?= $pagina_atual ?>&ordem=<?= $order ?>"
               onclick="return confirm('Apagar vacinação?');">
               Excluir
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Paginação -->
  <div style="text-align:center;margin-top:16px;">
    <?php if ($paginas > 1): ?>
      <?php for ($i = 1; $i <= $paginas; $i++): ?>
        <?php if ($i == $pagina_atual): ?>
          <span class="btn secondary" style="background:#00a2b8;color:#fff;"><?= $i ?></span>
        <?php else: ?>
          <a class="btn secondary" href="index.php?p=vacinacoes&pg=<?= $i ?>&ordem=<?= $order ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    <?php endif; ?>
  </div>
</section>