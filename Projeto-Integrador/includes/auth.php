<?php
/**
 * Autenticação com banco de dados MySQL.
 * Mantém fallback em sessão para não quebrar o fluxo em ambiente sem banco.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../php/conexao.php';

function validar_data_nascimento(?string $data): array
{
    $data = trim((string) $data);
    if ($data === '') {
        return [false, 'Informe sua data de nascimento.'];
    }

    $dt = DateTimeImmutable::createFromFormat('Y-m-d', $data);
    $errors = DateTimeImmutable::getLastErrors();
    if (!$dt || ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
        return [false, 'Data de nascimento inválida. Formato esperado: dia/mês/ano.'];
    }

    $hoje = new DateTimeImmutable('today');
    if ($dt > $hoje) {
        return [false, 'A data de nascimento não pode ser superior à data atual.'];
    }

    $diff = $hoje->diff($dt);
    $idade = (int) $diff->y;

    if ($idade < 16) {
        return [false, 'É necessário ter no mínimo 16 anos completos para continuar (em relação à data atual).'];
    }

    if ($idade > 120) {
        return [false, 'Informe uma data de nascimento válida.'];
    }

    return [true, ''];
}

function validar_senha(?string $senha, bool $obrigatorio = true): array
{
    $senha = (string) $senha;
    if ($senha === '') {
        if ($obrigatorio) {
            return [false, 'Informe a senha.'];
        }
        return [true, ''];
    }

    if (mb_strlen($senha, 'UTF-8') !== 8) {
        return [false, 'A senha deve ter exatamente 8 dígitos/caracteres (mínimo 8 e máximo 8).'];
    }

    return [true, ''];
}

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
            $stmtInsert = $db->prepare("INSERT INTO usuarios (nome, email, nascimento, senha, tipo, is_admin) VALUES (?, ?, ?, ?, 'cliente', 0)");
            $nome = 'Cliente Demo';
            $nascimento = '1998-05-20';
            $senha = password_hash('tech1234', PASSWORD_DEFAULT);
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
                'senha' => password_hash('tech1234', PASSWORD_DEFAULT),
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
            $stmtInsertAdmin = $db->prepare("INSERT INTO usuarios (nome, email, nascimento, senha, tipo, is_admin) VALUES (?, ?, ?, ?, 'admin', 1)");
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

        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $isAdmin = (!empty($user['is_admin']) || (isset($user['tipo']) && strtolower((string) $user['tipo']) === 'admin')) ? 1 : 0;
            $senha = $user['senha'] ?? $user['senha_segura'] ?? '';

            return [
                'id' => (int) $user['id'],
                'nome' => $user['nome'],
                'email' => $user['email'],
                'nascimento' => $user['nascimento'],
                'senha' => $senha,
                'is_admin' => $isAdmin,
                'tipo' => $user['tipo'] ?? ($isAdmin ? 'admin' : 'cliente'),
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

    if (trim($nome) === '' || $email === '' || trim($nascimento) === '' || $senha === '') {
        return [false, 'Preencha nome, e-mail, data de nascimento e senha.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [false, 'Informe um e-mail válido.'];
    }

    [$validaNasc, $msgNasc] = validar_data_nascimento($nascimento);
    if (!$validaNasc) {
        return [false, $msgNasc];
    }

    [$validaSenha, $msgSenha] = validar_senha($senha, true);
    if (!$validaSenha) {
        return [false, $msgSenha];
    }

    try {
        $db = db_connect();
        seed_users();

        $stmtCheck = $db->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $stmtCheck->bind_param('s', $email);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();
        if ($result->num_rows > 0) {
            return [false, 'Já existe uma conta com este e-mail.'];
        }

        $stmtInsert = $db->prepare("INSERT INTO usuarios (nome, email, nascimento, senha, tipo, is_admin) VALUES (?, ?, ?, ?, 'cliente', 0)");
        $nomeTrim = trim($nome);
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmtInsert->bind_param('ssss', $nomeTrim, $email, $nascimento, $hash);
        $stmtInsert->execute();

        return [true, 'Conta criada com sucesso! Você já pode entrar.'];
    } catch (Throwable $e) {
        error_log('register_user: ' . $e->getMessage());
        return [false, 'Erro ao cadastrar usuário no banco de dados.'];
    }
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

    if (!headers_sent()) {
        session_regenerate_id(true);
    }
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
            [$validaNasc, $msgNasc] = validar_data_nascimento($dados['nascimento']);
            if ($validaNasc) {
                $setParts[] = 'nascimento = ?';
                $types .= 's';
                $params[] = $dados['nascimento'];
            }
        }
        if (!empty($dados['senha_nova'])) {
            [$validaSenha, $msgSenha] = validar_senha($dados['senha_nova'], true);
            if ($validaSenha) {
                $setParts[] = 'senha = ?';
                $types .= 's';
                $params[] = password_hash($dados['senha_nova'], PASSWORD_DEFAULT);
            }
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
    if (!headers_sent()) {
        session_regenerate_id(true);
    }
}

function get_todos_usuarios(): array
{
    try {
        $db = db_connect();
        $res = $db->query('SELECT id, nome, email, nascimento, tipo, is_admin, avatar, created_at FROM usuarios ORDER BY id ASC');
        $users = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $isAdmin = (!empty($row['is_admin']) || strtolower((string) ($row['tipo'] ?? '')) === 'admin') ? 1 : 0;
                $users[] = [
                    'id' => (int) $row['id'],
                    'nome' => (string) $row['nome'],
                    'email' => (string) $row['email'],
                    'nascimento' => (string) $row['nascimento'],
                    'tipo' => (string) ($row['tipo'] ?? ($isAdmin ? 'admin' : 'cliente')),
                    'is_admin' => $isAdmin,
                    'avatar' => $row['avatar'] ?? null,
                    'created_at' => (string) ($row['created_at'] ?? ''),
                ];
            }
        }
        return $users;
    } catch (Throwable $e) {
        error_log('get_todos_usuarios: ' . $e->getMessage());
        return [];
    }
}

function admin_criar_usuario(array $dados): array
{
    $nome = trim((string) ($dados['nome'] ?? ''));
    $email = strtolower(trim((string) ($dados['email'] ?? '')));
    $nascimento = trim((string) ($dados['nascimento'] ?? ''));
    $senha = (string) ($dados['senha'] ?? '');
    $isAdmin = !empty($dados['is_admin']) ? 1 : 0;
    $tipo = $isAdmin ? 'admin' : 'cliente';

    if ($nome === '' || $email === '' || $nascimento === '' || $senha === '') {
        return ['ok' => false, 'mensagem' => 'Preencha nome, e-mail, data de nascimento e senha.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'mensagem' => 'Informe um e-mail válido.'];
    }

    [$validaNasc, $msgNasc] = validar_data_nascimento($nascimento);
    if (!$validaNasc) {
        return ['ok' => false, 'mensagem' => $msgNasc];
    }

    [$validaSenha, $msgSenha] = validar_senha($senha, true);
    if (!$validaSenha) {
        return ['ok' => false, 'mensagem' => $msgSenha];
    }

    try {
        $db = db_connect();
        $stmtCheck = $db->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
        $stmtCheck->bind_param('s', $email);
        $stmtCheck->execute();
        if ($stmtCheck->get_result()->num_rows > 0) {
            return ['ok' => false, 'mensagem' => 'Já existe uma conta cadastrada com este e-mail.'];
        }

        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO usuarios (nome, email, nascimento, senha, tipo, is_admin) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssi', $nome, $email, $nascimento, $hash, $tipo, $isAdmin);
        $stmt->execute();

        return ['ok' => true, 'mensagem' => 'Usuário (' . ($isAdmin ? 'Administrador' : 'Cliente') . ') cadastrado com sucesso no MySQL!'];
    } catch (Throwable $e) {
        error_log('admin_criar_usuario: ' . $e->getMessage());
        return ['ok' => false, 'mensagem' => 'Erro ao criar usuário no banco de dados.'];
    }
}

function admin_atualizar_usuario(int $id, array $dados): array
{
    if ($id <= 0) {
        return ['ok' => false, 'mensagem' => 'ID de usuário inválido.'];
    }

    try {
        $db = db_connect();
        $nome = trim((string) ($dados['nome'] ?? ''));
        $email = strtolower(trim((string) ($dados['email'] ?? '')));
        $nascimento = trim((string) ($dados['nascimento'] ?? ''));
        $isAdmin = !empty($dados['is_admin']) ? 1 : 0;
        $tipo = $isAdmin ? 'admin' : 'cliente';

        if ($nome === '' || $email === '') {
            return ['ok' => false, 'mensagem' => 'Nome e e-mail são obrigatórios.'];
        }

        $setParts = ['nome = ?', 'email = ?', 'nascimento = ?', 'tipo = ?', 'is_admin = ?'];
        $types = 'ssssi';
        $params = [$nome, $email, $nascimento, $tipo, $isAdmin];

        if (!empty($dados['senha_nova'])) {
            [$okSenha, $msgSenha] = validar_senha($dados['senha_nova'], true);
            if (!$okSenha) {
                return ['ok' => false, 'mensagem' => $msgSenha];
            }
            $setParts[] = 'senha = ?';
            $types .= 's';
            $params[] = password_hash($dados['senha_nova'], PASSWORD_DEFAULT);
        }

        $params[] = $id;
        $types .= 'i';

        $sql = 'UPDATE usuarios SET ' . implode(', ', $setParts) . ' WHERE id = ?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return ['ok' => true, 'mensagem' => 'Usuário atualizado com sucesso!'];
    } catch (Throwable $e) {
        error_log('admin_atualizar_usuario: ' . $e->getMessage());
        return ['ok' => false, 'mensagem' => 'Erro ao atualizar usuário no banco de dados.'];
    }
}

function admin_excluir_usuario(int $id): array
{
    if ($id <= 0) {
        return ['ok' => false, 'mensagem' => 'ID de usuário inválido.'];
    }

    try {
        $db = db_connect();
        $stmtPed = $db->prepare('DELETE FROM pedidos WHERE usuario_id = ?');
        $stmtPed->bind_param('i', $id);
        $stmtPed->execute();

        $stmt = $db->prepare('DELETE FROM usuarios WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return ['ok' => true, 'mensagem' => 'Usuário e seus dados foram excluídos com sucesso!'];
    } catch (Throwable $e) {
        error_log('admin_excluir_usuario: ' . $e->getMessage());
        return ['ok' => false, 'mensagem' => 'Erro ao excluir usuário no banco de dados.'];
    }
}
