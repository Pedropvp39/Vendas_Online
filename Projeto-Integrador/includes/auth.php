<?php
/**
 * Autenticação com banco de dados MySQL.
 * Mantém fallback em sessão para não quebrar o fluxo em ambiente sem banco.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../php/conexao.php';

function seed_users(): void
{
    try {
        $db = db_connect();
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $email = 'demo@techflow.com';
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmtInsert = $db->prepare("INSERT INTO usuarios (nome, email, nascimento, senha_segura, is_admin) VALUES (?, ?, ?, ?, 0)");
            $nome = 'Cliente Demo';
            $nascimento = '1998-05-20';
            $senha = password_hash('techflow123', PASSWORD_DEFAULT);
            $stmtInsert->bind_param('ssss', $nome, $email, $nascimento, $senha);
            $stmtInsert->execute();
        }
    } catch (Throwable $e) {
        error_log('seed_users: ' . $e->getMessage());
    }

        if (!isset($_SESSION['users'])) {
        $_SESSION['users'] = [
            'demo@techflow.com' => [
                'id' => 1,
                'nome' => 'Cliente Demo',
                'email' => 'demo@techflow.com',
                'nascimento' => '1998-05-20',
                'senha' => password_hash('techflow123', PASSWORD_DEFAULT),
                'is_admin' => 0,
                'avatar' => null,
            ],
        ];
    }

    try {
        $db = db_connect();
        $stmtAdmin = $db->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $emailAdmin = 'admin@techflow.com';
        $stmtAdmin->bind_param('s', $emailAdmin);
        $stmtAdmin->execute();
        $resultAdmin = $stmtAdmin->get_result();

        if ($resultAdmin->num_rows === 0) {
            $stmtInsertAdmin = $db->prepare("INSERT INTO usuarios (nome, email, nascimento, senha_segura, is_admin) VALUES (?, ?, ?, ?, 1)");
            $nomeAdmin = 'Administrador';
            $nascimentoAdmin = '1990-01-15';
            $senhaAdmin = password_hash('admin123', PASSWORD_DEFAULT);
            $stmtInsertAdmin->bind_param('ssss', $nomeAdmin, $emailAdmin, $nascimentoAdmin, $senhaAdmin);
            $stmtInsertAdmin->execute();
        }
    } catch (Throwable $e) {
        error_log('seed_users_admin: ' . $e->getMessage());
    }

    $_SESSION['users']['admin@techflow.com'] = [
        'id' => $_SESSION['users']['admin@techflow.com']['id'] ?? 0,
        'nome' => 'Administrador',
        'email' => 'admin@techflow.com',
        'nascimento' => '1990-01-15',
        'senha' => password_hash('admin123', PASSWORD_DEFAULT),
        'is_admin' => 1,
        'avatar' => $_SESSION['users']['admin@techflow.com']['avatar'] ?? null,
    ];
}

function find_user(string $email): ?array
{
    $email = strtolower(trim($email));

    try {
        $db = db_connect();
        seed_users();

        $stmt = $db->prepare("SELECT id, nome, email, nascimento, senha_segura, is_admin, avatar FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            return [
                'id' => (int) $user['id'],
                'nome' => $user['nome'],
                'email' => $user['email'],
                'nascimento' => $user['nascimento'],
                'senha' => $user['senha_segura'],
                'is_admin' => (int) ($user['is_admin'] ?? 0),
                'avatar' => $user['avatar'] ?? null,
            ];
        }
    } catch (Throwable $e) {
        error_log('find_user: ' . $e->getMessage());
    }

    return $_SESSION['users'][$email] ?? null;
}

/**
 * @return array{0:bool,1:string} sucesso e mensagem
 */
function register_user(string $nome, string $email, string $nascimento, string $senha): array
{
    $email = strtolower(trim($email));

    if ($nome === '' || $email === '' || $senha === '') {
        return [false, 'Preencha nome, e-mail e senha.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [false, 'Informe um e-mail válido.'];
    }
    if (strlen($senha) < 8) {
        return [false, 'A senha deve ter no mínimo 8 caracteres.'];
    }

    try {
        $db = db_connect();
        $stmtCheck = $db->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $stmtCheck->bind_param('s', $email);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();
        if ($result->num_rows > 0) {
            return [false, 'Já existe uma conta com este e-mail.'];
        }

        $stmtInsert = $db->prepare("INSERT INTO usuarios (nome, email, nascimento, senha_segura, is_admin) VALUES (?, ?, ?, ?, 0)");
        $nomeTrim = trim($nome);
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmtInsert->bind_param('ssss', $nomeTrim, $email, $nascimento, $hash);
        $stmtInsert->execute();

        return [true, 'Conta criada com sucesso! Você já pode entrar.'];
    } catch (Throwable $e) {
        error_log('register_user: ' . $e->getMessage());
    }

    seed_users();
    if (isset($_SESSION['users'][$email])) {
        return [false, 'Já existe uma conta com este e-mail.'];
    }

    $_SESSION['users'][$email] = [
        'nome' => trim($nome),
        'email' => $email,
        'nascimento' => $nascimento,
        'senha' => password_hash($senha, PASSWORD_DEFAULT),
    ];

    return [true, 'Conta criada com sucesso! Você já pode entrar.'];
}

/**
 * @return array{0:bool,1:string} sucesso e mensagem
 */
function login_user(string $email, string $senha): array
{
    $user = find_user($email);
    if (!$user || !password_verify($senha, $user['senha'])) {
        return [false, 'E-mail ou senha incorretos.'];
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) ($user['id'] ?? 0),
        'nome' => $user['nome'],
        'email' => $user['email'],
        'nascimento' => $user['nascimento'],
        'is_admin' => (int) ($user['is_admin'] ?? 0),
        'avatar' => $user['avatar'] ?? null,
    ];

    return [true, 'Login realizado!'];
}

function update_user(string $emailAtual, array $dados): void
{
    $emailAtual = strtolower(trim($emailAtual));

    try {
        $db = db_connect();
        $setParts = [];
        $types = '';
        $params = [];

        if (!empty($dados['nome'])) {
            $setParts[] = 'nome = ?';
            $types .= 's';
            $params[] = trim($dados['nome']);
        }
        if (!empty($dados['nascimento'])) {
            $setParts[] = 'nascimento = ?';
            $types .= 's';
            $params[] = $dados['nascimento'];
        }
        if (!empty($dados['senha_nova'])) {
            $setParts[] = 'senha_segura = ?';
            $types .= 's';
            $params[] = password_hash($dados['senha_nova'], PASSWORD_DEFAULT);
        }
        if (array_key_exists('avatar', $dados)) {
            if ($dados['avatar'] === null) {
                $setParts[] = 'avatar = NULL';
            } else {
                $setParts[] = 'avatar = ?';
                $types .= 's';
                $params[] = $dados['avatar'];
            }
        }

        if ($setParts === []) {
            return;
        }

        $sql = 'UPDATE usuarios SET ' . implode(', ', $setParts) . ' WHERE email = ?';
        $stmt = $db->prepare($sql);
        $params[] = $emailAtual;
        $types .= 's';
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $user = find_user($emailAtual);
        if ($user) {
            $_SESSION['user'] = [
                'id' => (int) ($user['id'] ?? 0),
                'nome' => $user['nome'],
                'email' => $user['email'],
                'nascimento' => $user['nascimento'],
                'is_admin' => (int) ($user['is_admin'] ?? 0),
                'avatar' => $user['avatar'] ?? null,
            ];
        }
        return;
    } catch (Throwable $e) {
        error_log('update_user: ' . $e->getMessage());
    }

    seed_users();
    if (!isset($_SESSION['users'][$emailAtual])) {
        return;
    }
    $u = &$_SESSION['users'][$emailAtual];
    if (!empty($dados['nome'])) {
        $u['nome'] = trim($dados['nome']);
    }
    if (!empty($dados['nascimento'])) {
        $u['nascimento'] = $dados['nascimento'];
    }
    if (!empty($dados['senha_nova'])) {
        $u['senha'] = password_hash($dados['senha_nova'], PASSWORD_DEFAULT);
    }
    if (array_key_exists('avatar', $dados)) {
        $u['avatar'] = $dados['avatar'];
    }
    $_SESSION['user'] = [
        'id' => (int) ($u['id'] ?? 0),
        'nome' => $u['nome'],
        'email' => $u['email'],
        'nascimento' => $u['nascimento'],
        'is_admin' => (int) ($u['is_admin'] ?? 0),
        'avatar' => $u['avatar'] ?? null,
    ];
}

function delete_user(string $email): void
{
    $email = strtolower(trim($email));

    try {
        $db = db_connect();
        $stmt = $db->prepare('DELETE FROM usuarios WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
    } catch (Throwable $e) {
        error_log('delete_user: ' . $e->getMessage());
    }

    unset($_SESSION['users'][$email]);
    unset($_SESSION['user']);
}

function logout_user(): void
{
    unset($_SESSION['user']);
    session_regenerate_id(true);
}
