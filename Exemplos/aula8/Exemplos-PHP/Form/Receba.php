<?php
// Captura os dados do formulário
$nome  = $_POST["nome"]  ?? "";
$email = $_POST["email"] ?? "";

// Mensagem simulando inserção no banco
$mensagem = "Recebi os dados seguintes dados: ($nome, $email). Agora estou agora pronto para adicionar no banco de dados :)";

// Criando algo em HTML dinamicamente
$itens = ["Item A", "Item B", "Item C"];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Processando Cadastro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">s

<div class="container mt-5">
    <div class="alert alert-success">
        <h4 class="alert-heading">Processamento Concluído</h4>
        <p><?php echo $mensagem ?></p>
    </div>

    <h5>Exemplo de lista criada em PHP:</h5>
    <ul class="list-group">
        <?php foreach ($itens as $item): ?>
            <li class="list-group-item"><?= $item ?></li>
        <?php endforeach; ?>
    </ul>

    <a href="Form.html" class="btn btn-primary mt-3">Voltar</a>
</div>

</body>
</html>
