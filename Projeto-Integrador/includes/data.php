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

function seed_initial_produtos(mysqli $db): void
{
    try {
        $check = $db->query("SELECT COUNT(*) AS total FROM produtos");
        if ($check) {
            $row = $check->fetch_assoc();
            if ((int) ($row['total'] ?? 0) > 0) {
                return;
            }
        }

        $catalog = [
            [1, 'AMD Ryzen 5 5600', 'Processadores', 899.00, 'Ideal para montar um PC equilibrado e rápido para games e trabalho.', 'cpu-ryzen.png', 1],
            [2, 'GeForce RTX 4060', 'Placas de vídeo', 2199.00, 'Excelente desempenho para jogos em Full HD e 2K com ray tracing.', 'gpu-rtx.png', 1],
            [3, 'SSD NVMe 1TB', 'Armazenamento', 349.00, 'Mais velocidade de boot e carregamento de jogos e programas.', 'ssd.png', 1],
            [4, 'Memória RAM DDR5 32GB', 'Memória RAM', 749.00, 'Kit 2x16GB com mais velocidade e estabilidade para multitarefas.', 'ram.png', 0],
            [5, 'Placa-mãe B650 Gaming', 'Placas-mãe', 1099.00, 'Suporte a DDR5 e PCIe 4.0 para builds modernas e expansíveis.', 'motherboard.png', 0],
            [6, 'Gabinete Gamer Mid-Tower', 'Gabinetes', 459.00, 'Lateral em vidro temperado e fans RGB para exibir seu setup com estilo.', 'gabinete.png', 0],
            [7, 'Fonte 750W 80 Plus Gold', 'Fontes', 629.00, 'Fonte modular com alta eficiência e proteção para seus componentes.', 'fonte.png', 0],
            [8, 'Water Cooler 240mm', 'Refrigeração', 539.00, 'Refrigeração líquida com iluminação RGB para manter a CPU fria.', 'cooler.png', 0],
        ];

        foreach ($catalog as $item) {
            $stmt = $db->prepare("INSERT INTO produtos (id, nome, categoria, preco, descricao, imagem, destaque) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE nome = VALUES(nome), preco = VALUES(preco)");
            $stmt->bind_param('issdssi', $item[0], $item[1], $item[2], $item[3], $item[4], $item[5], $item[6]);
            $stmt->execute();
        }
    } catch (Throwable $e) {
        error_log('seed_initial_produtos: ' . $e->getMessage());
    }
}

function get_produtos(): array
{
    try {
        $db = db_connect();
        seed_initial_produtos($db);

        $result = $db->query('SELECT * FROM produtos ORDER BY id ASC');
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

function processar_upload_imagem(?array $file, string $default = 'default.png'): string
{
    if (!$file || empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return '';
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
    if (!in_array($ext, $allowed, true)) {
        return '';
    }

    $targetDir = __DIR__ . '/../assets/img/';
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0777, true);
    }

    $filename = 'prod_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
    $targetFile = $targetDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return $filename;
    }

    return '';
}

function adicionar_produto(array $dados, ?array $file = null): array
{
    $nome = trim((string) ($dados['nome'] ?? ''));
    $categoria = trim((string) ($dados['categoria'] ?? ''));
    $preco = (float) ($dados['preco'] ?? 0);
    $descricao = trim((string) ($dados['descricao'] ?? ''));
    $destaque = !empty($dados['destaque']) ? 1 : 0;

    $imagemUpload = processar_upload_imagem($file);
    $imagem = $imagemUpload !== '' ? $imagemUpload : trim((string) ($dados['imagem'] ?? 'default.png'));
    if ($imagem === '') {
        $imagem = 'default.png';
    }

    if ($nome === '' || $categoria === '' || $preco <= 0 || $descricao === '') {
        return ['ok' => false, 'mensagem' => 'Preencha nome, categoria, preço e descrição do produto.'];
    }

    try {
        $db = db_connect();

        // Garante que a categoria exista na tabela categorias do MySQL
        adicionar_categoria($categoria, 'Produtos da categoria ' . $categoria);

        $stmt = $db->prepare('INSERT INTO produtos (nome, categoria, preco, descricao, imagem, destaque) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssdssi', $nome, $categoria, $preco, $descricao, $imagem, $destaque);
        $stmt->execute();
        return ['ok' => true, 'mensagem' => '✅ Produto "' . $nome . '" cadastrado e salvo com sucesso no MySQL!'];
    } catch (Throwable $e) {
        error_log('adicionar_produto: ' . $e->getMessage());
        return ['ok' => false, 'mensagem' => 'Não foi possível salvar o produto no MySQL: ' . $e->getMessage()];
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

function normalize_categoria(array $row): array
{
    return [
        'id' => (int) ($row['id'] ?? 0),
        'nome' => (string) ($row['nome'] ?? ''),
        'desc' => (string) ($row['descricao'] ?? $row['desc'] ?? ''),
        'descricao' => (string) ($row['descricao'] ?? $row['desc'] ?? ''),
        'icone' => (string) ($row['icone'] ?? ''),
    ];
}

function seed_initial_categorias(mysqli $db): void
{
    try {
        $check = $db->query("SELECT COUNT(*) AS total FROM categorias");
        if ($check) {
            $row = $check->fetch_assoc();
            if ((int) ($row['total'] ?? 0) > 0) {
                return;
            }
        }

        $catalogoCategorias = [
            ['Processadores', 'Desempenho para games, edição e multitarefas.', 'cpu-ryzen.png'],
            ['Placas de vídeo', 'Potência para jogos em alta qualidade.', 'gpu-rtx.png'],
            ['Memória RAM', 'Mais velocidade e estabilidade para o seu setup.', 'ram.png'],
            ['Armazenamento', 'SSD e HD com performance superior.', 'ssd.png'],
            ['Placas-mãe', 'Suporte a componentes modernos e expansível.', 'motherboard.png'],
            ['Gabinetes', 'Espaço e circulação de ar para builds profissionais.', 'gabinete.png'],
            ['Fontes', 'Alta eficiência e proteção para seus componentes.', 'fonte.png'],
            ['Refrigeração', 'Mantém sua CPU fria em qualquer situação.', 'cooler.png'],
        ];

        foreach ($catalogoCategorias as $cat) {
            $stmt = $db->prepare("INSERT INTO categorias (nome, descricao, icone) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE descricao = VALUES(descricao)");
            $stmt->bind_param('sss', $cat[0], $cat[1], $cat[2]);
            $stmt->execute();
        }
    } catch (Throwable $e) {
        error_log('seed_initial_categorias: ' . $e->getMessage());
    }
}

function get_categorias(): array
{
    try {
        $db = db_connect();
        seed_initial_categorias($db);

        $result = $db->query('SELECT * FROM categorias ORDER BY id ASC');
        if ($result && $result->num_rows > 0) {
            $categorias = [];
            while ($row = $result->fetch_assoc()) {
                $categorias[] = normalize_categoria($row);
            }
            return $categorias;
        }
    } catch (Throwable $e) {
        error_log('get_categorias: ' . $e->getMessage());
    }

    return [
        ['id' => 1, 'nome' => 'Processadores', 'desc' => 'Desempenho para games, edição e multitarefas.', 'descricao' => 'Desempenho para games, edição e multitarefas.'],
        ['id' => 2, 'nome' => 'Placas de vídeo', 'desc' => 'Potência para jogos em alta qualidade.', 'descricao' => 'Potência para jogos em alta qualidade.'],
        ['id' => 3, 'nome' => 'Memória RAM', 'desc' => 'Mais velocidade e estabilidade para o seu setup.', 'descricao' => 'Mais velocidade e estabilidade para o seu setup.'],
        ['id' => 4, 'nome' => 'Armazenamento', 'desc' => 'SSD e HD com performance superior.', 'descricao' => 'SSD e HD com performance superior.'],
    ];
}

function adicionar_categoria(string $nome, ?string $descricao = null, ?string $icone = null): array
{
    $nome = trim($nome);
    $descricao = trim((string) ($descricao ?? ''));
    $icone = trim((string) ($icone ?? ''));

    if ($nome === '') {
        return ['ok' => false, 'mensagem' => 'Informe o nome da categoria.'];
    }

    try {
        $db = db_connect();
        $stmtCheck = $db->prepare('SELECT id FROM categorias WHERE nome = ? LIMIT 1');
        $stmtCheck->bind_param('s', $nome);
        $stmtCheck->execute();
        if ($stmtCheck->get_result()->num_rows > 0) {
            return ['ok' => true, 'mensagem' => 'Categoria já existente.'];
        }

        $stmt = $db->prepare('INSERT INTO categorias (nome, descricao, icone) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $nome, $descricao, $icone);
        $stmt->execute();
        return ['ok' => true, 'mensagem' => 'Categoria cadastrada com sucesso!'];
    } catch (Throwable $e) {
        error_log('adicionar_categoria: ' . $e->getMessage());
        return ['ok' => false, 'mensagem' => 'Erro ao salvar categoria no banco de dados.'];
    }
}

function atualizar_produto(int $id, array $dados, ?array $file = null): array
{
    $nome = trim((string) ($dados['nome'] ?? ''));
    $categoria = trim((string) ($dados['categoria'] ?? ''));
    $preco = (float) ($dados['preco'] ?? 0);
    $descricao = trim((string) ($dados['descricao'] ?? ''));
    $destaque = !empty($dados['destaque']) ? 1 : 0;

    $imagemUpload = processar_upload_imagem($file);
    if ($imagemUpload !== '') {
        $imagem = $imagemUpload;
    } else {
        $imagem = trim((string) ($dados['imagem'] ?? 'default.png'));
        if ($imagem === '') {
            $imagem = 'default.png';
        }
    }

    if ($id <= 0 || $nome === '' || $categoria === '' || $preco <= 0 || $descricao === '') {
        return ['ok' => false, 'mensagem' => 'Preencha todos os campos obrigatórios do produto.'];
    }

    try {
        $db = db_connect();
        adicionar_categoria($categoria, 'Produtos da categoria ' . $categoria);
        $stmt = $db->prepare('UPDATE produtos SET nome = ?, categoria = ?, preco = ?, descricao = ?, imagem = ?, destaque = ? WHERE id = ?');
        $stmt->bind_param('ssdssii', $nome, $categoria, $preco, $descricao, $imagem, $destaque, $id);
        $stmt->execute();
        return ['ok' => true, 'mensagem' => '✅ Produto #' . $id . ' atualizado com sucesso no MySQL!'];
    } catch (Throwable $e) {
        error_log('atualizar_produto: ' . $e->getMessage());
        return ['ok' => false, 'mensagem' => 'Erro ao atualizar produto no banco de dados: ' . $e->getMessage()];
    }
}

function excluir_produto(int $id): array
{
    if ($id <= 0) {
        return ['ok' => false, 'mensagem' => 'ID de produto inválido.'];
    }

    try {
        $db = db_connect();
        $stmt = $db->prepare('DELETE FROM produtos WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return ['ok' => true, 'mensagem' => 'Produto excluído com sucesso!'];
    } catch (Throwable $e) {
        error_log('excluir_produto: ' . $e->getMessage());
        return ['ok' => false, 'mensagem' => 'Erro ao excluir produto no banco de dados.'];
    }
}

function atualizar_categoria(int $id, string $nome, ?string $descricao = null): array
{
    $nome = trim($nome);
    $descricao = trim((string) ($descricao ?? ''));

    if ($id <= 0 || $nome === '') {
        return ['ok' => false, 'mensagem' => 'Nome da categoria é obrigatório.'];
    }

    try {
        $db = db_connect();
        $stmt = $db->prepare('UPDATE categorias SET nome = ?, descricao = ? WHERE id = ?');
        $stmt->bind_param('ssi', $nome, $descricao, $id);
        $stmt->execute();
        return ['ok' => true, 'mensagem' => 'Categoria atualizada com sucesso!'];
    } catch (Throwable $e) {
        error_log('atualizar_categoria: ' . $e->getMessage());
        return ['ok' => false, 'mensagem' => 'Erro ao atualizar categoria no banco de dados.'];
    }
}

function excluir_categoria(int $id): array
{
    if ($id <= 0) {
        return ['ok' => false, 'mensagem' => 'ID de categoria inválido.'];
    }

    try {
        $db = db_connect();
        $stmt = $db->prepare('DELETE FROM categorias WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return ['ok' => true, 'mensagem' => 'Categoria excluída com sucesso!'];
    } catch (Throwable $e) {
        error_log('excluir_categoria: ' . $e->getMessage());
        return ['ok' => false, 'mensagem' => 'Erro ao excluir categoria no banco de dados.'];
    }
}
