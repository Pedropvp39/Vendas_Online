<?php
// Inicia o código PHP principal do arquivo index.php

// Inclui as configurações globais do sistema URL base, funções auxiliares de segurança e sessão
require_once __DIR__ . '/includes/config.php';

// Inclui as funções de banco de dados e manipulação de dados dos produtos e categorias
require_once __DIR__ . '/includes/data.php';

// Define o título que aparecerá na aba do navegador do usuário
$page_title = 'Peças de PC de alta performance';

// Obtém a URL base dinâmica do projeto para montar os caminhos dos arquivos
$base = base_url();

// Busca a lista de categorias do catálogo cadastradas no banco de dados MySQL
$categorias = get_categorias();

// Busca apenas os produtos marcados como destaque no banco de dados MySQL
$destaques = get_produtos_destaque();
$estatisticasLoja = get_loja_estatisticas();

// Inclui o arquivo do cabeçalho HTML (contém a barra de navegação e menu superior)
require __DIR__ . '/includes/header.php';
?>

<!-- Carrossel principal com ofertas e categorias da página inicial -->
<section class="hero" data-hero-carousel aria-label="Destaques da TechFlow">
    <div class="hero-content">
        <div class="hero-slides">
            <article class="hero-slide is-active" data-slide>
                <span class="hero-eyebrow">◆ Montagem &amp; upgrade de PCs</span>
                <h1>Monte o seu PC com peças de <span class="grad">alta performance</span></h1>
                <p>Processadores, placas de vídeo, memória RAM, SSD e muito mais, com preços competitivos e entrega rápida para o seu setup dos sonhos.</p>
                <div class="hero-actions">
                    <a class="btn" href="<?= e($base) ?>/pages/produtos.php">Ver produtos</a>
                    <a class="btn secondary" href="<?= e($base) ?>/pages/cadastro.php">Criar conta</a>
                </div>
            </article>
            <article class="hero-slide" data-slide aria-hidden="true">
                <span class="hero-eyebrow">◆ Desempenho para jogar</span>
                <h1>Mais velocidade para o seu <span class="grad">setup gamer</span></h1>
                <p>Encontre placas de vídeo, processadores e memória para jogar com estabilidade e aproveitar cada frame.</p>
                <div class="hero-actions">
                    <a class="btn" href="<?= e($base) ?>/pages/produtos.php?cat=Placas%20de%20v%C3%ADdeo">Ver GPUs</a>

                </div>
            </article>
            <article class="hero-slide" data-slide aria-hidden="true">
                <span class="hero-eyebrow">◆ Oferta da semana</span>
                <h1>Atualize seu computador com <span class="grad">entrega rápida</span></h1>
                <p>Componentes selecionados, produtos originais e condições especiais para você montar sem complicação.</p>
                <div class="hero-actions">
                    <a class="btn" href="<?= e($base) ?>/pages/produtos.php">Explorar ofertas</a>
                </div>
            </article>
        </div>
    </div>

    <div class="hero-visual" aria-roledescription="carrossel" aria-label="Imagens de produtos em destaque">
        <div class="hero-image-slides">
            <div class="hero-image-slide is-active" data-slide><img src="<?= e($base) ?>/assets/img/gabinete.png" alt="Gabinete gamer com iluminação RGB vermelha"></div>
            <div class="hero-image-slide" data-slide aria-hidden="true"><img src="<?= e($base) ?>/assets/img/gpu-rtx.png" alt="Placa de vídeo GeForce RTX 4060"></div>
            <div class="hero-image-slide" data-slide aria-hidden="true"><img src="<?= e($base) ?>/assets/img/cpu-ryzen.png" alt="Processador AMD Ryzen 5 5600"></div>
        </div>
        <button class="carousel-control carousel-prev" type="button" data-carousel-prev aria-label="Slide anterior">&#8249;</button>
        <button class="carousel-control carousel-next" type="button" data-carousel-next aria-label="Próximo slide">&#8250;</button>
        <div class="carousel-dots" role="tablist" aria-label="Selecionar slide">
            <button class="carousel-dot is-active" type="button" data-carousel-dot="0" role="tab" aria-label="Ir para slide 1" aria-selected="true"></button>
            <button class="carousel-dot" type="button" data-carousel-dot="1" role="tab" aria-label="Ir para slide 2" aria-selected="false"></button>
            <button class="carousel-dot" type="button" data-carousel-dot="2" role="tab" aria-label="Ir para slide 3" aria-selected="false"></button>
        </div>
    </div>
</section>

<!-- Seção com estatísticas e diferenciais da loja -->
<section class="hero-stats" aria-label="Destaques da loja">
    <!-- Card de estatística 1: Quantidade de produtos -->
    <div class="hero-stat"><strong><?= number_format($estatisticasLoja['produtos'], 0, ',', '.') ?></strong><span>produtos no catálogo</span></div>

    <!-- Card de estatística 2: Agilidade de envio -->
    <div class="hero-stat"><strong>24h</strong><span>envio para todo o Brasil</span></div>

    <!-- Card de estatística 3: Satisfação dos clientes -->
    <div class="hero-stat"><strong><?= number_format($estatisticasLoja['nota'], 1, ',', '.') ?>/5</strong><span>média de <?= $estatisticasLoja['avaliacoes'] ?> avaliação(ões)</span></div>
</section>

<!-- Seção de exibição das categorias principais -->
<section class="section" aria-labelledby="cat-title">
    <!-- Cabeçalho da seção de categorias -->
    <div class="section-head">
        <div>
            <!-- Título da seção -->
            <h2 id="cat-title">Principais categorias</h2>
            <!-- Subtítulo explicativo -->
            <p>Encontre exatamente o que falta para o seu build.</p>
        </div>
    </div>

    <!-- Container em grade das categorias -->
    <div class="cards">
        <!-- Loop PHP que percorre cada categoria vinda do banco de dados MySQL -->
        <?php foreach ($categorias as $cat): ?>
            <!-- Card clicável de categoria que direciona para o catálogo filtrado -->
            <a class="card" href="<?= e($base) ?>/pages/produtos.php?cat=<?= urlencode($cat['nome']) ?>">
                <span class="category-card-media">
                    <img src="<?= e($base) ?>/assets/img/<?= e($cat['icone'] ?: 'default.png') ?>" alt="" loading="lazy">
                </span>
                <span class="category-card-content">
                    <h3><?= e($cat['nome']) ?></h3>
                    <span class="category-card-link">Ver produtos <span aria-hidden="true">→</span></span>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Seção de produtos em destaque na loja -->
<section class="section" aria-labelledby="dest-title">
    <!-- Cabeçalho da seção de produtos em destaque -->
    <div class="section-head">
        <div>
            <!-- Título da seção -->
            <h2 id="dest-title">Produtos em destaque</h2>
            <!-- Subtítulo da seção -->
            <p>Ofertas da semana com preços especiais.</p>
        </div>
        <!-- Link para ver todo o catálogo de produtos -->
        <a class="link-more" href="<?= e($base) ?>/pages/produtos.php">Ver todos →</a>
    </div>

    <!-- Grid com a lista dos produtos em destaque -->
    <div class="produtos">
        <!-- Loop PHP que exibe cada produto marcado como destaque -->
        <?php foreach ($destaques as $p): ?>
            <!-- Card de produto individual -->
            <article class="produto">
                <!-- Imagem do produto com link para a página de detalhes -->
                <a class="produto-media" href="<?= e($base) ?>/pages/produto.php?id=<?= $p['id'] ?>">
                    <img src="<?= e($base) ?>/assets/img/<?= e($p['imagem']) ?>" alt="<?= e($p['nome']) ?>" loading="lazy">
                </a>

                <!-- Corpo de informações do produto -->
                <div class="produto-body">
                    <!-- Categoria do produto -->
                    <span class="produto-cat"><?= e($p['categoria']) ?></span>

                    <!-- Título do produto com link para a página de detalhes -->
                    <h3><a href="<?= e($base) ?>/pages/produto.php?id=<?= $p['id'] ?>"><?= e($p['nome']) ?></a></h3>
                    <!-- Rodapé do card de produto com preço e formulário de compra -->
                    <div class="produto-footer">
                        <!-- Exibe o preço formatado em Real (R$) -->
                        <span class="preco"><?= e(money($p['preco'])) ?></span>

                        <!-- Formulário de adição direta ao carrinho de compras -->
                        <form method="post" action="<?= e($base) ?>/php/carrinho-acao.php" data-cart-form>
                            <!-- Token CSRF para proteção do formulário -->
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <!-- Ação enviada ao backend (adicionar item) -->
                            <input type="hidden" name="acao" value="add">
                            <!-- ID único do produto a ser adicionado -->
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <!-- Quantidade padrão adicionada -->
                            <input type="hidden" name="qty" value="1">
                            <!-- Botão de submissão do formulário -->
                            <button type="submit" class="btn-buy">Comprar</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<script src="<?= e($base) ?>/assets/js/hero-carousel.js" defer></script>

<!-- Inclui o rodapé HTML com direitos autorais e scripts JavaScript do sistema -->
<?php require __DIR__ . '/includes/footer.php'; ?>
