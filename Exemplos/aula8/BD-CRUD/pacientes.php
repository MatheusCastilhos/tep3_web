<?php
// pacientes.php — CRUD simples (um arquivo)
$DB_HOST='127.0.0.1'; $DB_NAME='clinica'; $DB_USER='root'; $DB_PASS='';
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
  ]);
} catch(Throwable $e){ http_response_code(500); exit('Erro: '.h($e->getMessage())); }

$acao = $_GET['acao'] ?? '';
$id   = (int)($_GET['id'] ?? 0);
$msg  = '';

// EXCLUIR
if($acao==='delete' && $id>0){
  if($_SERVER['REQUEST_METHOD']==='POST'){
    if(($_POST['confirm']??'')==='sim'){
      $pdo->prepare("DELETE FROM pacientes WHERE id=?")->execute([$id]);
      header("Location: pacientes.php?msg=".urlencode("Paciente #$id excluído.")); exit;
    }
    header("Location: pacientes.php"); exit;
  }
  $p = $pdo->prepare("SELECT * FROM pacientes WHERE id=?"); $p->execute([$id]); $p=$p->fetch();
  if(!$p){ http_response_code(404); exit('Paciente não encontrado.'); }
  ?>
  <!doctype html><html lang="pt-br"><meta charset="utf-8"><title>Excluir paciente</title>
  <style>
  :root{--primary:#2563eb;--bd:#e5e7eb;--card:#f8fafc;font-family:system-ui}
  body{max-width:720px;margin:40px auto;padding:0 16px;font-family:system-ui}
  a{color:var(--primary);text-decoration:none} .btn{background:var(--primary);color:#fff;padding:8px 12px;border-radius:10px;text-decoration:none}
  .card{background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:14px}
  </style>
  <h1>Excluir paciente</h1>
  <div class="card" style="margin:12px 0">
    Tem certeza que deseja excluir <b><?=h($p['nome'])?></b> (ID <?= (int)$p['id']?>)?
    <form method="post" style="margin-top:10px">
      <button class="btn" name="confirm" value="sim">Sim, excluir</button>
      <a href="pacientes.php" style="margin-left:8px">Cancelar</a>
    </form>
  </div>
  <p><a href="index.php">← Voltar ao menu</a></p>
  </html><?php exit;
}

// EDITAR
if($acao==='edit' && $id>0){
  $st=$pdo->prepare("SELECT * FROM pacientes WHERE id=?"); $st->execute([$id]); $p=$st->fetch();
  if(!$p){ http_response_code(404); exit('Paciente não encontrado.'); }

  if($_SERVER['REQUEST_METHOD']==='POST'){
    $nome=trim($_POST['nome']??'');
    $cpf=preg_replace('/\D+/','',$_POST['cpf']??'');
    $nasc=$_POST['data_nascimento']??null;
    $tel=trim($_POST['telefone']??''); $email=trim($_POST['email']??'');
    if($nome===''||$cpf===''){ $msg='Nome e CPF são obrigatórios.'; }
    else{
      try{
        $up=$pdo->prepare("UPDATE pacientes SET nome=?, cpf=?, data_nascimento=?, telefone=?, email=? WHERE id=?");
        $up->execute([$nome,$cpf,$nasc?:null,$tel?:null,$email?:null,$id]);
        header("Location: pacientes.php?msg=".urlencode("Paciente #$id atualizado!")); exit;
      }catch(PDOException $e){
        $msg = ($e->errorInfo[1]==1062) ? 'CPF já cadastrado em outro paciente.' : 'Erro: '.$e->getMessage();
      }
    }
    $p=['id'=>$id,'nome'=>$nome,'cpf'=>$cpf,'data_nascimento'=>$nasc,'telefone'=>$tel,'email'=>$email,'criado_em'=>$p['criado_em']];
  }
  ?>
  <!doctype html><html lang="pt-br"><meta charset="utf-8"><title>Editar paciente</title>
  <style>
  :root{--primary:#2563eb;--bd:#e5e7eb;--card:#f8fafc;font-family:system-ui}
  body{max-width:720px;margin:40px auto;padding:0 16px;font-family:system-ui}
  input,textarea,select{padding:8px;border:1px solid var(--bd);border-radius:8px;width:100%}
  .row{margin:8px 0} .btn{background:var(--primary);color:#fff;padding:8px 12px;border-radius:10px;text-decoration:none}
  .hint{color:#6b7280;font-size:13px} .msg{color:#b91c1c;margin-bottom:8px}
  </style>
  <h1>Editar paciente #<?= (int)$id ?></h1>
  <?php if($msg): ?><p class="msg"><?=h($msg)?></p><?php endif; ?>
  <form method="post">
    <div class="row"><label>Nome*<br><input name="nome" value="<?=h($p['nome'])?>" required></label></div>
    <div class="row"><label>CPF* (apenas números)<br><input name="cpf" value="<?=h($p['cpf'])?>" required></label></div>
    <div class="row"><label>Data de nascimento<br><input type="date" name="data_nascimento" value="<?=h($p['data_nascimento']??'')?>"></label></div>
    <div class="row"><label>Telefone<br><input name="telefone" value="<?=h($p['telefone']??'')?>"></label></div>
    <div class="row"><label>Email<br><input type="email" name="email" value="<?=h($p['email']??'')?>"></label></div>
    <button class="btn" type="submit">Salvar</button>
    <a href="pacientes.php" style="margin-left:8px">Cancelar</a>
  </form>
  <p class="hint" style="margin-top:8px">Campos com * são obrigatórios.</p>
  <p style="margin-top:12px"><a href="index.php">← Menu</a></p>
  </html><?php exit;
}

// LISTAR + CRIAR (padrão)
if($_SERVER['REQUEST_METHOD']==='POST' && $acao===''){
  $nome=trim($_POST['nome']??''); $cpf=preg_replace('/\D+/','',$_POST['cpf']??'');
  $nasc=$_POST['data_nascimento']??null; $tel=trim($_POST['telefone']??''); $email=trim($_POST['email']??'');
  if($nome===''||$cpf===''){ $msg='Nome e CPF são obrigatórios.'; }
  else{
    try{
      $ins=$pdo->prepare("INSERT INTO pacientes (nome, cpf, data_nascimento, telefone, email) VALUES (?,?,?,?,?)");
      $ins->execute([$nome,$cpf,$nasc?:null,$tel?:null,$email?:null]);
      $msg='Paciente cadastrado!';
    }catch(PDOException $e){
      $msg = ($e->errorInfo[1]==1062) ? 'CPF já cadastrado.' : 'Erro: '.$e->getMessage();
    }
  }
}

$q=trim($_GET['q']??'');
if($q!==''){
  $st=$pdo->prepare("SELECT * FROM pacientes WHERE nome LIKE ? OR cpf LIKE ? ORDER BY id DESC");
  $like="%$q%"; $st->execute([$like,$like]);
}else{
  $st=$pdo->query("SELECT * FROM pacientes ORDER BY id DESC");
}
$rows=$st->fetchAll();
?>
<!doctype html>
<html lang="pt-br">
<meta charset="utf-8">
<title>Pacientes — CRUD</title>
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
  <h1 style="margin:0">Pacientes</h1>
  <nav>
    <a href="index.php">Menu</a>
    <a class="btn" style="margin-left:8px" href="consultas.php">Consultas</a>
  </nav>
</header>

<?php if(($g=$_GET['msg']??'')||$msg): ?>
  <div class="card msg"><?= h($g?:$msg) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:12px">
  <form method="get" style="display:flex;gap:8px;flex-wrap:wrap">
    <input name="q" placeholder="Buscar por nome ou CPF" value="<?=h($q)?>" style="flex:1">
    <button class="btn>">Buscar</button>
    <?php if($q!==''): ?><a href="pacientes.php" style="align-self:center">Limpar</a><?php endif; ?>
  </form>
</div>

<div class="card" style="margin-bottom:12px">
  <h2 style="margin:0 0 8px">Novo paciente</h2>
  <form method="post">
    <div class="row"><label>Nome*<br><input name="nome" required></label></div>
    <div class="row"><label>CPF* (apenas números)<br><input name="cpf" required></label></div>
    <div class="row" style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
      <label>Data de nascimento<br><input type="date" name="data_nascimento"></label>
      <label>Telefone<br><input name="telefone"></label>
      <label>Email<br><input type="email" name="email"></label>
    </div>
    <button class="btn" type="submit">Salvar</button>
  </form>
</div>

<div class="card">
  <h2 style="margin:0 0 8px">Lista de pacientes</h2>
  <table>
    <thead><tr>
      <th>ID</th><th>Nome</th><th>CPF</th><th>Nascimento</th><th>Telefone</th><th>Email</th><th>Criado em</th><th>Ações</th>
    </tr></thead>
    <tbody>
      <?php if(!$rows): ?>
        <tr><td colspan="8">Nenhum paciente.</td></tr>
      <?php else: foreach($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= h($r['nome']) ?></td>
          <td><?= h($r['cpf']) ?></td>
          <td><?= h($r['data_nascimento']??'') ?></td>
          <td><?= h($r['telefone']??'') ?></td>
          <td><?= h($r['email']??'') ?></td>
          <td><?= h($r['criado_em']??'') ?></td>
          <td>
            <a href="pacientes.php?acao=edit&id=<?= (int)$r['id'] ?>">Editar</a> |
            <a href="pacientes.php?acao=delete&id=<?= (int)$r['id'] ?>">Excluir</a>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
</html>