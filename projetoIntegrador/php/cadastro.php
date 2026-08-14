<?php
include_once('conexao.php');

$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';
$nascimento = $_POST['nascimento'] ?? '';
$tipo = $_POST['tipo'] ?? 'cliente';

if (!empty($nome) && !empty($email) && !empty($senha)) {
    // Mantém o uso do mysqli compatível com o seu projeto
    $sql = "INSERT INTO usuarios (nome, email, senha, nascimento, tipo) VALUES ('$nome', '$email', '$senha', '$nascimento', '$tipo')";

    if ($conexao->query($sql) === TRUE) {
        header('Location: ../pages/login.php');
        exit();
    } else {
        echo "Erro ao cadastrar: " . $conexao->error;
    }
} else {
    echo "Preencha todos os campos!";
}
?>