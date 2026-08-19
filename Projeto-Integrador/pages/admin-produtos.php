<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/data.php';

no_cache();
require_admin();

$base = base_url();
$mensagem = '';
$tipoMensagem = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $mensagem = 'Sessão expirada. Tente novamente.';
        $tipoMensagem = 'error';
    } else {
        $resultado = adicionar_produto([
            'nome' => $_POST['nome'] ?? '',
            'categoria' => $_POST['categoria'] ?? '',
            'preco' => $_POST['preco'] ?? 0,
            'descricao' => $_POST['descricao'] ?? '',
            'imagem' => $_POST['imagem'] ?? 'default.png',
            'destaque' => !empty($_POST['destaque']),
        ]);

        $mensagem = $resultado['mensagem'];
        $tipoMensagem = $resultado['ok'] ? 'success' : 'error';
    }
}

$page_title = 'Admin - Produtos';
require __DIR__ . '/../includes/header.php';
?>

<div class="section">
    <div class="section-head">
        <div>
            <h1>Área administrativa</h1>
            <p>Cadastre novos produtos para o catálogo do site.</p>
        </div>
    </div>

    <?php if ($mensagem): ?>
        <p class="alert <?= $tipoMensagem === 'success' ? 'alert-success' : 'alert-error' ?>" role="status"><?= e($mensagem) ?></p>
    <?php endif; ?>

    <div class="panel" style="max-width: 820px; margin: 0 auto;">
        <form method="post" action="<?= e($base) ?>/pages/admin-produtos.php" class="form-grid">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

            <div class="field">
                <label for="nome">Nome do produto</label>
                <input id="nome" name="nome" type="text" required>
            </div>

            <div class="field">
                <label for="categoria">Categoria</label>
                <input id="categoria" name="categoria" type="text" required>
            </div>

            <div class="field">
                <label for="preco">Preço</label>
                <input id="preco" name="preco" type="number" min="0.01" step="0.01" required>
            </div>

            <div class="field">
                <label for="imagem">Imagem</label>
                <input id="imagem" name="imagem" type="text" value="default.png" required>
            </div>

            <div class="field" style="grid-column: 1 / -1;">
                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" rows="4" required></textarea>
            </div>

            <div class="field" style="grid-column: 1 / -1;">
                <label>
                    <input type="checkbox" name="destaque" value="1"> Produto em destaque
                </label>
            </div>

            <div class="panel-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="btn">Salvar produto</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
