<?php
/*
  UNIVERSIDADE FEDERAL DE CIÊNCIAS DA SAÚDE DE PORTO ALEGRE
  CURSO DE INFORMÁTICA BIOMÉDICA
  DISCIPLINA DE TÓPICOS ESPECIAIS EM INFORMÁTICA BIOMÉDICA III
  NOMES: CARLISE VR SEBASTIANY E MATHEUS CASTILHOS
*/

require_once __DIR__ . '/../functions.php';

// Verifica se o calendário foi carregado
if (!isset($calendario) || !is_array($calendario)) {
  die("<section class='card'>Calendário vacinal não encontrado. Verifique config/calendario_vacinal.php.</section>");
}

$pacientes = $pdo->query("SELECT * FROM pacientes ORDER BY nome")->fetchAll();
$filtro = $_GET['filtro'] ?? 'todos';
?>

<section class="card">
  <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
    <div>
      <h2>Pessoas com vacinas em atraso</h2>
      <p style="margin-top:6px;">
        Cálculo baseado nas idades previstas (em meses) no calendário vacinal oficial.
      </p>
    </div>
    <form method="get">
      <input type="hidden" name="p" value="atrasos">
      <label for="filtro"><b>Mostrar:</b></label>
      <select id="filtro" name="filtro" onchange="this.form.submit()">
        <option value="todos"  <?= $filtro === 'todos' ? 'selected' : '' ?>>Todos</option>
        <option value="emdia"  <?= $filtro === 'emdia' ? 'selected' : '' ?>>Apenas em dia</option>
        <option value="atraso" <?= $filtro === 'atraso' ? 'selected' : '' ?>>Apenas com atraso</option>
      </select>
    </form>
  </div>
</section>

<section class="card">
  <table>
    <thead>
      <tr>
        <th>Paciente</th>
        <th>Idade (meses)</th>
        <th>Status</th>
        <th>Faltas</th>
      </tr>
    </thead>
    <tbody>
    <?php
    foreach ($pacientes as $p):
      $idade = function_exists('idadeEmMeses')
        ? idadeEmMeses($p['data_nascimento'])
        : idade_em_meses($p['data_nascimento']);

      $apps   = get_aplicacoes_por_paciente($pdo, (int)$p['id']);
      $faltas = calcular_atrasos_paciente($calendario, $apps, $idade);
      $ok     = empty($faltas);

      // Aplica o filtro
      if ($filtro === 'emdia' && !$ok) continue;
      if ($filtro === 'atraso' && $ok) continue;
    ?>
      <tr>
        <td><?= htmlspecialchars($p['nome']) ?></td>
        <td><?= $idade ?></td>
        <td style="font-weight:600; color:<?= $ok ? '#107d47' : '#c93c3c' ?>;">
          <?= $ok ? '✅ Em dia' : '⚠️ Com atraso' ?>
        </td>
        <td>
          <?php if ($ok): ?>
            —
          <?php else: foreach ($faltas as $vac => $info): ?>
            <div>
              <b><?= htmlspecialchars($vac) ?>:</b>
              faltam <?= $info['faltam'] ?>
              (feitas <?= $info['feitas'] ?>/deveriam <?= $info['deveriam'] ?>)
            </div>
          <?php endforeach; endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

</section>