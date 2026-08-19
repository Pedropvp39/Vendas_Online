<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$base = base_url();
$erro = '';
$sucesso = '';

if (current_user()) {
    header('Location: ' . $base . '/pages/dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $erro = 'Sessão expirada. Tente novamente.';
    } else {
        [$ok, $msg] = register_user(
            $_POST['nome'] ?? '',
            $_POST['email'] ?? '',
            $_POST['nascimento'] ?? '',
            $_POST['senha'] ?? ''
        );
        if ($ok) {
            $sucesso = $msg;
            $_POST = [];
        } else {
            $erro = $msg;
        }
    }
}

$page_title = 'Criar conta';
require __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrap">
    <div class="form-card">
        <h1>Criar conta</h1>
        <p class="sub">Cadastre-se para acompanhar pedidos e favoritos.</p>

        <?php if ($erro): ?>
            <p class="alert alert-error" role="alert"><?= e($erro) ?></p>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <p class="alert alert-success" role="status">
                <?= e($sucesso) ?> <a href="<?= e($base) ?>/pages/login.php">Entrar</a>
            </p>
        <?php endif; ?>

        <form method="post" action="<?= e($base) ?>/pages/cadastro.php" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="field">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" required autocomplete="name"
                       placeholder="Digite seu nome" value="<?= e($_POST['nome'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required autocomplete="email"
                       placeholder="Digite seu e-mail" value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="nascimento">Data de nascimento</label>
                <input type="date" id="nascimento" name="nascimento" value="<?= e($_POST['nascimento'] ?? '') ?>">
            </div>
            <div class="field field-password">
                <label for="senha">Senha</label>
                <div class="password-wrapper">
                    <input type="password" id="senha" name="senha" required autocomplete="new-password"
                           placeholder="Mínimo 8 caracteres" minlength="8">
                    <button type="button" class="toggle-password" data-target="senha" aria-label="Mostrar/esconder senha">👁️</button>
                </div>
            </div>
            <button type="submit" class="btn">Cadastrar</button>
        </form>

        <p class="form-foot">Já tem conta? <a href="<?= e($base) ?>/pages/login.php">Entrar</a></p>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>