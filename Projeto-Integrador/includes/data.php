<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../php/conexao.php';

function normalize_produto(array $row): array
{
    return [
        'id' => (int) ($row['id'] ?? 0),
        'nome' => (string) ($row['nome'] ?? ''),
        'categoria' => (string) ($row['categoria'] ?? ''),
        'preco' => (float) ($row['preco'] ?? 0),
        'descricao' => (string) ($row['descricao'] ?? ''),
        'imagem' => (string) ($row['imagem'] ?? 'default.png'),
        'destaque' => (bool) ((int) ($row['destaque'] ?? 0)),
    ];
}

function get_produtos(): array
{
    try {
        $db = db_connect();
        $result = $db->query('SELECT * FROM produtos ORDER BY id DESC');
        if ($result && $result->num_rows > 0) {
            $produtos = [];
            while ($row = $result->fetch_assoc()) {
                $produtos[] = normalize_produto($row);
            }
            return $produtos;
        }
    } catch (Throwable $e) {
        error_log('get_produtos: ' . $e->getMessage());
    }

    return [
        [
            'id' => 1,
            'nome' => 'AMD Ryzen 5 5600',
            'categoria' => 'Processadores',
            'preco' => 899.00,
            'descricao' => 'Ideal para montar um PC equilibrado e rápido para games e trabalho.',
            'imagem' => 'cpu-ryzen.png',
            'destaque' => true,
        ],
        [
            'id' => 2,
            'nome' => 'GeForce RTX 4060',
            'categoria' => 'Placas de vídeo',
            'preco' => 2199.00,
            'descricao' => 'Excelente desempenho para jogos em Full HD e 2K com ray tracing.',
            'imagem' => 'gpu-rtx.png',
            'destaque' => true,
        ],
        [
            'id' => 3,
            'nome' => 'SSD NVMe 1TB',
            'categoria' => 'Armazenamento',
            'preco' => 349.00,
            'descricao' => 'Mais velocidade de boot e carregamento de jogos e programas.',
            'imagem' => 'ssd.png',
            'destaque' => true,
        ],
        [
            'id' => 4,
            'nome' => 'Memória RAM DDR5 32GB',
            'categoria' => 'Memória RAM',
            'preco' => 749.00,
            'descricao' => 'Kit 2x16GB com mais velocidade e estabilidade para multitarefas.',
            'imagem' => 'ram.png',
            'destaque' => false,
        ],
        [
            'id' => 5,
            'nome' => 'Placa-mãe B650 Gaming',
            'categoria' => 'Placas-mãe',
            'preco' => 1099.00,
            'descricao' => 'Suporte a DDR5 e PCIe 4.0 para builds modernas e expansíveis.',
            'imagem' => 'motherboard.png',
            'destaque' => false,
        ],
        [
            'id' => 6,
            'nome' => 'Gabinete Gamer Mid-Tower',
            'categoria' => 'Gabinetes',
            'preco' => 459.00,
            'descricao' => 'Lateral em vidro temperado e fans RGB para exibir seu setup com estilo.',
            'imagem' => 'gabinete.png',
            'destaque' => false,
        ],
        [
            'id' => 7,
            'nome' => 'Fonte 750W 80 Plus Gold',
            'categoria' => 'Fontes',
            'preco' => 629.00,
            'descricao' => 'Fonte modular com alta eficiência e proteção para seus componentes.',
            'imagem' => 'fonte.png',
            'destaque' => false,
        ],
        [
            'id' => 8,
            'nome' => 'Water Cooler 240mm',
            'categoria' => 'Refrigeração',
            'preco' => 539.00,
            'descricao' => 'Refrigeração líquida com iluminação RGB para manter a CPU fria.',
            'imagem' => 'cooler.png',
            'destaque' => false,
        ],
    ];
}

function get_produtos_destaque(): array
{
    return array_values(array_filter(get_produtos(), fn ($p) => $p['destaque']));
}

function get_produto(int $id): ?array
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('SELECT * FROM produtos WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row) {
            return normalize_produto($row);
        }
    } catch (Throwable $e) {
        error_log('get_produto: ' . $e->getMessage());
    }

    foreach (get_produtos() as $p) {
        if ($p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

function adicionar_produto(array $dados): array
{
    $nome = trim((string) ($dados['nome'] ?? ''));
    $categoria = trim((string) ($dados['categoria'] ?? ''));
    $preco = (float) ($dados['preco'] ?? 0);
    $descricao = trim((string) ($dados['descricao'] ?? ''));
    $imagem = trim((string) ($dados['imagem'] ?? 'default.png'));
    $destaque = !empty($dados['destaque']) ? 1 : 0;

    if ($nome === '' || $categoria === '' || $preco <= 0 || $descricao === '') {
        return ['ok' => false, 'mensagem' => 'Preencha nome, categoria, preço e descrição do produto.'];
    }

    try {
        $db = db_connect();
        $stmt = $db->prepare('INSERT INTO produtos (nome, categoria, preco, descricao, imagem, destaque) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssdssi', $nome, $categoria, $preco, $descricao, $imagem, $destaque);
        $stmt->execute();
        return ['ok' => true, 'mensagem' => 'Produto cadastrado com sucesso!'];
    } catch (Throwable $e) {
        error_log('adicionar_produto: ' . $e->getMessage());
        return ['ok' => false, 'mensagem' => 'Não foi possível salvar o produto no banco de dados.'];
    }
}

/**
 * Produtos relacionados (mesma categoria), excluindo o atual.
 */
function get_relacionados(array $produto, int $limite = 3): array
{
    $rel = array_filter(
        get_produtos(),
        fn ($p) => $p['categoria'] === $produto['categoria'] && $p['id'] !== $produto['id']
    );
    // Completa com outros produtos se houver poucos da mesma categoria.
    if (count($rel) < $limite) {
        foreach (get_produtos() as $p) {
            if ($p['id'] !== $produto['id'] && !isset($rel[$p['id'] - 1])) {
                $rel[] = $p;
            }
        }
    }
    return array_slice(array_values($rel), 0, $limite);
}

function get_categorias(): array
{
    return [
        ['nome' => 'Processadores', 'desc' => 'Desempenho para games, edição e multitarefas.'],
        ['nome' => 'Placas de vídeo', 'desc' => 'Potência para jogos em alta qualidade.'],
        ['nome' => 'Memória RAM', 'desc' => 'Mais velocidade e estabilidade para o seu setup.'],
        ['nome' => 'Armazenamento', 'desc' => 'SSD e HD com performance superior.'],
    ];
}
