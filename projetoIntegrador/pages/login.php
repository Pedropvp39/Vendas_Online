<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>login</title>
    
</head>

<body>
    <form method="post" action="../php/login.php">
        <label>E-mail</label>
        <input type="email" name="email"  placeholder="Ex: exemplo@gmail.com">

        <label>Senha:</label>
        <input type="password" name="senha"  placeholder="Senha segura A-Z mínimo 8 caracteres">

        <button type="submit">Entrar</button>
    </form>
   
</body>

</html>