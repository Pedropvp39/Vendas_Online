
<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/pedidos.php';

no_cache();
require_login();

$base = base_url();
$user = current_user();
$aviso = '';
$sucesso = '';
$flash = get_flash();
$pedidos = get_meus_pedidos((int) ($user['id'] ?? 0));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $aviso = 'Sessão expirada. Tente novamente.';
    } elseif (isset($_POST['excluir'])) {
        delete_user($user['email']);
        header('Location: ' . $base . '/index.php');
        exit();
    } elseif (isset($_POST['remover_avatar'])) {
        if (!empty($user['avatar'])) {
            $avatarFile = __DIR__ . '/../' . $user['avatar'];
            if (file_exists($avatarFile) && is_file($avatarFile)) {
                @unlink($avatarFile);
            }
        }
        update_user($user['email'], ['avatar' => null]);
        $user = current_user();
        $sucesso = 'Foto de perfil removida com sucesso!';
    } else {
        $novaSenha = $_POST['senha_nova'] ?? '';
        $erroUpload = '';
        $avatarRelPath = $user['avatar'] ?? null;

        if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['avatar_file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $erroUpload = 'Erro ao carregar a imagem. Tente novamente.';
            } else {
                $maxSize = 3 * 1024 * 1024; // 3MB
                if ($file['size'] > $maxSize) {
                    $erroUpload = 'A imagem do avatar deve ter no máximo 3MB.';
                } else {
                    $allowedTypes = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                        'image/gif' => 'gif',
                    ];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);

                    if (!isset($allowedTypes[$mimeType])) {
                        $erroUpload = 'Formato de imagem inválido. Use JPG, PNG, WEBP ou GIF.';
                    } else {
                        $ext = $allowedTypes[$mimeType];
                        $uploadDir = __DIR__ . '/../assets/img/avatars/';
                        if (!is_dir($uploadDir)) {
                            @mkdir($uploadDir, 0777, true);
                        }
                        $userId = (int) ($user['id'] ?? 0);
                        $safeFileName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                        $targetPath = $uploadDir . $safeFileName;

                        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                            if (!empty($user['avatar'])) {
                                $oldFile = __DIR__ . '/../' . $user['avatar'];
                                if (file_exists($oldFile) && is_file($oldFile)) {
                                    @unlink($oldFile);
                                }
                            }
                            $avatarRelPath = 'assets/img/avatars/' . $safeFileName;
                        } else {
                            $erroUpload = 'Não foi possível salvar o avatar enviado.';
                        }
                    }
                }
            }
        }

        if ($erroUpload !== '') {
            $aviso = $erroUpload;
        } elseif ($novaSenha !== '' && strlen($novaSenha) < 8) {
            $aviso = 'A nova senha deve ter no mínimo 8 caracteres.';
        } else {
            $dadosUpdate = [
                'nome' => $_POST['nome'] ?? '',
                'nascimento' => $_POST['nascimento'] ?? '',
                'senha_nova' => $novaSenha,
            ];
            if ($avatarRelPath !== ($user['avatar'] ?? null)) {
                $dadosUpdate['avatar'] = $avatarRelPath;
            }
            update_user($user['email'], $dadosUpdate);
            $user = current_user();
            $sucesso = 'Informações atualizadas com sucesso!';
        }
    }
}

$primeiraLetra = function_exists('mb_substr')
    ? mb_substr($user['nome'] ?? 'U', 0, 1)
    : substr($user['nome'] ?? 'U', 0, 1);
$inicial = strtoupper($primeiraLetra);
$hasAvatar = !empty($user['avatar']) && file_exists(__DIR__ . '/../' . $user['avatar']);

$page_title = 'Meu perfil';
require __DIR__ . '/../includes/header.php';
?>

<div class="dash">
    <aside class="sidebar">
        <div class="avatar <?= $hasAvatar ? 'avatar-img-wrap' : '' ?>" aria-hidden="true">
            <?php if ($hasAvatar): ?>
                <img src="<?= e($base . '/' . $user['avatar']) ?>" alt="Avatar de <?= e($user['nome']) ?>" class="avatar-photo">
            <?php else: ?>
                <?= e($inicial) ?>
            <?php endif; ?>
        </div>
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

        <?php if ($flash): ?>
            <p class="alert alert-success" role="status"><?= e($flash['message']) ?></p>
        <?php endif; ?>
        <?php if ($aviso): ?>
            <p class="alert alert-error" role="alert"><?= e($aviso) ?></p>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <p class="alert alert-success" role="status"><?= e($sucesso) ?></p>
        <?php endif; ?>

        <form method="post" action="<?= e($base) ?>/pages/dashboard.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

            <div class="avatar-edit-box">
                <div class="avatar-preview-container">
                    <div class="avatar avatar-preview" id="avatarPreviewContainer">
                        <?php if ($hasAvatar): ?>
                            <img src="<?= e($base . '/' . $user['avatar']) ?>" alt="Foto de perfil" class="avatar-photo" id="avatarPreviewImg">
                        <?php else: ?>
                            <span id="avatarPreviewInitial"><?= e($inicial) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="avatar-file-input">
                        <label for="avatar_file" class="avatar-upload-btn">
                            <span>📷 Escolher nova foto</span>
                            <input type="file" id="avatar_file" name="avatar_file" accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;">
                        </label>
                        <span class="avatar-file-name" id="avatarFileName">Nenhum arquivo selecionado</span>
                        <span class="avatar-help-text">JPG, PNG, WEBP ou GIF (máximo 3MB)</span>
                    </div>
                </div>
                <?php if ($hasAvatar): ?>
                    <button type="submit" name="remover_avatar" value="1" class="avatar-remove-btn" onclick="return confirm('Tem certeza que deseja remover sua foto de perfil?');">
                        Remover foto
                    </button>
                <?php endif; ?>
            </div>

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
                <div class="field field-password">
                    <label for="senha_nova">Nova senha</label>
                    <div class="password-wrapper">
                        <input type="password" id="senha_nova" name="senha_nova" autocomplete="new-password"
                               placeholder="Deixe em branco para manter" minlength="8" maxlength="8">
                        <button type="button" class="toggle-password" data-target="senha_nova" aria-label="Mostrar/esconder senha">👁️</button>
                    </div>
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

<section class="section" aria-labelledby="meus-pedidos-title">
    <div class="section-head">
        <div>
            <h2 id="meus-pedidos-title">Minhas compras</h2>
            <p>Seu histórico atualiza automaticamente conforme o status da entrega.</p>
        </div>
    </div>

    <?php if (empty($pedidos)): ?>
        <p class="empty-message">Você ainda não comprou nenhum item.</p>
    <?php else: ?>
        <div class="produtos">
            <?php foreach ($pedidos as $pedido): ?>
                <article class="produto">
                    <div class="produto-body">
                        <span class="produto-cat"><?= e($pedido['categoria']) ?></span>
                        <h3><?= e($pedido['produto_nome']) ?></h3>
                        <p>Quantidade: <?= (int) $pedido['quantidade'] ?> · Valor: <?= e(money($pedido['preco'])) ?></p>
                        <p>Status: <strong><?= e($pedido['status']) ?></strong></p>
                        <?php if ($pedido['status'] === 'Entregue'): ?>
                            <form method="post" action="<?= e($base) ?>/php/pedidos-acao.php">
                                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="acao" value="remover">
                                <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">
                                <button type="submit" class="btn-danger btn">Remover item</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var avatarInput = document.getElementById('avatar_file');
    var avatarFileName = document.getElementById('avatarFileName');
    var previewContainer = document.getElementById('avatarPreviewContainer');

    if (avatarInput && previewContainer) {
        avatarInput.addEventListener('change', function(e) {
            var file = e.target.files && e.target.files[0];
            if (file) {
                if (avatarFileName) {
                    avatarFileName.textContent = file.name;
                }
                var reader = new FileReader();
                reader.onload = function(evt) {
                    previewContainer.innerHTML = '<img src="' + evt.target.result + '" alt="Prévia da foto" class="avatar-photo" id="avatarPreviewImg">';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>