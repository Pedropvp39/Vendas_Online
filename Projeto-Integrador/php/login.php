<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$base = base_url();
$erro = '';

// Já logado? Vai para o painel.
if (current_user()) {
    header('Location: ' . $base . '/pages/dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $erro = 'Sessão expirada. Tente novamente.';
    } else {
        [$ok, $msg] = login_user($_POST['email'] ?? '', $_POST['senha'] ?? '');
        if ($ok) {
            header('Location: ' . $base . '/pages/dashboard.php');
            exit();
        }
        $erro = $msg;
    }
}

$page_title = 'Entrar';
require __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrap">
    <div class="form-card">
        <h1>Entrar</h1>
        <p class="sub">Acesse sua conta para gerenciar seu perfil.</p>

        <div class="hint">
            Conta de teste: <code>demo@techflow.com</code> / senha <code>techflow123</code>
        </div>

        <?php if ($erro): ?>
            <p class="alert alert-error" role="alert"><?= e($erro) ?></p>
        <?php endif; ?>

        <form method="post" action="<?= e($base) ?>/pages/login.php" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="field">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required autocomplete="email"
                       placeholder="exemplo@gmail.com" value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required autocomplete="current-password"
                       placeholder="Mínimo 8 caracteres">
            </div>
            <button type="submit" class="btn">Entrar</button>
        </form>

        <p class="form-foot">Não tem conta? <a href="<?= e($base) ?>/pages/cadastro.php">Criar agora</a></p>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
