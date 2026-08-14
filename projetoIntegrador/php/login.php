<?php
session_start();
include_once('conexao.php');

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

if (!empty($email) && !empty($senha)) {
    $sql = "SELECT * FROM usuarios WHERE email = '$email' AND senha = '$senha'";
    $resultado = $conexao->query($sql);

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['nome'] = $usuario['nome'];
        
        header('Location: ../pages/dashboard.php');
        exit();
    } else {
        echo "E-mail ou senha incorretos!";
    }
} else {
    echo "Preencha todos os campos!";
}
?>