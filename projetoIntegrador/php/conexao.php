<?php
$servidor = "localhost";
$usuario = "root";
$senha = "senac";
$banco = "techflow";

$admin = new mysqli($servidor, $usuario, $senha, null, 3307);
if ($admin->connect_error) {
    die("Erro na conexão com o MySQL: " . $admin->connect_error);
}

$admin->query("CREATE DATABASE IF NOT EXISTS `$banco`");
$admin->close();

$conexao = new mysqli($servidor, $usuario, $senha, $banco, 3307);
if ($conexao->connect_error) {
    die("Erro na conexão com o banco: " . $conexao->connect_error);
}

$conexao->set_charset("utf8");

$conexao->query("CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    nascimento DATE NOT NULL,
    senha_segura VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
?>
