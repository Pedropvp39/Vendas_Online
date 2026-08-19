<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data.php';

$page_title = 'Peças de PC de alta performance';
$base = base_url();
$categorias = get_categorias();
$destaques = get_produtos_destaque();

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero-content">
        <span class="hero-eyebrow">◆ Montagem &amp; upgrade de PCs</span>
        <h1>Monte o seu PC com peças de <span class="grad">alta performance</span></h1>
        <p>Processadores, placas de vídeo, memória RAM, SSD e muito mais, com preços competitivos e entrega rápida para o seu setup dos sonhos.</p>
        <div class="hero-actions">
            <a class="btn" href="<?= e($base) ?>/pages/produtos.php">Ver produtos</a>
            <a class="btn secondary" href="<?= e($base) ?>/pages/cadastro.php">Criar conta</a>
        </div>
    </div>
    <div class="hero-visual">
        <img src="<?= e($base) ?>/assets/img/gabinete.png" alt="Gabinete gamer com iluminação RGB vermelha">
    </div>
</section>

<section class="hero-stats" aria-label="Destaques da loja">
    <div class="hero-stat"><strong>+500</strong><span>peças no catálogo</span></div>
    <div class="hero-stat"><strong>24h</strong><span>envio para todo o Brasil</span></div>
    <div class="hero-stat"><strong>4.9/5</strong><span>avaliação dos clientes</span></div>
</section>

<section class="section" aria-labelledby="cat-title">
    <div class="section-head">
        <div>
            <h2 id="cat-title">Principais categorias</h2>
            <p>Encontre exatamente o que falta para o seu build.</p>
        </div>
    </div>
    <div class="cards">
        <?php foreach ($categorias as $cat): ?>
            <article class="card">
                <h3><?= e($cat['nome']) ?></h3>
                <p><?= e($cat['desc']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section" aria-labelledby="dest-title">
    <div class="section-head">
        <div>
            <h2 id="dest-title">Produtos em destaque</h2>
            <p>Ofertas da semana com preços especiais.</p>
        </div>
        <a class="link-more" href="<?= e($base) ?>/pages/produtos.php">Ver todos →</a>
    </div>
    <div class="produtos">
        <?php foreach ($destaques as $p): ?>
            <article class="produto">
                <a class="produto-media" href="<?= e($base) ?>/pages/produto.php?id=<?= $p['id'] ?>">
                    <img src="<?= e($base) ?>/assets/img/<?= e($p['imagem']) ?>" alt="<?= e($p['nome']) ?>" loading="lazy">
                </a>
                <div class="produto-body">
                    <span class="produto-cat"><?= e($p['categoria']) ?></span>
                    <h3><a href="<?= e($base) ?>/pages/produto.php?id=<?= $p['id'] ?>"><?= e($p['nome']) ?></a></h3>
                    <p><?= e($p['descricao']) ?></p>
                    <div class="produto-footer">
                        <span class="preco"><?= e(money($p['preco'])) ?></span>
                        <form method="post" action="<?= e($base) ?>/php/carrinho-acao.php" data-cart-form>
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="acao" value="add">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="qty" value="1">
                            <button type="submit" class="btn-buy">Comprar</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
