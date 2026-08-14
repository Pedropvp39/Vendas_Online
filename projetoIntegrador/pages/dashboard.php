
<?php
session_start();

// 1. Impede o navegador de salvar o cache da página
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// 2. Se a sessão não existir, obriga a ir para a tela de login
if (!isset($_SESSION['email'])) {
    header('Location: ../pages/login.php');
    exit();
}

include_once('../php/produtos.php');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Painel de Usuario</title>
</head>

<body class="page-dashboard">
    <div class="container">
     
        <asside class="sidebar">
            <h2>Menu</h2>
            <ul>
                <li><a href="../pages/dashboard.php">Meu perfil</a></li>
                <li><a href="#">Meus Favoritos</a></li>
                <li><a href="#">Minhas Compras</a></li>
                <li><a href="../pages/produtos.php">ver Produtos</a></li>
                <li><a href="../php/logout.php">Sair</a>
                </li>
            </ul>
        </asside>
        <main class="content">
            <h1> Meu Perfil</h1>
            <p>gerencie suas informações abaixo:</p>
            <form action="../php/dashboard.php" method="post">
                <label>Nome:</label>
                <input type="text" name="nome" required>
                <label>E-mail</label>
                <input type="email" name="email" required>
                <label>Data de Nascimento:</label>
                <input type="date" name="nascimento">
           <label>Nova Senha:</label>
                <input type="password" name="senha_nova">
                <button type="submit">Salvar alterações</button>
                <button type="submit" name="excluir" value="1" class="btn-danger">Excluir Conta</button>
            </form>
        </main>
    </div>
</body>

</html>