<?php
// consultas.php — CRUD simples (um arquivo)
$DB_HOST='127.0.0.1'; $DB_NAME='clinica'; $DB_USER='root'; $DB_PASS='';
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

try {
  $pdo=new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",$DB_USER,$DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
} catch(Throwable $e){ http_response_code(500); exit('Erro: '.h($e->getMessage())); }

$acao=$_GET['acao']??''; $id=(int)($_GET['id']??0); $msg='';
$pacientes=$pdo->query("SELECT id,nome FROM pacientes ORDER BY nome")->fetchAll(PDO::FETCH_KEY_PAIR);

// EXCLUIR
if($acao==='delete' && $id>0){
  if($_SERVER['REQUEST_METHOD']==='POST'){
    if(($_POST['confirm']??'')==='sim'){
      $pdo->prepare("DELETE FROM consultas WHERE id=?")->execute([$id]);
      header("Location: consultas.php?msg=".urlencode("Consulta #$id excluída.")); exit;
    }
    header("Location: consultas.php"); exit;
  }
  $s=$pdo->prepare("SELECT c.*,p.nome AS paciente_nome FROM consultas c JOIN pacientes p ON p.id=c.paciente_id WHERE c.id=?");
  $s->execute([$id]); $c=$s->fetch();
  if(!$c){ http_response_code(404); exit('Consulta não encontrada.'); }
  ?>
  <!doctype html><html lang="pt-br"><meta charset="utf-8"><title>Excluir consulta</title>
  <style>
  :root{--primary:#2563eb;--bd:#e5e7eb;--card:#f8fafc;font-family:system-ui}
  body{max-width:720px;margin:40px auto;padding:0 16px;font-family:system-ui}
  a{color:var(--primary);text-decoration:none} .btn{background:var(--primary);color:#fff;padding:8px 12px;border-radius:10px;text-decoration:none}
  .card{background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:14px}
  </style>
  <h1>Excluir consulta</h1>
  <div class="card" style="margin:12px 0">
    Excluir a consulta #<?= (int)$c['id']?> de <b><?=h($c['paciente_nome'])?></b> em <b><?=h($c['data_consulta'])?></b> (<?=h($c['especialidade'])?>)?
    <form method="post" style="margin-top:10px">
      <button class="btn" name="confirm" value="sim">Sim, excluir</button>
      <a href="consultas.php" style="margin-left:8px">Cancelar</a>
    </form>
  </div>
  <p><a href="index.php">← Menu</a></p>
  </html><?php exit;
}

// EDITAR
if($acao==='edit' && $id>0){
  $s=$pdo->prepare("SELECT * FROM consultas WHERE id=?"); $s->execute([$id]); $c=$s->fetch();
  if(!$c){ http_response_code(404); exit('Consulta não encontrada.'); }
  if($_SERVER['REQUEST_METHOD']==='POST'){
    $pid=(int)($_POST['paciente_id']??0);
    $data=$_POST['data_consulta']??'';
    $esp=trim($_POST['especialidade']??'');
    $obs=trim($_POST['observacoes']??'');
    if($pid<=0||$data===''||$esp===''){ $msg='Paciente, Data e Especialidade são obrigatórios.'; }
    else{
      $up=$pdo->prepare("UPDATE consultas SET paciente_id=?, data_consulta=?, especialidade=?, observacoes=? WHERE id=?");
      $up->execute([$pid,$data,$esp,$obs?:null,$id]);
      header("Location: consultas.php?msg=".urlencode("Consulta #$id atualizada!")); exit;
    }
    $c=['id'=>$id,'paciente_id'=>$pid,'data_consulta'=>$data,'especialidade'=>$esp,'observacoes'=>$obs,'criado_em'=>$c['criado_em']];
  }
  ?>
  <!doctype html><html lang="pt-br"><meta charset="utf-8"><title>Editar consulta</title>
  <style>
  :root{--primary:#2563eb;--bd:#e5e7eb;--card:#f8fafc;font-family:system-ui}
  body{max-width:720px;margin:40px auto;padding:0 16px;font-family:system-ui}
  input,textarea,select{padding:8px;border:1px solid var(--bd);border-radius:8px;width:100%}
  .row{margin:8px 0} .btn{background:var(--primary);color:#fff;padding:8px 12px;border-radius:10px;text-decoration:none}
  .msg{color:#b91c1c;margin-bottom:8px}
  </style>
  <h1>Editar consulta #<?= (int)$id ?></h1>
  <?php if($msg): ?><p class="msg"><?=h($msg)?></p><?php endif; ?>
  <form method="post">
    <div class="row"><label>Paciente*<br>
      <select name="paciente_id" required>
        <option value="">Selecione…</option>
        <?php foreach($pacientes as $pid=>$pn): ?>
          <option value="<?=$pid?>" <?= $pid==($c['paciente_id']??0)?'selected':'' ?>><?=h($pn)?></option>
        <?php endforeach; ?>
      </select></label>
    </div>
    <div class="row"><label>Data*<br><input type="date" name="data_consulta" value="<?=h($c['data_consulta']??'')?>" required></label></div>
    <div class="row"><label>Especialidade*<br><input name="especialidade" value="<?=h($c['especialidade']??'')?>" required></label></div>
    <div class="row"><label>Observações<br><textarea name="observacoes" rows="3"><?=h($c['observacoes']??'')?></textarea></label></div>
    <button class="btn" type="submit">Salvar</button>
    <a href="consultas.php" style="margin-left:8px">Cancelar</a>
  </form>
  <p style="margin-top:12px"><a href="index.php">← Menu</a></p>
  </html><?php exit;
}

// LISTAR + CRIAR
if($_SERVER['REQUEST_METHOD']==='POST' && $acao===''){
  $pid=(int)($_POST['paciente_id']??0);
  $data=$_POST['data_consulta']??'';
  $esp=trim($_POST['especialidade']??'');
  $obs=trim($_POST['observacoes']??'');
  if($pid<=0||$data===''||$esp===''){ $msg='Paciente, Data e Especialidade são obrigatórios.'; }
  else{
    $ins=$pdo->prepare("INSERT INTO consultas (paciente_id, data_consulta, especialidade, observacoes) VALUES (?,?,?,?)");
    $ins->execute([$pid,$data,$esp,$obs?:null]);
    $msg='Consulta cadastrada!';
  }
}

$fesp=trim($_GET['especialidade']??'');
if($fesp!==''){
  $st=$pdo->prepare("SELECT c.*,p.nome AS paciente_nome FROM consultas c JOIN pacientes p ON p.id=c.paciente_id WHERE c.especialidade LIKE ? ORDER BY c.data_consulta DESC, c.id DESC");
  $like="%$fesp%"; $st->execute([$like]);
}else{
  $st=$pdo->query("SELECT c.*,p.nome AS paciente_nome FROM consultas c JOIN pacientes p ON p.id=c.paciente_id ORDER BY c.data_consulta DESC, c.id DESC");
}
$rows=$st->fetchAll();
?>
<!doctype html>
<html lang="pt-br">
<meta charset="utf-8">
<title>Consultas — CRUD</title>
<style>
:root{--bg:#fff;--fg:#16181b;--muted:#6b7280;--card:#f8fafc;--bd:#e5e7eb;--primary:#2563eb;}
*{box-sizing:border-box} body{font-family:system-ui,Segoe UI,Arial,sans-serif;background:var(--bg);color:var(--fg);max-width:980px;margin:40px auto;padding:0 16px}
header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
a{color:var(--primary);text-decoration:none} .btn{background:var(--primary);color:#fff;padding:8px 12px;border-radius:10px;text-decoration:none}
.card{background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:14px}
input,textarea,select{padding:8px;border:1px solid var(--bd);border-radius:8px;width:100%}
.row{margin:8px 0}
table{border-collapse:collapse;width:100%;margin-top:10px} th,td{border:1px solid var(--bd);padding:8px;text-align:left} th{background:#eef2ff}
.msg{margin:8px 0;color:#065f46}
</style>

<header>
  <h1 style="margin:0">Consultas</h1>
  <nav>
    <a href="index.php">Menu</a>
    <a class="btn" style="margin-left:8px" href="pacientes.php">Pacientes</a>
  </nav>
</header>

<?php if(($g=$_GET['msg']??'')||$msg): ?>
  <div class="card msg"><?= h($g?:$msg) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:12px">
  <form method="get" style="display:flex;gap:8px;flex-wrap:wrap">
    <input name="especialidade" placeholder="Filtrar por especialidade" value="<?=h($fesp)?>" style="flex:1">
    <button class="btn">Filtrar</button>
    <?php if($fesp!==''): ?><a href="consultas.php" style="align-self:center">Limpar</a><?php endif; ?>
  </form>
</div>

<div class="card" style="margin-bottom:12px">
  <h2 style="margin:0 0 8px">Nova consulta</h2>
  <form method="post">
    <div class="row" style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:8px">
      <label>Paciente*<br>
        <select name="paciente_id" required>
          <option value="">Selecione…</option>
          <?php foreach($pacientes as $pid=>$pn){ echo '<option value="'.(int)$pid.'">'.h($pn).'</option>'; } ?>
        </select>
      </label>
      <label>Data*<br><input type="date" name="data_consulta" required></label>
      <label>Especialidade*<br><input name="especialidade" required></label>
    </div>
    <div class="row"><label>Observações<br><textarea name="observacoes" rows="3"></textarea></label></div>
    <button class="btn" type="submit">Salvar</button>
  </form>
</div>

<div class="card">
  <h2 style="margin:0 0 8px">Lista de consultas</h2>
  <table>
    <thead><tr>
      <th>ID</th><th>Paciente</th><th>Data</th><th>Especialidade</th><th>Observações</th><th>Criado em</th><th>Ações</th>
    </tr></thead>
    <tbody>
      <?php if(!$rows): ?>
        <tr><td colspan="7">Nenhuma consulta.</td></tr>
      <?php else: foreach($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= h($r['paciente_nome']) ?></td>
          <td><?= h($r['data_consulta']) ?></td>
          <td><?= h($r['especialidade']) ?></td>
          <td><?= h($r['observacoes']??'') ?></td>
          <td><?= h($r['criado_em']??'') ?></td>
          <td>
            <a href="consultas.php?acao=edit&id=<?= (int)$r['id'] ?>">Editar</a> |
            <a href="consultas.php?acao=delete&id=<?= (int)$r['id'] ?>">Excluir</a>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
</html>