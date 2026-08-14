<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../pages/login.php");
    exit();
}

include('conexao.php');

$id = $_SESSION['id_usuario'];
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$nascimento = trim($_POST['nascimento'] ?? '');
$senha_nova = trim($_POST['senha_nova'] ?? '');

if (!empty($nome) && !empty($email)) {
    $sql_update = "UPDATE usuarios SET nome = '$nome', email = '$email', nascimento = '$nascimento' WHERE id = '$id'";
    $conexao->query($sql_update);
}

if (!empty($senha_nova)) {
    $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
    $sql_senha = "UPDATE usuarios SET senha_segura = '$senha_hash' WHERE id = '$id'";
    $conexao->query($sql_senha);
}

if (isset($_POST['excluir'])) {
    $sql_delete = "DELETE FROM usuarios WHERE id = '$id'";
    $conexao->query($sql_delete);
    session_destroy();
    header("Location: ../index.php");
    exit();
}

header("Location: ../pages/dashboard.php");
exit();
?>
