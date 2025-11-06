<?php
// Desabilitar exibição de erros em produção (comentar as linhas abaixo para debug)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
session_start();

// =================== CONFIGURAÇÕES DO BANCO DE DADOS ===================
require_once __DIR__ . '/config.php';
// =====================================================================

// DETECTA SE A REQUISIÇÃO É AJAX (CHAVE PARA O LIVE SEARCH)
$is_ajax = isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1;

// Configurações de paginação
$itens_por_pagina = 20;
$pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

// Conexão com o banco de dados (usando função centralizada)
$conexao = get_db_connection();

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
$todos_os_resultados = [];
$termo_busca = trim($_GET['busca'] ?? '');
$setor_busca = $_GET['setor'] ?? 'todos';
$total_resultados = 0;
$total_paginas = 0;
$like_termo = "%" . $termo_busca . "%";

// Monta queries para cada setor
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

// Executa busca se houver queries
if (!empty($queries)) {
    $sql_base = implode(" UNION ALL ", $queries);
    $sql_count = "SELECT COUNT(*) AS total FROM (" . $sql_base . ") AS t";
    $stmt_count = $conexao->prepare($sql_count);
    
    // Prepara parâmetros se houver termo de busca
    if (!empty($termo_busca)) {
        $tipos = str_repeat('sssss', count($queries));
        $params = [];
        for ($i = 0; $i < count($queries); $i++) {
            $params = array_merge($params, [$like_termo, $like_termo, $like_termo, $like_termo, $like_termo]);
        }
        $stmt_count->bind_param($tipos, ...$params);
    }
    
    // Conta total de resultados
    $stmt_count->execute();
    $total_resultados = intval($stmt_count->get_result()->fetch_assoc()['total'] ?? 0);
    $total_paginas = ($total_resultados > 0) ? ceil($total_resultados / $itens_por_pagina) : 0;
    $stmt_count->close();
    
    // Busca resultados paginados
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
        /* ====================================================================
           VARIÁVEIS CSS - PALETA DE CORES E DESIGN TOKENS
           ==================================================================== */
        :root { 
            /* Cores principais */
            --leaf: #2e7d32; 
            --leaf-700: #1b5e20; 
            --leaf-light: #e8f5e9;
            --papaya: #ff6a2a; 
            --papaya-700: #e65100; 
            
            /* Cores neutras */
            --off-white: #fafafa;
            --border: #e2e8f0; 
            --bg: #f5f7fa; 
            --bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            --text: #1a202c; 
            --text-light: #4a5568;
            --muted: #718096;
            
            /* Espaçamento */
            --mobile-padding: 16px;
            
            /* Sombras */
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 40px rgba(0,0,0,0.12);
        }
        
        /* Reset básico */
        * {
            box-sizing: border-box;
        }
        
        /* ====================================================================
           LAYOUT BASE
           ==================================================================== */
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            line-height: 1.6; 
            color: var(--text); 
            background: var(--bg-gradient);
            margin: 0; 
            padding: 20px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .container { 
            max-width: 100%; 
            margin: 0 auto; 
            background-color: var(--off-white); 
            padding: 0;
            min-height: calc(100vh - 40px);
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }
        
        @media (min-width: 768px) {
            body {
                padding: 24px;
            }
            .container {
                max-width: 1200px;
                min-height: auto;
            }
        }
        
        /* ====================================================================
           HEADER
           ==================================================================== */
        
        .header-container {
            background: var(--off-white);
            padding: 24px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e2e8f0;
            position: relative;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .logo {
            height: 60px;
            width: auto;
            max-width: 200px;
        }
        
        h1 { 
            color: #2e7d32; 
            font-weight: 600; 
            letter-spacing: -0.3px;
            font-size: 1.95rem;
            margin: 0;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }
        
        @media (min-width: 768px) {
            h1 {
                font-size: 2.275rem;
            }
        }
        
        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-left: auto;
        }
        
        .header-actions a {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .header-actions a:first-child {
            background: linear-gradient(135deg, #ff6a2a 0%, #e65100 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(255, 106, 42, 0.2);
        }
        
        .header-actions a:first-child:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 106, 42, 0.3);
        }
        
        .header-actions a:last-child {
            background: #d32f2f;
            color: white;
            box-shadow: 0 2px 8px rgba(211, 47, 47, 0.2);
        }
        
        .header-actions a:last-child:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
        }
        
        @media (max-width: 767px) {
            .header-container {
                flex-direction: column;
                gap: 16px;
                padding: 20px 16px;
                align-items: center;
            }
            
            .logo-section {
                width: 100%;
                justify-content: center;
            }
            
            .logo {
                height: 45px;
                max-width: 150px;
            }
            
            h1 {
                font-size: 1.69rem;
                position: static;
                transform: none;
                order: 2;
                width: 100%;
            }
            
            .logo-section {
                order: 1;
            }
            
            .header-actions {
                width: 100%;
                justify-content: center;
                order: 3;
            }
            
            .header-actions a {
                padding: 10px 16px;
                font-size: 13px;
            }
        }
        
        /* ====================================================================
           SEÇÃO DE BUSCA
           ==================================================================== */
        .search-section {
            padding: 32px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
        }
        
        .content-section {
            padding: 32px;
        }
        
        form { 
            display: flex; 
            flex-direction: column;
            gap: 16px; 
        }
        
        @media (min-width: 768px) {
            form {
                flex-direction: row;
                align-items: flex-end;
                gap: 16px;
            }
        }
        
        .form-group {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        form input, form select { 
            padding: 10px 16px; 
            border: 2px solid var(--border); 
            border-radius: 8px; 
            font-size: 15px; 
            background: var(--off-white); 
            color: var(--text);
            width: 100%;
            height: 44px;
            font-family: inherit;
            transition: all 0.2s;
            -webkit-appearance: none;
            appearance: none;
        }
        
        form input:focus, form select:focus {
            outline: none;
            border-color: var(--leaf);
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
        }
        
        form input::placeholder {
            color: var(--muted);
        }
        
        form select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23718096' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 14px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 44px;
            cursor: pointer;
        }
        
        form button { 
            display: none; 
        }
        
        /* ====================================================================
           TABELA DE RESULTADOS
           ==================================================================== */
        
        .results-table { 
            width: 100%; 
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden; 
            border-radius: 12px;
            font-size: 14px;
            background: var(--off-white);
            box-shadow: var(--shadow-sm);
        }
        
        .results-table th, .results-table td { 
            border-bottom: 1px solid #f1f5f9; 
            padding: 16px; 
            text-align: left; 
            vertical-align: middle;
            word-wrap: break-word;
        }
        
        .results-table thead {
            background: linear-gradient(180deg, var(--papaya) 0%, var(--papaya-700) 100%);
        }
        
        .results-table thead th { 
            background: transparent;
            color: #fff; 
            font-weight: 700; 
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--papaya-700);
            padding: 18px 16px;
        }
        
        .results-table tbody tr {
            transition: all 0.2s;
        }
        
        .results-table tbody tr:hover { 
            background-color: #f8fafc; 
            transform: scale(1.01);
        }
        
        .results-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* Colunas específicas */
        .setor-col { 
            white-space: nowrap;
            font-weight: 600;
            color: var(--leaf);
        }
        
        .ramal-col { 
            font-weight: 700; 
            color: var(--leaf); 
            text-align: left; 
            width: 80px;
            white-space: nowrap;
        }
        
        .acoes-col { 
            text-align: center; 
            width: 80px;
        }
        
        @media (min-width: 768px) {
            .results-table {
                font-size: 15px;
            }
            .results-table thead th {
                font-size: 13px;
            }
            .ramal-col {
                width: 120px;
            }
            .acoes-col {
                width: 110px;
            }
        }
        
        /* ====================================================================
           BOTÕES E AÇÕES
           ==================================================================== */
        
        .btn-copiar { 
            background: linear-gradient(135deg, var(--leaf) 0%, var(--leaf-700) 100%);
            color: #fff; 
            border: none; 
            padding: 10px 18px; 
            font-size: 13px; 
            font-weight: 600;
            border-radius: 8px; 
            cursor: pointer; 
            transition: all 0.2s; 
            box-shadow: 0 2px 8px rgba(46,125,50,0.3);
            min-width: 80px;
            touch-action: manipulation;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .btn-copiar:hover { 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(46,125,50,0.4);
        }
        
        .btn-copiar:active { 
            transform: translateY(0); 
        }
        
        .btn-copiar.copiado { 
            background: linear-gradient(135deg, var(--papaya) 0%, var(--papaya-700) 100%);
            box-shadow: 0 2px 8px rgba(255,106,42,0.3);
        }
        
        @media (min-width: 768px) {
            .btn-copiar {
                padding: 8px 16px;
                min-width: 90px;
            }
        }
        
        /* ====================================================================
           MENSAGENS E FEEDBACK
           ==================================================================== */
        .results-info { 
            text-align: center; 
            color: var(--text-light); 
            margin-bottom: 24px; 
            font-size: 14px;
            font-weight: 500;
            padding: 16px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid var(--border);
        }
        
        .no-results { 
            text-align: center; 
            color: var(--muted); 
            font-size: 16px; 
            padding: 60px 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 2px dashed var(--border);
        }
        
        .no-results::before {
            content: "🔍";
            font-size: 48px;
            display: block;
            margin-bottom: 16px;
        }
        
        #content-container {
            position: relative;
        }
        
        #content-container.loading { 
            opacity: 0.6; 
            transition: opacity 0.3s;
            pointer-events: none;
        }
        
        @media (min-width: 768px) {
            .results-info {
                font-size: 15px;
            }
            .no-results {
                font-size: 18px;
                padding: 80px 40px;
            }
        }
        
        /* ====================================================================
           PAGINAÇÃO
           ==================================================================== */
        .pagination { 
            text-align: center; 
            margin-top: 32px; 
            padding: 20px 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        
        .pagination a, .pagination span { 
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px; 
            margin: 0; 
            text-decoration: none; 
            border: 2px solid var(--border); 
            border-radius: 10px; 
            color: var(--text-light); 
            background: var(--off-white); 
            font-weight: 600;
            font-size: 14px;
            min-width: 44px;
            height: 44px;
            text-align: center;
            touch-action: manipulation;
            transition: all 0.2s;
        }
        
        .pagination span.current { 
            background: linear-gradient(135deg, var(--papaya) 0%, var(--papaya-700) 100%); 
            color: #fff; 
            border-color: var(--papaya-700);
            box-shadow: 0 2px 8px rgba(255,106,42,0.3);
        }
        
        .pagination a:hover { 
            background-color: #f8fafc;
            border-color: var(--leaf);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        
        .pagination a.disabled { 
            color: #cbd5e0; 
            border-color: #e2e8f0;
            pointer-events: none; 
            opacity: 0.5;
        }
        
        @media (min-width: 768px) {
            .pagination a, .pagination span {
                padding: 10px 14px;
                margin: 0 2px;
                min-width: 44px;
            }
        }
        
        /* ====================================================================
           ANIMAÇÕES
           ==================================================================== */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
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
        
        /* ====================================================================
           RESPONSIVIDADE - MOBILE
           ==================================================================== */
        
        @media (max-width: 767px) {
            /* Esconde colunas menos importantes */
            .results-table .sub-setor-col {
                display: none;
            }
            
            /* Otimiza coluna de descrição */
            .results-table .descricao-col {
                max-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            /* Otimiza tabela */
            .results-table {
                font-size: 13px;
            }
            
            .results-table th, .results-table td {
                padding: 10px 6px;
            }
            
            /* Botões mais fáceis de tocar */
            .btn-copiar {
                padding: 12px 16px;
                font-size: 13px;
                min-width: 70px;
            }
            
            /* Paginação otimizada */
            .pagination {
                gap: 6px;
            }
            
            .pagination a, .pagination span {
                padding: 12px 10px;
                font-size: 13px;
                min-width: 50px;
            }
            
            /* Cards mobile */
            .mobile-card {
                display: block;
            }
            
            .desktop-table {
                display: none;
            }
            
            .mobile-card .card {
                background: var(--off-white);
                border: 2px solid var(--border);
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 16px;
                box-shadow: var(--shadow-sm);
                transition: all 0.2s;
            }
            
            .mobile-card .card:hover {
                box-shadow: var(--shadow-md);
                transform: translateY(-2px);
                border-color: var(--leaf);
            }
            
            .mobile-card .card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 12px;
                padding-bottom: 12px;
                border-bottom: 2px solid #f1f5f9;
            }
            
            .mobile-card .card-title {
                font-weight: 600;
                color: var(--leaf);
                font-size: 14px;
                margin: 0;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .mobile-card .card-ramal {
                font-weight: 700;
                color: var(--leaf);
                font-size: 20px;
                background: var(--leaf-light);
                padding: 6px 12px;
                border-radius: 8px;
            }
            
            .mobile-card .card-content {
                margin-bottom: 16px;
            }
            
            .mobile-card .card-description {
                color: var(--text);
                font-size: 15px;
                line-height: 1.5;
                font-weight: 500;
                margin-bottom: 8px;
            }
            
            .mobile-card .card-subsetor {
                color: var(--muted);
                font-size: 13px;
                font-weight: 500;
            }
            
            .mobile-card .card-actions {
                text-align: right;
                padding-top: 12px;
                border-top: 1px solid #f1f5f9;
            }
        }
        
        /* Esconde cards mobile no desktop */
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
            <div class="logo-section">
                <img src="logo-sgra2.png" alt="Logo São Gonçalo do Rio Abaixo" class="logo">
                <h1>Consulta de Ramais</h1>
            </div>
            <div class="header-actions">
                <?php if (!empty($_SESSION['is_admin'])): ?>
                    <a href="./admin.php">Painel</a>
                    <a href="./logout.php">Sair</a>
                <?php else: ?>
                    <a href="./admin.php">Admin</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="search-section">
            <form id="search-form" action="" method="GET">
                <div class="form-group">
                    <label for="busca-input">🔍 Buscar</label>
                    <input type="text" id="busca-input" name="busca" placeholder="Digite nome, ramal ou descrição..." value="<?= h($termo_busca) ?>">
                </div>
                <div class="form-group">
                    <label for="setor-select">📂 Setor</label>
                    <select id="setor-select" name="setor">
                        <option value="todos">Todos os Setores</option>
                        <?php foreach ($lista_setores as $setor): ?>
                            <option value="<?= h($setor) ?>" <?= ($setor_busca == $setor ? 'selected' : '') ?>>
                                <?= formatar_nome_setor($setor) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" style="display:none">Buscar</button>
            </form>
        </div>

        <div class="content-section">
            <div id="content-container">
<?php 
endif; // FIM DO BLOCO IF(!$is_ajax)

// =======================================================================================
// ESTE BLOCO ABAIXO É RENDERIZADO TANTO NA CARGA INICIAL QUANTO NAS REQUISIÇÕES AJAX
// =======================================================================================
?>
            <div class="results-container">
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
                                <tr>
                                    <td class="setor-col"><?= formatar_nome_setor($linha['setor']) ?></td>
                                    <td class="sub-setor-col"><?= h(formatar_sub_setor($linha['sub_setor'] ?? null)) ?></td>
                                    <td class="descricao-col"><?= h(formatar_contato($linha)) ?></td>
                                    <td class="ramal-col"><?= h($linha['ramal']) ?></td>
                                    <td class="acoes-col">
                                        <button class="btn-copiar" data-ramal="<?= h($linha['ramal']) ?>">
                                            📋 Copiar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Mobile Cards -->
                    <div class="mobile-card">
                        <?php foreach ($todos_os_resultados as $linha): ?>
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title"><?= formatar_nome_setor($linha['setor']) ?></h3>
                                    <span class="card-ramal"><?= h($linha['ramal']) ?></span>
                                </div>
                                <div class="card-content">
                                    <div class="card-description"><?= h(formatar_contato($linha)) ?></div>
                                    <div class="card-subsetor"><?= h(formatar_sub_setor($linha['sub_setor'] ?? null)) ?></div>
                                </div>
                                <div class="card-actions">
                                    <button class="btn-copiar" data-ramal="<?= h($linha['ramal']) ?>">
                                        📋 Copiar Ramal
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($total_paginas > 1): ?>
                        <div class="pagination">
                            <?php
                                $params = $_GET;
                                
                                // Primeira página
                                if ($pagina_atual > 1): 
                                    ?><a href="?<?= http_build_query(array_merge($params, ['pagina' => 1])) ?>">Primeira</a><?php
                                else: 
                                    ?><span class="disabled">Primeira</span><?php
                                endif;
                                
                                // Página anterior
                                if ($pagina_atual > 1): 
                                    ?><a href="?<?= http_build_query(array_merge($params, ['pagina' => $pagina_atual - 1])) ?>">Anterior</a><?php
                                else: 
                                    ?><span class="disabled">Anterior</span><?php
                                endif;
                                
                                // Números das páginas
                                $inicio = max(1, $pagina_atual - 2);
                                $fim = min($total_paginas, $pagina_atual + 2);
                                for ($i = $inicio; $i <= $fim; $i++): 
                                    if ($i == $pagina_atual): 
                                        ?><span class="current"><?= $i ?></span><?php
                                    else: 
                                        ?><a href="?<?= http_build_query(array_merge($params, ['pagina' => $i])) ?>"><?= $i ?></a><?php
                                    endif;
                                endfor;
                                
                                // Próxima página
                                if ($pagina_atual < $total_paginas): 
                                    ?><a href="?<?= http_build_query(array_merge($params, ['pagina' => $pagina_atual + 1])) ?>">Próxima</a><?php
                                else: 
                                    ?><span class="disabled">Próxima</span><?php
                                endif;
                                
                                // Última página
                                if ($pagina_atual < $total_paginas): 
                                    ?><a href="?<?= http_build_query(array_merge($params, ['pagina' => $total_paginas])) ?>">Última</a><?php
                                else: 
                                    ?><span class="disabled">Última</span><?php
                                endif;
                            ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="no-results">Nenhum resultado encontrado.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php
// SE NÃO FOR AJAX, RENDERIZA O RESTANTE DA PÁGINA E O JAVASCRIPT
if (!$is_ajax):
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const buscaInput = document.getElementById('busca-input');
        const setorSelect = document.getElementById('setor-select');
        const contentContainer = document.getElementById('content-container');
        let debounceTimer;

        /**
         * Executa a busca com os parâmetros atuais
         * @param {number} page - Número da página (padrão: 1)
         */
        function performSearch(page = 1) {
            const termoBusca = buscaInput.value;
            const setorBusca = setorSelect.value;
            
            contentContainer.classList.add('loading');

            const params = new URLSearchParams({
                busca: termoBusca,
                setor: setorBusca,
                pagina: page,
                ajax: 1
            });

            const url = window.location.pathname + '?' + params.toString().replace('ajax=1', '').replace(/&$/, '');
            window.history.pushState({ path: url }, '', url);
            
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

        // Busca com debounce para evitar requisições excessivas
        buscaInput.addEventListener('keyup', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                performSearch(1);
            }, 400);
        });

        // Busca ao mudar setor
        setorSelect.addEventListener('change', function() {
            performSearch(1);
        });

        // Delegação de eventos para paginação e cópia
        document.addEventListener('click', function(e) {
            // Paginação AJAX
            const pageLink = e.target.closest('.pagination a');
            if (pageLink) {
                e.preventDefault();
                const url = new URL(pageLink.href);
                const page = url.searchParams.get('pagina') || 1;
                performSearch(page);
                return;
            }

            // Cópia de ramal
            const copyButton = e.target.closest('.btn-copiar');
            if (copyButton) {
                const ramal = copyButton.getAttribute('data-ramal') || '';
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
    div.style.background = "#fafafa";
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