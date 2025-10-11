<?php
/*
  UNIVERSIDADE FEDERAL DE CIÊNCIAS DA SAÚDE DE PORTO ALEGRE
  CURSO DE INFORMÁTICA BIOMÉDICA
  DISCIPLINA DE TÓPICOS ESPECIAIS EM INFORMÁTICA BIOMÉDICA III
  NOMES: CARLISE SEBASTIANY E MATHEUS CASTILHOS
*/

require_once __DIR__ . '/../functions.php';

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
?>

<section class="card">
  <h2>Estatísticas</h2>
  <p>Visão geral das vacinações agrupadas por tipo de vacina e por equipe responsável.</p>
</section>

<section class="card">
  <h3>Vacinações por Vacina</h3>
  <canvas id="chartVacina" width="900" height="380"></canvas>
</section>

<section class="card">
  <h3>Vacinações por Equipe</h3>
  <canvas id="chartEquipe" width="900" height="380"></canvas>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labelsVacina = <?= json_encode(array_column($por_vacina, 'vacina')) ?>;
const dataVacina   = <?= json_encode(array_map('intval', array_column($por_vacina, 'total'))) ?>;

const labelsEquipe = <?= json_encode(array_column($por_equipe, 'equipe')) ?>;
const dataEquipe   = <?= json_encode(array_map('intval', array_column($por_equipe, 'total'))) ?>;

const chartConfig = (ctx, labels, data) => new Chart(ctx, {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [{
      label: 'Total',
      data: data,
      backgroundColor: 'rgba(0, 162, 184, 0.6)',
      borderColor: 'rgba(0, 162, 184, 1)',
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
  }
});

chartConfig(document.getElementById('chartVacina'), labelsVacina, dataVacina);
chartConfig(document.getElementById('chartEquipe'), labelsEquipe, dataEquipe);
</script>