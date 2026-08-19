<?php
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
}
if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') ?: 'senac');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'techflow');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', (int) (getenv('DB_PORT') ?: 3307));
}

function db_connection_candidates(): array
{
    $hosts = [DB_HOST, 'localhost', '127.0.0.1'];
    $users = [DB_USER, 'root'];
    $passes = [DB_PASS, '', 'senac', 'root'];
    $ports = [DB_PORT, 3307, 3306];

    $seen = [];
    $candidates = [];
    foreach ($hosts as $host) {
        foreach ($users as $user) {
            foreach ($passes as $pass) {
                foreach ($ports as $port) {
                    $key = $host . '|' . $user . '|' . $pass . '|' . $port;
                    if (isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;
                    $candidates[] = [$host, $user, $pass, (int) $port];
                }
            }
        }
    }

    return $candidates;
}

function db_connect(): mysqli
{
    static $conexao = null;
    static $lastConfig = null;

    if ($conexao instanceof mysqli && !$conexao->connect_errno) {
        return $conexao;
    }

    foreach (db_connection_candidates() as [$host, $user, $pass, $port]) {
        $try = @new mysqli($host, $user, $pass, '', $port);
        if ($try->connect_error) {
            $try->close();
            continue;
        }

        $lastConfig = [$host, $user, $pass, $port];
        $conexao = @new mysqli($host, $user, $pass, DB_NAME, $port);
        if ($conexao && !$conexao->connect_error) {
            $conexao->set_charset('utf8mb4');
            return $conexao;
        }
    }

    if ($lastConfig) {
        [$host, $user, $pass, $port] = $lastConfig;
        $conexao = @new mysqli($host, $user, $pass, DB_NAME, $port);
        if ($conexao && !$conexao->connect_error) {
            $conexao->set_charset('utf8mb4');
            return $conexao;
        }
    }

    throw new RuntimeException('Erro na conexão com o MySQL. Verifique se o XAMPP/MySQL está ligado e se a senha do usuário root está correta.');
}

function db_add_column_if_missing(mysqli $conexao, string $table, string $column, string $definition): void
{
    try {
        $check = $conexao->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if ($check && $check->num_rows === 0) {
            $conexao->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    } catch (Throwable $e) {
        error_log("db_add_column_if_missing ($table.$column): " . $e->getMessage());
    }
}

function db_ensure_schema(): void
{
    try {
        $admin = @new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
        if (!$admin || $admin->connect_error) {
            foreach (db_connection_candidates() as [$host, $user, $pass, $port]) {
                $admin = @new mysqli($host, $user, $pass, '', $port);
                if ($admin && !$admin->connect_error) {
                    break;
                }
            }
        }

        if (!$admin || $admin->connect_error) {
            return;
        }

        $dbName = str_replace('`', '``', DB_NAME);
        $admin->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $admin->close();

        $conexao = db_connect();
        $conexao->query("CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            nascimento VARCHAR(50) NOT NULL,
            senha VARCHAR(255) NOT NULL,
            tipo VARCHAR(50) NOT NULL DEFAULT 'cliente',
            is_admin TINYINT(1) NOT NULL DEFAULT 0,
            avatar VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Garante compatibilidade adicionando colunas faltantes com segurança
        db_add_column_if_missing($conexao, 'usuarios', 'tipo', "VARCHAR(50) NOT NULL DEFAULT 'cliente'");
        db_add_column_if_missing($conexao, 'usuarios', 'is_admin', "TINYINT(1) NOT NULL DEFAULT 0");
        db_add_column_if_missing($conexao, 'usuarios', 'avatar', "VARCHAR(255) NULL");
        db_add_column_if_missing($conexao, 'usuarios', 'senha', "VARCHAR(255) NOT NULL DEFAULT ''");

        $conexao->query("CREATE TABLE IF NOT EXISTS categorias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL UNIQUE,
            descricao TEXT NULL,
            icone VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        db_add_column_if_missing($conexao, 'categorias', 'descricao', "TEXT NULL");
        db_add_column_if_missing($conexao, 'categorias', 'icone', "VARCHAR(255) NULL");

        $conexao->query("CREATE TABLE IF NOT EXISTS produtos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(150) NOT NULL,
            categoria VARCHAR(80) NOT NULL,
            preco DECIMAL(10,2) NOT NULL,
            descricao TEXT NOT NULL,
            imagem VARCHAR(255) NOT NULL,
            destaque TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $conexao->query("CREATE TABLE IF NOT EXISTS pedidos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            produto_id INT NOT NULL,
            produto_nome VARCHAR(150) NOT NULL,
            categoria VARCHAR(80) NOT NULL,
            preco DECIMAL(10,2) NOT NULL,
            quantidade INT NOT NULL DEFAULT 1,
            status VARCHAR(30) NOT NULL DEFAULT 'Pago',
            removido TINYINT(1) NOT NULL DEFAULT 0,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    } catch (Throwable $e) {
        error_log('db_ensure_schema: ' . $e->getMessage());
    }
}

db_ensure_schema();

