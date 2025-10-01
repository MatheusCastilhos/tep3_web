<?php
// api_graficos.php — UM arquivo para:
//  (A) Endpoint JSON:   ?format=json [&especialidade=...&mes=YYYY-MM&paciente_id=...]
//  (B) Página de gráficos: sem format=json (HTML que consome o modo JSON acima)

$DB_HOST='127.0.0.1'; $DB_NAME='clinica'; $DB_USER='root'; $DB_PASS='';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",$DB_USER,$DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
} catch(Throwable $e){
  http_response_code(500);
  exit(json_encode(['error'=>'db_connection_failed','detail'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
}

$format = strtolower(trim($_GET['format'] ?? ''));

// ==============================
// (A) MODO JSON (endpoint)
// ==============================
if ($format === 'json') {
  header('Content-Type: application/json; charset=utf-8');

  $especialidade = trim($_GET['especialidade'] ?? '');
  $mes           = trim($_GET['mes'] ?? '');          // YYYY-MM
  $paciente_id   = (int)($_GET['paciente_id'] ?? 0);

  $where  = [];
  $params = [];

  if ($especialidade !== '') { $where[] = "c.especialidade = :esp"; $params[':esp'] = $especialidade; }
  if ($mes !== '')           { $where[] = "DATE_FORMAT(c.data_consulta,'%Y-%m') = :mes"; $params[':mes'] = $mes; }
  if ($paciente_id > 0)      { $where[] = "c.paciente_id = :pid"; $params[':pid'] = $paciente_id; }

  $sql = "SELECT c.id, c.paciente_id, p.nome AS paciente_nome, c.data_consulta, c.especialidade, c.observacoes, c.criado_em
          FROM consultas c
          JOIN pacientes p ON p.id = c.paciente_id";
  if ($where) { $sql .= " WHERE ".implode(' AND ', $where); }
  $sql .= " ORDER BY c.data_consulta DESC, c.id DESC";

  try {
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
      'count'=>count($rows),
      'filters'=>[
        'especialidade'=>$especialidade ?: null,
        'mes'=>$mes ?: null,
        'paciente_id'=>$paciente_id ?: null,
      ],
      'data'=>$rows
    ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  } catch(Throwable $e){
    http_response_code(500);
    echo json_encode(['error'=>'query_failed','detail'=>$e->getMessage()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  }
  exit;
}

// ==============================
// (B) MODO PÁGINA DE GRÁFICOS
// ==============================
?>
<!doctype html>
<html lang="pt-br">
<meta charset="utf-8">
<title>Gráficos — Consultas</title>
<style>
:root{--bg:#fff;--fg:#16181b;--muted:#6b7280;--card:#f8fafc;--bd:#e5e7eb;--primary:#2563eb;}
*{box-sizing:border-box} body{font-family:system-ui,Segoe UI,Arial,sans-serif;background:var(--bg);color:var(--fg);max-width:980px;margin:40px auto;padding:0 16px}
header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
a{color:var(--primary);text-decoration:none} .btn{background:var(--primary);color:#fff;padding:8px 12px;border-radius:10px;text-decoration:none}
.card{background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:14px}
label{display:block;margin:6px 0} input,select{padding:8px;border:1px solid var(--bd);border-radius:8px}
figure{margin:14px 0}
figcaption{font-weight:600;margin-bottom:6px}
</style>

<header>
  <h1 style="margin:0">Gráficos — Consultas</h1>
  <nav>
    <a href="index.php">Menu</a>
    <a class="btn" href="api_graficos.php?format=json">Ver JSON</a>
  </nav>
</header>

<div class="card" style="margin-bottom:12px">
  <form id="filtros" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
    <label>Especialidade
      <input name="especialidade" placeholder="ex.: Cardiologia">
    </label>
    <label>Mês (YYYY-MM)
      <input name="mes" placeholder="2025-10">
    </label>
    <label>Paciente ID
      <input name="paciente_id" type="number" min="1" placeholder="opcional">
    </label>
    <button class="btn" type="submit">Aplicar</button>
    <button class="btn" type="button" id="limpar" style="background:#6b7280">Limpar</button>
  </form>
</div>

<div class="card">
  <figure>
    <figcaption>Consultas por Mês</figcaption>
    <canvas id="chartMes" height="120"></canvas>
  </figure>
  <figure>
    <figcaption>Consultas por Especialidade</figcaption>
    <canvas id="chartEsp" height="120"></canvas>
  </figure>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const form = document.getElementById('filtros');
const btnLimpar = document.getElementById('limpar');
const endpoint = 'api_graficos.php?format=json';

let chartMes, chartEsp;

async function carregar(params = {}) {
  const usp = new URLSearchParams(params);
  const url = usp.toString() ? `${endpoint}&${usp}` : endpoint;
  const res = await fetch(url);
  if (!res.ok) throw new Error('Falha ao carregar JSON');
  const json = await res.json();
  return json.data || [];
}

function groupCount(arr, keyFn){
  const m = new Map();
  for(const it of arr){
    const k = keyFn(it);
    m.set(k, (m.get(k) || 0) + 1);
  }
  return m;
}
function monthKey(isoDate /* YYYY-MM-DD */){
  return (isoDate||'').slice(0,7); // YYYY-MM
}
function renderChartMes(labels, values){
  const ctx = document.getElementById('chartMes');
  if (chartMes) chartMes.destroy();
  chartMes = new Chart(ctx, { type:'bar', data:{ labels, datasets:[{ label:'Consultas', data:values }] }, options:{ responsive:true } });
}
function renderChartEsp(labels, values){
  const ctx = document.getElementById('chartEsp');
  if (chartEsp) chartEsp.destroy();
  chartEsp = new Chart(ctx, { type:'pie', data:{ labels, datasets:[{ label:'Consultas', data:values }] }, options:{ responsive:true } });
}
async function atualizar(params = {}){
  try{
    const data = await carregar(params);

    const gMes = groupCount(data, c => monthKey(c.data_consulta));
    const labelsMes = Array.from(gMes.keys()).sort();
    const valuesMes = labelsMes.map(k => gMes.get(k));
    renderChartMes(labelsMes, valuesMes);

    const gEsp = groupCount(data, c => c.especialidade || '—');
    const labelsEsp = Array.from(gEsp.keys()).sort();
    const valuesEsp = labelsEsp.map(k => gEsp.get(k));
    renderChartEsp(labelsEsp, valuesEsp);
  }catch(err){
    alert(err.message || err);
  }
}

form.addEventListener('submit', ev => {
  ev.preventDefault();
  const fd = new FormData(form);
  const p = {};
  for (const [k,v] of fd.entries()){
    const t = String(v).trim();
    if (t) p[k] = t;
  }
  atualizar(p);
});
btnLimpar.addEventListener('click', () => { form.reset(); atualizar(); });

// inicial
atualizar();
</script>
</html>
