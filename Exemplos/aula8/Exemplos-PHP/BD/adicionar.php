<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Usuário</title>
</head>
<body>
    <h1>Adicionar Usuário</h1>
    <form method="POST" action="processa.php">
        <label>Nome:</label><br>
        <input type="text" name="nome" required><br><br>
        
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>
        
        <button type="submit">Salvar</button>
    </form>
</body>
</html>
