<?php
// index.php — Menu + mini-resumo (PHP puro)
$DB_HOST = '127.0.0.1';
$DB_NAME = 'clinica';
$DB_USER = 'root';
$DB_PASS = '';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
  ]);
} catch(Throwable $e){
  http_response_code(500);
  exit('<pre>Erro ao conectar: '.h($e->getMessage()).'</pre>');
}

$qtPac = (int)$pdo->query("SELECT COUNT(*) FROM pacientes")->fetchColumn();
$qtCon = (int)$pdo->query("SELECT COUNT(*) FROM consultas")->fetchColumn();
?>
<!doctype html>
<html lang="pt-br">
<meta charset="utf-8">
<title>Clínica — Menu</title>
<style>
:root{--bg:#fff;--fg:#16181b;--muted:#6b7280;--card:#f8fafc;--bd:#e5e7eb;--primary:#2563eb;}
*{box-sizing:border-box} body{font-family:system-ui,Segoe UI,Arial,sans-serif;background:var(--bg);color:var(--fg);max-width:980px;margin:40px auto;padding:0 16px}
header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
nav a.btn{display:inline-block;background:var(--primary);color:#fff;padding:8px 12px;border-radius:10px;text-decoration:none;margin-left:8px}
.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.card{background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:14px}
.kpi{font-size:32px;font-weight:800;margin:8px 0}
a.link{color:var(--primary);text-decoration:none}
footer{margin-top:18px;color:var(--muted);font-size:14px}
</style>

<header>
  <h1 style="margin:0">Clínica — Menu</h1>
    <nav>
        <a class="btn" href="pacientes.php">Pacientes</a>
        <a class="btn" href="consultas.php">Consultas</a>
        <a class="btn" href="api_graficos.php">Gráficos</a>
    </nav>
</header>

<section class="grid">
  <div class="card">
    <div>Pacientes cadastrados</div>
    <div class="kpi"><?= $qtPac ?></div>
    <a class="link" href="pacientes.php">Abrir pacientes →</a>
  </div>
  <div class="card">
    <div>Consultas cadastradas</div>
    <div class="kpi"><?= $qtCon ?></div>
    <a class="link" href="consultas.php">Abrir consultas →</a>
  </div>
</section>

<footer>Use os botões acima para navegar.</footer>
</html>