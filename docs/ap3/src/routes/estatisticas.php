<?php
/*
  UNIVERSIDADE FEDERAL DE CIÊNCIAS DA SAÚDE DE PORTO ALEGRE
  CURSO DE INFORMÁTICA BIOMÉDICA
  DISCIPLINA DE TÓPICOS ESPECIAIS EM INFORMÁTICA BIOMÉDICA III
  NOMES: CARLISE VR SEBASTIANY E MATHEUS CASTILHOS
*/

require_once __DIR__ . '/../functions.php';

/* === Consulta de dados === */
$por_vacina = $pdo->query("
  SELECT vacina, COUNT(*) AS total 
  FROM vacinacoes 
  GROUP BY vacina 
  ORDER BY total DESC
")->fetchAll();

$por_equipe = $pdo->query("
  SELECT equipe, COUNT(*) AS total 
  FROM vacinacoes 
  GROUP BY equipe 
  ORDER BY total DESC
")->fetchAll();

$por_equipe_vacina = $pdo->query("
  SELECT equipe, vacina, COUNT(*) AS total 
  FROM vacinacoes 
  GROUP BY equipe, vacina
")->fetchAll();

$por_mes = $pdo->query("
  SELECT DATE_FORMAT(data_aplicacao, '%Y-%m') AS mes, COUNT(*) AS total
  FROM vacinacoes
  GROUP BY mes
  ORDER BY mes
")->fetchAll();

/* === Cálculo: pacientes em dia x atrasados === */
$pacientes = $pdo->query("SELECT * FROM pacientes")->fetchAll();
$em_dia = $atrasados = 0;

foreach ($pacientes as $p) {
  $idade = idade_em_meses($p['data_nascimento']);
  $apps  = get_aplicacoes_por_paciente($pdo, (int)$p['id']);
  $faltas = calcular_atrasos_paciente($calendario, $apps, $idade);
  if (empty($faltas)) $em_dia++;
  else $atrasados++;
}
?>

<section class="card">
  <h2>Estatísticas Gerais</h2>
  <p>
    Abaixo estão representações visuais sobre as vacinações cadastradas no sistema, 
    mostrando a proporção de pacientes em dia, a distribuição por tipo, por equipe, 
    e a evolução temporal das aplicações.
  </p>
</section>

<!-- === LINHA 1: Pizza + Barras (Lado a Lado) === -->
<div class="chart-row">
  <section class="card half">
    <h3>Em Dia × Atrasadas</h3>
    <canvas id="chartPizza" height="360"></canvas>
  </section>

  <section class="card half">
    <h3>Contagem por Tipo de Vacina</h3>
    <canvas id="chartVacina" height="360"></canvas>
  </section>
</div>

<!-- === LINHA 2: Barras Simples (Equipes) === -->
<section class="card">
  <h3>Vacinações por Equipe</h3>
  <canvas id="chartEquipe"></canvas>
</section>

<!-- === LINHA 3: Barras Agrupadas (Equipe × Vacina) === -->
<section class="card">
  <h3>Distribuição de Tipos de Vacinas por Equipe</h3>
  <canvas id="chartAgrupado"></canvas>
</section>

<!-- === LINHA 4: Linha Temporal === -->
<section class="card">
  <h3>Vacinações ao Longo do Tempo</h3>
  <canvas id="chartLinha"></canvas>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// === Dados PHP -> JS ===
const labelsVacina = <?= json_encode(array_column($por_vacina, 'vacina')) ?>;
const dataVacina   = <?= json_encode(array_map('intval', array_column($por_vacina, 'total'))) ?>;
const labelsEquipe = <?= json_encode(array_column($por_equipe, 'equipe')) ?>;
const dataEquipe   = <?= json_encode(array_map('intval', array_column($por_equipe, 'total'))) ?>;
const emDia = <?= (int)$em_dia ?>;
const atrasados = <?= (int)$atrasados ?>;
const dadosAgrupados = <?= json_encode($por_equipe_vacina) ?>;
const dadosPorMes = <?= json_encode($por_mes) ?>;

// === GRÁFICO 1: Pizza (Em dia × Atrasadas) ===
new Chart(document.getElementById('chartPizza'), {
  type: 'pie',
  data: {
    labels: ['Em dia', 'Atrasadas'],
    datasets: [{
      data: [emDia, atrasados],
      backgroundColor: ['#42c3a7', '#f26b6b']
    }]
  },
  options: {
    plugins: {
      legend: { position: 'bottom' },
      tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.parsed}` } }
    }
  }
});

// === GRÁFICO 2: Barras (Vacinações por Tipo) ===
new Chart(document.getElementById('chartVacina'), {
  type: 'bar',
  data: {
    labels: labelsVacina,
    datasets: [{
      label: 'Total de Aplicações',
      data: dataVacina,
      backgroundColor: 'rgba(0, 162, 184, 0.6)',
      borderColor: 'rgba(0, 162, 184, 1)',
      borderWidth: 1
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
  }
});

// === GRÁFICO 3: Barras (Vacinações por Equipe) ===
new Chart(document.getElementById('chartEquipe'), {
  type: 'bar',
  data: {
    labels: labelsEquipe,
    datasets: [{
      label: 'Total de Aplicações',
      data: dataEquipe,
      backgroundColor: 'rgba(66, 195, 167, 0.7)',
      borderColor: 'rgba(0, 128, 128, 1)',
      borderWidth: 1
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
  }
});

// === GRÁFICO 4: Barras Agrupadas (Vacina × Equipe) ===
const equipes = [...new Set(dadosAgrupados.map(d => d.equipe))];
const vacinas = [...new Set(dadosAgrupados.map(d => d.vacina))];
const datasetsAgrupado = vacinas.map(vac => ({
  label: vac,
  data: equipes.map(eq => {
    const item = dadosAgrupados.find(d => d.equipe === eq && d.vacina === vac);
    return item ? parseInt(item.total) : 0;
  }),
  backgroundColor: `hsla(${Math.random() * 360}, 60%, 60%, 0.7)`
}));

new Chart(document.getElementById('chartAgrupado'), {
  type: 'bar',
  data: { labels: equipes, datasets: datasetsAgrupado },
  options: {
    scales: { y: { beginAtZero: true } },
    plugins: { legend: { position: 'bottom' } }
  }
});

// === GRÁFICO 5: Linha Temporal ===
new Chart(document.getElementById('chartLinha'), {
  type: 'line',
  data: {
    labels: dadosPorMes.map(d => d.mes),
    datasets: [{
      label: 'Total de Vacinações',
      data: dadosPorMes.map(d => parseInt(d.total)),
      borderColor: 'rgba(0, 162, 184, 1)',
      backgroundColor: 'rgba(0, 162, 184, 0.2)',
      tension: 0.3,
      fill: true
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});
</script>