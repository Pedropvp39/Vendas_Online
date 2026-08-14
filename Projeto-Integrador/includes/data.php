<?php
/**
 * Catálogo de produtos (dados de exemplo).
 * Em um projeto real, isso viria de um banco de dados.
 */

function get_produtos(): array
{
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
    foreach (get_produtos() as $p) {
        if ($p['id'] === $id) {
            return $p;
        }
    }
    return null;
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
