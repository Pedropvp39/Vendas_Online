<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

no_cache();
require_login();

$base = base_url();
$user = current_user();
$aviso = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $aviso = 'Sessão expirada. Tente novamente.';
    } elseif (isset($_POST['excluir'])) {
        delete_user($user['email']);
        header('Location: ' . $base . '/index.php');
        exit();
    } else {
        $novaSenha = $_POST['senha_nova'] ?? '';
        if ($novaSenha !== '' && strlen($novaSenha) < 8) {
            $aviso = 'A nova senha deve ter no mínimo 8 caracteres.';
        } else {
            update_user($user['email'], [
                'nome' => $_POST['nome'] ?? '',
                'nascimento' => $_POST['nascimento'] ?? '',
                'senha_nova' => $novaSenha,
            ]);
            $user = current_user();
            $sucesso = 'Informações atualizadas com sucesso!';
        }
    }
}

$primeiraLetra = function_exists('mb_substr')
    ? mb_substr($user['nome'] ?? 'U', 0, 1)
    : substr($user['nome'] ?? 'U', 0, 1);
$inicial = strtoupper($primeiraLetra);

$page_title = 'Meu perfil';
require __DIR__ . '/../includes/header.php';
?>

<div class="dash">
    <aside class="sidebar">
        <div class="avatar" aria-hidden="true"><?= e($inicial) ?></div>
        <div class="who"><?= e($user['nome']) ?></div>
        <div class="who-mail"><?= e($user['email']) ?></div>
        <nav aria-label="Menu do perfil">
            <ul>
                <li><a class="active" href="<?= e($base) ?>/pages/dashboard.php">Meu perfil</a></li>
                <li><a href="<?= e($base) ?>/pages/produtos.php">Ver produtos</a></li>
                <li><a href="<?= e($base) ?>/index.php">Início</a></li>
                <li><a href="<?= e($base) ?>/php/logout.php">Sair</a></li>
            </ul>
        </nav>
    </aside>

    <section class="panel" aria-labelledby="perfil-title">
        <h1 id="perfil-title">Meu perfil</h1>
        <p class="sub">Gerencie suas informações abaixo.</p>

        <?php if ($aviso): ?>
            <p class="alert alert-error" role="alert"><?= e($aviso) ?></p>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <p class="alert alert-success" role="status"><?= e($sucesso) ?></p>
        <?php endif; ?>

        <form method="post" action="<?= e($base) ?>/pages/dashboard.php">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="form-grid">
                <div class="field">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" required value="<?= e($user['nome']) ?>">
                </div>
                <div class="field">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="<?= e($user['email']) ?>" readonly>
                </div>
                <div class="field">
                    <label for="nascimento">Data de nascimento</label>
                    <input type="date" id="nascimento" name="nascimento" value="<?= e($user['nascimento'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="senha_nova">Nova senha</label>
                    <input type="password" id="senha_nova" name="senha_nova" autocomplete="new-password"
                           placeholder="Deixe em branco para manter" minlength="8">
                </div>
            </div>

            <div class="panel-actions">
                <button type="submit" class="btn">Salvar alterações</button>
                <button type="submit" name="excluir" value="1" class="btn btn-danger"
                        onclick="return confirm('Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.');">
                    Excluir conta
                </button>
            </div>
        </form>
    </section>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
