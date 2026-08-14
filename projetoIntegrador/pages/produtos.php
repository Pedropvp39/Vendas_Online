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
    <title>produtos</title>
</head>
<body class="page-dashboard">
    <div class="container">
        <aside class="sidebar">
            <h2>Menu</h2>
            <ul>
                <li><a href="../index.php">Início</a></li>
                <li><a href="../pages/cadastro.php">Cadastro</a></li>
                <li><a href="../pages/login.php">Login</a></li>
                <li><a href="../pages/dashboard.php">Perfil</a></li>
            </ul>
        </aside>
        <main class="content">
            <h1>Lista de Produtos</h1>
            <p>Confira os produtos disponíveis para o seu setup.</p>
            <?php include('../php/produtos.php');?>
            <img src="../assets/img/gabinete_gamer.anime.lolly.otaku.jpg" alt="Gabinete Gamer">
          <p>Gabinete Gamer com design inspirado em anime, perfeito para montar seu setup com estilo.</p>

            <?php
            // Exibe os produtos
            if ($result_produtos && $result_produtos->num_rows > 0) {
                while ($row = $result_produtos->fetch_assoc()) {
                    $imagem = trim($row['imagem']);
                    $imagemTag = $imagem !== '' ? '<img src="../assets/img/' . htmlspecialchars($imagem) . '" alt="' . htmlspecialchars($row['nome']) . '">' : '';
                    echo '<div class="card">';
                    echo $imagemTag;
                    echo '<h2>' . htmlspecialchars($row['nome']) . '</h2>';
                    echo '<p>' . htmlspecialchars($row['preco']) . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<p class="empty-message">Nenhum produto disponível no momento.</p>';
            }
            ?>
        </main>
    </div>
</body>
</html>