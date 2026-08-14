<?php
/**
 * Autenticação de exemplo (mock).
 * Os usuários ficam armazenados na sessão. Um usuário demo é criado
 * automaticamente para facilitar o teste do login.
 */

require_once __DIR__ . '/config.php';

function seed_users(): void
{
    if (!isset($_SESSION['users'])) {
        $_SESSION['users'] = [
            'demo@techflow.com' => [
                'nome' => 'Cliente Demo',
                'email' => 'demo@techflow.com',
                'nascimento' => '1998-05-20',
                'senha' => password_hash('techflow123', PASSWORD_DEFAULT),
            ],
        ];
    }
}

function find_user(string $email): ?array
{
    seed_users();
    return $_SESSION['users'][strtolower(trim($email))] ?? null;
}

/**
 * @return array{0:bool,1:string} sucesso e mensagem
 */
function register_user(string $nome, string $email, string $nascimento, string $senha): array
{
    seed_users();
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

    // Regenera o ID de sessão para evitar fixation
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'nome' => $user['nome'],
        'email' => $user['email'],
        'nascimento' => $user['nascimento'],
    ];

    return [true, 'Login realizado!'];
}

function update_user(string $emailAtual, array $dados): void
{
    seed_users();
    $emailAtual = strtolower(trim($emailAtual));
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
    $_SESSION['user'] = [
        'nome' => $u['nome'],
        'email' => $u['email'],
        'nascimento' => $u['nascimento'],
    ];
}

function delete_user(string $email): void
{
    $email = strtolower(trim($email));
    unset($_SESSION['users'][$email]);
    unset($_SESSION['user']);
}

function logout_user(): void
{
    unset($_SESSION['user']);
    session_regenerate_id(true);
}
