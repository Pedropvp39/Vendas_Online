<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Pagina de cadastro</title>
    
</head>

<body>
    <form method="POST" action="../php/cadastro.php">
        <label>Nome:</label>
        <input type="text" name="nome" placeholder="Digite seu nome">
        <label>E-mail:</label>
        <input type="email" name="email" placeholder="Digite seu email">
        <label>Nascimento:</label>
        <input type="date" name="nascimento">
        <label>Senha:</label>
        <input type="password" name="senha" placeholder="Digite sua senha">
        <button type="submit">Cadastrar</button>
    </form>
</body>

</html>