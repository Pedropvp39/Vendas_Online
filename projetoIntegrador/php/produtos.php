<?php
include_once('conexao.php');

// Se for requisição POST, salva o produto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $preco = $_POST['preco'] ?? 0;
    $quantidade = $_POST['quantidade'] ?? 0;
    $descricao = $_POST['descricao'] ?? '';
    $imagem = $_POST['imagem'] ?? 'gabinete_gamer.anime.lolly.otaku.jpg';

    if (!empty($nome)) {
        $sql = "INSERT INTO produtos (nome, preco, quantidade, descricao, imagem) VALUES ('$nome', '$preco', '$quantidade', '$descricao', '$imagem')";
        $conexao->query($sql);
        header('Location: ../pages/produtos.php');
        exit();
    }
}

// Busca a lista de produtos
$sql_produtos = "SELECT * FROM produtos";
$result_produtos = $conexao->query($sql_produtos);
?>