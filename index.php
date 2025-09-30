<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();

// =================== CONFIGURAÇÕES DO BANCO DE DADOS ===================
require_once __DIR__ . '/config.php';
[$servidor, $usuario, $senha, $banco] = get_db_credentials();
// =====================================================================

// DETECTA SE A REQUISIÇÃO É AJAX (CHAVE PARA O LIVE SEARCH)
$is_ajax = isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1;

// Configurações de paginação
$itens_por_pagina = 20;
$pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

// Conexão com o banco de dados
$conexao = new mysqli($servidor, $usuario, $senha, $banco);
if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}
$conexao->set_charset("utf8mb4");

// Função para buscar a lista de tabelas (setores)
function get_lista_setores($conexao) {
    $tabelas = [];
    $resultado = $conexao->query("SHOW TABLES");
    while ($linha = $resultado->fetch_array()) {
        if ($linha[0] !== 'importar' && $linha[0] !== 'consulta') {
            $tabelas[] = $linha[0];
        }
    }
    return $tabelas;
}

$lista_setores = get_lista_setores($conexao);
$todos_os_resultados = []; // Usaremos esta variável para os resultados
$termo_busca = '';
$setor_busca = '';
$form_enviado = false;
$total_resultados = 0;
$total_paginas = 0;

// Monta a consulta sempre: por padrão carrega todos os ramais (setor=todos)
$form_enviado = true;
$termo_busca = trim($_GET['busca'] ?? '');
$setor_busca = $_GET['setor'] ?? 'todos';
$like_termo = "%" . $termo_busca . "%";

    $queries = [];
    $tabelas_para_buscar = ($setor_busca == 'todos') ? $lista_setores : [$setor_busca];

    foreach ($tabelas_para_buscar as $tabela) {
        if (!in_array($tabela, $lista_setores)) continue;

        if (empty($termo_busca)) {
            $queries[] = "(SELECT *, '$tabela' as setor FROM `$tabela`)";
        } else {
            $queries[] = "(SELECT *, '$tabela' as setor FROM `$tabela` WHERE `descricao` LIKE ? OR `falar_com` LIKE ? OR `ramal` LIKE ? OR `sub_setor` LIKE ? OR '$tabela' LIKE ?)";
        }
    }

if (!empty($queries)) {
    $sql_base = implode(" UNION ALL ", $queries);
    $sql_count = "SELECT COUNT(*) AS total FROM (" . $sql_base . ") AS t";
    $stmt_count = $conexao->prepare($sql_count);

        if (!empty($termo_busca)) {
            $tipos = str_repeat('sssss', count($queries));
            $params = [];
            for ($i = 0; $i < count($queries); $i++) {
                array_push($params, $like_termo, $like_termo, $like_termo, $like_termo, $like_termo);
            }
            $stmt_count->bind_param($tipos, ...$params);
        }

    $stmt_count->execute();
    $total_resultados = intval($stmt_count->get_result()->fetch_assoc()['total'] ?? 0);
    $total_paginas = ($total_resultados > 0) ? ceil($total_resultados / $itens_por_pagina) : 0;
    $stmt_count->close();
    
    if ($total_resultados > 0) {
        $sql_paginado = $sql_base . " ORDER BY setor, sub_setor, descricao LIMIT ? OFFSET ?";
        $stmt_paginado = $conexao->prepare($sql_paginado);

        if (!empty($termo_busca)) {
            $tipos_paginado = $tipos . 'ii';
            $params_paginado = array_merge($params, [$itens_por_pagina, $offset]);
            $stmt_paginado->bind_param($tipos_paginado, ...$params_paginado);
        } else {
            $stmt_paginado->bind_param('ii', $itens_por_pagina, $offset);
        }

        $stmt_paginado->execute();
        $resultado_paginado = $stmt_paginado->get_result();
        while ($linha = $resultado_paginado->fetch_assoc()) {
            $todos_os_resultados[] = $linha;
        }
        $stmt_paginado->close();
    }
}
$conexao->close();

// SE NÃO FOR AJAX, RENDERIZA O CABEÇALHO E TOPO DA PÁGINA
if (!$is_ajax): 
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#2e7d32">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>Consulta de Ramais</title>
    <link rel="manifest" href="./manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Paleta moderna: verde folha, amarelo melão, laranja mamão */
        :root { 
            --leaf:#2e7d32; 
            --leaf-700:#256628; 
            --papaya:#ff6a2a; 
            --papaya-700:#e65100; 
            --border:#e6efe6; 
            --bg:#f5f7f5; 
            --text:#233127; 
            --muted:#6b7a6d;
            --mobile-padding: 16px;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Inter', system-ui, sans-serif; 
            line-height: 1.6; 
            color: var(--text); 
            background-color: var(--bg); 
            margin: 0; 
            padding: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .container { 
            max-width: 100%; 
            margin: 0 auto; 
            background-color: #fff; 
            padding: 20px var(--mobile-padding); 
            min-height: 100vh;
        }
        
        @media (min-width: 768px) {
            .container {
                max-width: 1000px;
                padding: 28px;
                min-height: auto;
                border-radius: 12px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.06);
                border: 1px solid var(--border);
                margin: 24px auto;
            }
        }
        
        .header-container {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            min-height: 80px;
        }
        
        .logo {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 60px;
            width: auto;
            max-width: 200px;
        }
        
        @media (max-width: 767px) {
            .header-container {
                min-height: 60px;
                flex-direction: column;
                gap: 12px;
                padding-top: 10px;
            }
            
            .logo {
                position: static;
                transform: none;
                height: 50px;
                max-width: 150px;
                margin: 0 auto;
            }
            
            h1 {
                font-size: 1.3rem;
                margin-top: 8px;
            }
        }
        
        h1 { 
            color: var(--leaf); 
            text-align: center; 
            font-weight: 700; 
            letter-spacing: .2px;
            font-size: 1.5rem;
            margin: 0;
        }
        
        @media (min-width: 768px) {
            h1 {
                font-size: 2rem;
            }
        }
        
        form { 
            display: flex; 
            flex-direction: column;
            gap: 12px; 
            margin-bottom: 24px; 
        }
        
        @media (min-width: 768px) {
            form {
                flex-direction: row;
                align-items: center;
                gap: 14px;
            }
        }
        
        form input, form select, form button { 
            padding: 16px 18px; 
            border: 1px solid var(--border); 
            border-radius: 12px; 
            font-size: 16px; 
            background: #fff; 
            color: var(--text);
            width: 100%;
            -webkit-appearance: none;
            appearance: none;
        }
        
        @media (min-width: 768px) {
            form input, form select, form button {
                padding: 12px 14px;
                border-radius: 50px;
                width: auto;
            }
            
            form input { 
                flex: 3 1 250px; 
            }
            
            form select { 
                flex: 2 1 200px; 
            }
        }
        
        form select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7a6d' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
        }
        
        /* Ocultamos o botão de busca original, pois a busca é ao digitar */
        form button { 
            display: none; 
        }
        
        .results-table { 
            width: 100%; 
            border-collapse: collapse; 
            overflow: hidden; 
            border-radius: 10px;
            font-size: 14px;
        }
        
        @media (min-width: 768px) {
            .results-table {
                font-size: 16px;
            }
        }
        
        .results-table th, .results-table td { 
            border-bottom: 1px solid var(--border); 
            padding: 12px 8px; 
            text-align: left; 
            vertical-align: middle;
            word-wrap: break-word;
        }
        
        @media (min-width: 768px) {
            .results-table th, .results-table td {
                padding: 14px 10px;
            }
        }
        
        .results-table thead th { 
            background: linear-gradient(180deg, var(--papaya), var(--papaya-700)); 
            color: #fff; 
            font-weight: 700; 
            border-bottom: 2px solid var(--papaya-700);
            font-size: 13px;
        }
        
        @media (min-width: 768px) {
            .results-table thead th {
                font-size: 16px;
            }
        }
        
        .results-table tr:hover { 
            background-color: #f9fbf8; 
        }
        
        .setor-col { 
            white-space: nowrap;
            font-weight: 600;
            color: var(--leaf);
        }
        
        .no-wrap { 
            white-space: nowrap; 
        }
        
        .ramal-col { 
            font-weight: 700; 
            color: var(--leaf); 
            text-align: left; 
            width: 80px;
            white-space: nowrap;
        }
        
        @media (min-width: 768px) {
            .ramal-col {
                width: 120px;
            }
        }
        
        .results-table th.ramal-col { 
            text-align: left; 
        }
        
        .acoes-col { 
            text-align: center; 
            width: 80px;
        }
        
        @media (min-width: 768px) {
            .acoes-col {
                width: 110px;
            }
        }
        
        .btn-copiar { 
            background: var(--leaf); 
            color: #fff; 
            border: none; 
            padding: 10px 14px; 
            font-size: 12px; 
            border-radius: 8px; 
            cursor: pointer; 
            transition: filter .2s, transform .06s; 
            box-shadow: 0 2px 6px rgba(46,125,50,0.25);
            min-width: 60px;
            touch-action: manipulation;
        }
        
        @media (min-width: 768px) {
            .btn-copiar {
                padding: 8px 12px;
                min-width: auto;
            }
        }
        
        .btn-copiar:hover { 
            filter: brightness(1.05); 
        }
        
        .btn-copiar:active { 
            transform: translateY(1px); 
        }
        
        .btn-copiar.copiado { 
            background: var(--papaya-700); 
        }
        
        .no-results { 
            text-align: center; 
            color: var(--muted); 
            font-size: 16px; 
            padding: 40px 20px;
        }
        
        @media (min-width: 768px) {
            .no-results {
                font-size: 18px;
                padding: 20px;
            }
        }
        
        .pagination { 
            text-align: center; 
            margin-top: 24px; 
            padding: 16px 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
        }
        
        .pagination a, .pagination span { 
            display: inline-block; 
            padding: 10px 14px; 
            margin: 0; 
            text-decoration: none; 
            border: 1px solid var(--border); 
            border-radius: 10px; 
            color: var(--papaya-700); 
            background: #fff; 
            font-weight: 600;
            font-size: 14px;
            min-width: 44px;
            text-align: center;
            touch-action: manipulation;
        }
        
        @media (min-width: 768px) {
            .pagination a, .pagination span {
                padding: 8px 12px;
                margin: 0 4px;
                min-width: auto;
            }
        }
        
        .pagination span.current { 
            background: var(--papaya-700); 
            color: #fff; 
            border-color: var(--papaya-700); 
        }
        
        .pagination a:hover { 
            background-color: #eef6ee; 
        }
        
        .pagination a.disabled { 
            color: #9fb2a1; 
            pointer-events: none; 
        }
        
        .results-info { 
            text-align: center; 
            color: var(--muted); 
            margin-bottom: 16px; 
            font-size: 13px;
            padding: 0 8px;
        }
        
        @media (min-width: 768px) {
            .results-info {
                font-size: 14px;
                padding: 0;
            }
        }
        
        #content-container.loading { 
            opacity: 0.5; 
            transition: opacity 0.3s; 
        }
        
        /* Mobile-specific optimizations */
        @media (max-width: 767px) {
            /* Hide less important columns on mobile */
            .results-table .sub-setor-col {
                display: none;
            }
            
            /* Make description column more readable */
            .results-table .descricao-col {
                max-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            /* Optimize table for mobile */
            .results-table {
                font-size: 13px;
            }
            
            .results-table th, .results-table td {
                padding: 10px 6px;
            }
            
            /* Make buttons easier to tap */
            .btn-copiar {
                padding: 12px 16px;
                font-size: 13px;
                min-width: 70px;
            }
            
            /* Optimize pagination for mobile */
            .pagination {
                gap: 6px;
            }
            
            .pagination a, .pagination span {
                padding: 12px 10px;
                font-size: 13px;
                min-width: 50px;
            }
        }
        
        /* Loading animation */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--leaf);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Mobile card layout alternative */
        @media (max-width: 767px) {
            .mobile-card {
                display: block;
            }
            
            .mobile-card .card {
                background: #fff;
                border: 1px solid var(--border);
                border-radius: 12px;
                padding: 16px;
                margin-bottom: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            
            .mobile-card .card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 8px;
            }
            
            .mobile-card .card-title {
                font-weight: 600;
                color: var(--leaf);
                font-size: 14px;
                margin: 0;
            }
            
            .mobile-card .card-ramal {
                font-weight: 700;
                color: var(--leaf);
                font-size: 16px;
            }
            
            .mobile-card .card-content {
                margin-bottom: 12px;
            }
            
            .mobile-card .card-description {
                color: var(--text);
                font-size: 13px;
                line-height: 1.4;
            }
            
            .mobile-card .card-subsetor {
                color: var(--muted);
                font-size: 12px;
                margin-top: 4px;
            }
            
            .mobile-card .card-actions {
                text-align: right;
            }
        }
        
        /* Hide desktop table on mobile */
        @media (max-width: 767px) {
            .desktop-table {
                display: none;
            }
        }
        
        /* Hide mobile cards on desktop */
        @media (min-width: 768px) {
            .mobile-card {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-container">
            <img src="logo-sgra2.png" alt="Logo São Gonçalo do Rio Abaixo" class="logo">
            <h1>Consulta de Ramais</h1>
            <div style="position:absolute; right:0; top:50%; transform: translateY(-50%); display:flex; gap:8px;">
                <?php if (!empty($_SESSION['is_admin'])): ?>
                    <a href="./admin.php" style="text-decoration:none; background: var(--leaf); color:#fff; padding:10px 14px; border-radius:8px; font-weight:600; font-size:14px;">Painel</a>
                    <a href="./logout.php" style="text-decoration:none; background: #b00020; color:#fff; padding:10px 14px; border-radius:8px; font-weight:600; font-size:14px;">Sair</a>
                <?php else: ?>
                    <a href="./admin.php" style="text-decoration:none; background: var(--papaya-700); color:#fff; padding:10px 14px; border-radius:8px; font-weight:700; font-size:14px;">Admin</a>
                <?php endif; ?>
            </div>
        </div>
        
        <form id="search-form" action="" method="GET">
            <input type="text" id="busca-input" name="busca" placeholder="Digite para buscar..." value="<?= htmlspecialchars($termo_busca) ?>">
            <select id="setor-select" name="setor">
                <option value="todos">Todos os Setores</option>
                <?php foreach ($lista_setores as $setor): ?>
                    <option value="<?= $setor ?>" <?= ($setor_busca == $setor ? 'selected' : '') ?>>
                        <?= ucfirst(str_replace('_', ' ', $setor)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Buscar</button>
        </form>

        <div id="content-container">
<?php 
endif; // FIM DO BLOCO IF(!$is_ajax)

// =======================================================================================
// ESTE BLOCO ABAIXO É RENDERIZADO TANTO NA CARGA INICIAL QUANTO NAS REQUISIÇÕES AJAX
// =======================================================================================
?>
            <div class="results-container">
                <?php if ($form_enviado): ?>
                    <?php if (!empty($todos_os_resultados)): ?>
                        <div class="results-info">
                            Mostrando <?= count($todos_os_resultados) ?> de <?= $total_resultados ?> resultados
                            (Página <?= $pagina_atual ?> de <?= $total_paginas ?>)
                        </div>
                        
                        <!-- Desktop Table -->
                        <table class="results-table desktop-table">
                            <thead>
                                <tr>
                                    <th class="setor-col">Setor</th>
                                    <th class="sub-setor-col">Sub-setor</th>
                                    <th class="descricao-col">Descrição / Falar com</th>
                                    <th class="ramal-col">Ramal</th>
                                    <th class="acoes-col">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todos_os_resultados as $linha): ?>
                                    <?php
                                    $falar_com = htmlspecialchars($linha['falar_com'] ?? '');
                                    $descricao = htmlspecialchars($linha['descricao'] ?? '');
                                    if (!empty($falar_com) && !empty($descricao)) {
                                        $contato = $falar_com . ' (' . $descricao . ')';
                                    } elseif (!empty($falar_com)) {
                                        $contato = $falar_com;
                                    } else {
                                        $contato = $descricao;
                                    }
                                    ?>
                                    <tr>
                                        <td class="setor-col"><?= ucfirst(str_replace('_', ' ', htmlspecialchars($linha['setor']))) ?></td>
                                        <td class="sub-setor-col"><?= htmlspecialchars($linha['sub_setor'] ?? 'Geral') ?></td>
                                        <td class="descricao-col"><?= $contato ?></td>
                                        <td class="ramal-col"><?= htmlspecialchars($linha['ramal']) ?></td>
                                        <td class="acoes-col">
                                            <button class="btn-copiar" data-ramal="<?= htmlspecialchars($linha['ramal']) ?>">
                                                Copiar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <!-- Mobile Cards -->
                        <div class="mobile-card">
                            <?php foreach ($todos_os_resultados as $linha): ?>
                                <?php
                                $falar_com = htmlspecialchars($linha['falar_com'] ?? '');
                                $descricao = htmlspecialchars($linha['descricao'] ?? '');
                                if (!empty($falar_com) && !empty($descricao)) {
                                    $contato = $falar_com . ' (' . $descricao . ')';
                                } elseif (!empty($falar_com)) {
                                    $contato = $falar_com;
                                } else {
                                    $contato = $descricao;
                                }
                                ?>
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title"><?= ucfirst(str_replace('_', ' ', htmlspecialchars($linha['setor']))) ?></h3>
                                        <span class="card-ramal"><?= htmlspecialchars($linha['ramal']) ?></span>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-description"><?= $contato ?></div>
                                        <div class="card-subsetor"><?= htmlspecialchars($linha['sub_setor'] ?? 'Geral') ?></div>
                                    </div>
                                    <div class="card-actions">
                                        <button class="btn-copiar" data-ramal="<?= htmlspecialchars($linha['ramal']) ?>">
                                            Copiar
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($total_paginas > 1): ?>
                            <div class="pagination">
                                <?php
                                    $params = $_GET;
                                    // Links da paginação (código sem alteração)...
                                    if ($pagina_atual > 1): ?> <a href="?<?= http_build_query(array_merge($params, ['pagina' => 1])) ?>">Primeira</a> <?php else: ?> <span class="disabled">Primeira</span> <?php endif;
                                    if ($pagina_atual > 1): ?> <a href="?<?= http_build_query(array_merge($params, ['pagina' => $pagina_atual - 1])) ?>">Anterior</a> <?php else: ?> <span class="disabled">Anterior</span> <?php endif;
                                    $inicio = max(1, $pagina_atual - 2); $fim = min($total_paginas, $pagina_atual + 2);
                                    for ($i = $inicio; $i <= $fim; $i++): if ($i == $pagina_atual): ?> <span class="current"><?= $i ?></span> <?php else: ?> <a href="?<?= http_build_query(array_merge($params, ['pagina' => $i])) ?>"><?= $i ?></a> <?php endif; endfor;
                                    if ($pagina_atual < $total_paginas): ?> <a href="?<?= http_build_query(array_merge($params, ['pagina' => $pagina_atual + 1])) ?>">Próxima</a> <?php else: ?> <span class="disabled">Próxima</span> <?php endif;
                                    if ($pagina_atual < $total_paginas): ?> <a href="?<?= http_build_query(array_merge($params, ['pagina' => $total_paginas])) ?>">Última</a> <?php else: ?> <span class="disabled">Última</span> <?php endif;
                                ?>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <p class="no-results">Nenhum resultado encontrado.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
<?php
// SE NÃO FOR AJAX, RENDERIZA O RESTANTE DA PÁGINA E O JAVASCRIPT
if (!$is_ajax):
?>
        </div> </div> <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const buscaInput = document.getElementById('busca-input');
        const setorSelect = document.getElementById('setor-select');
        const contentContainer = document.getElementById('content-container');
        let debounceTimer;

        // Função principal que executa a busca
        function performSearch(page = 1) {
            const termoBusca = buscaInput.value;
            const setorBusca = setorSelect.value;
            
            // Adiciona um efeito visual de carregamento
            contentContainer.classList.add('loading');

            // Constrói a URL para a requisição AJAX
            const params = new URLSearchParams({
                busca: termoBusca,
                setor: setorBusca,
                pagina: page,
                ajax: 1 // Parâmetro essencial para o PHP saber que é um live search
            });

            // Atualiza a URL no navegador do usuário sem recarregar a página
            const url = window.location.pathname + '?' + params.toString().replace('ajax=1', '').replace(/&$/, '');
            window.history.pushState({ path: url }, '', url);
            
            // Faz a requisição ao servidor
            fetch(window.location.pathname + '?' + params.toString())
                .then(response => response.text())
                .then(html => {
                    contentContainer.innerHTML = html;
                    contentContainer.classList.remove('loading');
                })
                .catch(error => {
                    console.error('Erro na busca:', error);
                    contentContainer.innerHTML = '<p class="no-results">Ocorreu um erro ao buscar.</p>';
                    contentContainer.classList.remove('loading');
                });
        }

        // Evento de digitação no campo de busca (com delay para não sobrecarregar)
        buscaInput.addEventListener('keyup', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                performSearch(1); // Sempre volta para a primeira página ao digitar
            }, 400); // Delay de 400ms
        });

        // Evento de mudança no seletor de setor
        setorSelect.addEventListener('change', function() {
            performSearch(1); // Sempre volta para a primeira página ao mudar o setor
        });

        // Delegação de eventos para os cliques na paginação e no botão de copiar
        document.addEventListener('click', function(e) {
            // Lógica para a paginação AJAX
            const pageLink = e.target.closest('.pagination a');
            if (pageLink) {
                e.preventDefault(); // Impede o link de recarregar a página
                const url = new URL(pageLink.href);
                const page = url.searchParams.get('pagina') || 1;
                performSearch(page);
                return; // Encerra para não conflitar com a lógica de cópia
            }

            // Lógica para copiar o ramal (já existente)
            const copyButton = e.target.closest('.btn-copiar');
            if (copyButton) {
                const ramal = copyButton.getAttribute('data-ramal') || '';
                
                // Melhorar feedback visual para mobile
                const originalText = copyButton.innerText;
                copyButton.innerText = 'Copiado!';
                copyButton.classList.add('copiado');
                
                // Usar Clipboard API com fallback
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(ramal).then(() => {
                        setTimeout(() => {
                            copyButton.innerText = originalText;
                            copyButton.classList.remove('copiado');
                        }, 1500);
                    }).catch(() => {
                        // Fallback para navegadores mais antigos
                        fallbackCopyTextToClipboard(ramal, copyButton, originalText);
                    });
                } else {
                    // Fallback para navegadores sem Clipboard API
                    fallbackCopyTextToClipboard(ramal, copyButton, originalText);
                }
            }
        });
        
        // Função fallback para copiar texto
        function fallbackCopyTextToClipboard(text, button, originalText) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            textArea.style.opacity = "0";
            
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    setTimeout(() => {
                        button.innerText = originalText;
                        button.classList.remove('copiado');
                    }, 1500);
                } else {
                    alert('Não foi possível copiar o ramal.');
                    button.innerText = originalText;
                    button.classList.remove('copiado');
                }
            } catch (err) {
                alert('Não foi possível copiar o ramal.');
                button.innerText = originalText;
                button.classList.remove('copiado');
            }
            
            document.body.removeChild(textArea);
        }
        
        // Prevenir zoom em input no iOS
        if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
            buscaInput.addEventListener('focus', function() {
                this.style.fontSize = '16px';
            });
            
            buscaInput.addEventListener('blur', function() {
                this.style.fontSize = '';
            });
        }
        
        // Otimizar para touch devices
        if ('ontouchstart' in window) {
            // Aumentar área de toque para botões
            const buttons = document.querySelectorAll('.btn-copiar, .pagination a');
            buttons.forEach(button => {
                button.style.minHeight = '44px';
                button.style.minWidth = '44px';
            });
        }
    });
    </script>
        <script>
    if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("./service-worker.js")
        .then(() => console.log("Service Worker registrado."))
        .catch((e) => console.warn("Falha ao registrar SW", e));
    }

    let deferredPrompt;
    window.addEventListener("beforeinstallprompt", (e) => {
    e.preventDefault();
    deferredPrompt = e;
    const div = document.createElement("div");
    div.id = "installPrompt";
    div.style.position = "fixed";
    div.style.bottom = "20px";
    div.style.left = "20px";
    div.style.background = "#fff";
    div.style.padding = "15px";
    div.style.border = "1px solid #ccc";
    div.style.borderRadius = "8px";
    div.style.boxShadow = "0 2px 6px rgba(0,0,0,0.2)";
    div.innerHTML = `
        <p>Deseja adicionar o <b>Ramais PMSGRA</b> na sua área de trabalho?</p>
        <button id="installBtn">Adicionar</button>
        <button onclick="document.getElementById('installPrompt').remove()">Agora não</button>
    `;
    document.body.appendChild(div);

    document.getElementById("installBtn").addEventListener("click", () => {
        div.remove();
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choice) => {
        if (choice.outcome === "accepted") {
            console.log("Usuário aceitou instalar");
        } else {
            console.log("Usuário recusou instalar");
        }
        deferredPrompt = null;
        });
    });
    });
    </script>
</body>
</html>
<?php endif; // FIM DO BLOCO IF(!$is_ajax) ?>