<?php
// Desabilitar exibição de erros em produção (comentar as linhas abaixo para debug)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

// =================== CONFIGURAÇÕES DO BANCO DE DADOS ===================
require_once __DIR__ . '/config.php';
start_app_session();
// =====================================================================

// DETECTA SE A REQUISIÇÃO É AJAX (CHAVE PARA O LIVE SEARCH)
$is_ajax = isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1;

// Configurações de paginação
$itens_por_pagina = 20;
$pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

// Conexão com o banco de dados (usando função centralizada)
$conexao = get_db_connection();

// Busca lista de setores (com cache)
$lista_setores = get_lista_setores($conexao);
$todos_os_resultados = [];
$termo_busca = trim($_GET['busca'] ?? '');
$setor_busca = $_GET['setor'] ?? 'todos';
$tipo_ramal = $_GET['tipo'] ?? 'interno'; // 'interno' | 'externo' | 'centro'
$tipos_validos = ['interno', 'externo', 'centro'];
if (!in_array($tipo_ramal, $tipos_validos, true)) {
    $tipo_ramal = 'interno';
}
$total_resultados = 0;
$total_paginas = 0;
$like_termo = "%" . $termo_busca . "%";

// Separar setores internos e externos
$setores_internos = array_filter($lista_setores, function($s) {
    return $s !== 'externos' && $s !== 'centro_administrativo';
});
$setores_externos = ['externos'];
$setores_centro = ['centro_administrativo'];

// =================== CENTRO ADMINISTRATIVO (sub-setor como filtro) ===================
// Neste tipo, o "setor" da URL é usado como filtro de sub_setor, pois a tabela é única.
$lista_subsetores_centro = [];
if ($tipo_ramal === 'centro') {
    garantir_tabela_centro_administrativo($conexao);

    // Lista de sub-setores para o seletor (normaliza vazio/null para "Geral")
    $res_ss = $conexao->query("SELECT DISTINCT COALESCE(NULLIF(TRIM(sub_setor), ''), 'Geral') AS ss FROM centro_administrativo ORDER BY ss");
    if ($res_ss) {
        while ($row = $res_ss->fetch_assoc()) {
            $ss = trim((string)($row['ss'] ?? ''));
            if ($ss !== '') $lista_subsetores_centro[] = $ss;
        }
        $res_ss->free();
    }

    // Filtro selecionado no combo (via param setor)
    $subsetor_busca = $setor_busca; // reutiliza o mesmo param para manter JS/AJAX

    // Monta WHERE
    $where = [];
    $params = [];
    $types = '';

    // Não mostrar ocultos
    $where[] = "oculto = 0";

    // Filtro por sub_setor (se não for "todos")
    if ($subsetor_busca !== 'todos') {
        if ($subsetor_busca === 'Geral') {
            $where[] = "(sub_setor IS NULL OR TRIM(sub_setor) = '')";
        } else {
            $where[] = "sub_setor = ?";
            $params[] = $subsetor_busca;
            $types .= 's';
        }
    }

    // Busca textual
    if (!empty($termo_busca)) {
        $where[] = "(COALESCE(descricao,'') LIKE ? OR COALESCE(falar_com,'') LIKE ? OR COALESCE(ramal,'') LIKE ? OR COALESCE(sub_setor,'') LIKE ?)";
        for ($i = 0; $i < 4; $i++) $params[] = $like_termo;
        $types .= 'ssss';
    }

    $where_sql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

    // Total
    $sql_count = "SELECT COUNT(*) AS total FROM centro_administrativo $where_sql";
    $stmt_count = $conexao->prepare($sql_count);
    if (!empty($params)) $stmt_count->bind_param($types, ...$params);
    $stmt_count->execute();
    $total_resultados = intval($stmt_count->get_result()->fetch_assoc()['total'] ?? 0);
    $total_paginas = ($total_resultados > 0) ? ceil($total_resultados / $itens_por_pagina) : 0;
    $stmt_count->close();

    // Página
    if ($total_resultados > 0) {
        $sql_sel = "SELECT id, sub_setor, descricao, falar_com, ramal, emergencia, oculto, principal, 'centro_administrativo' as setor
                    FROM centro_administrativo
                    $where_sql
                    ORDER BY principal DESC, emergencia DESC, sub_setor, descricao
                    LIMIT ? OFFSET ?";
        $stmt_sel = $conexao->prepare($sql_sel);
        $types_sel = $types . 'ii';
        $params_sel = array_merge($params, [$itens_por_pagina, $offset]);
        $stmt_sel->bind_param($types_sel, ...$params_sel);
        $stmt_sel->execute();
        $res = $stmt_sel->get_result();
        while ($linha = $res->fetch_assoc()) $todos_os_resultados[] = $linha;
        $stmt_sel->close();
    }

    $conexao->close();
} else {

// Função para verificar se uma coluna existe em uma tabela
function coluna_existe(mysqli $con, string $tabela, string $coluna): bool {
    // Sanitiza o nome da tabela e coluna removendo caracteres não alfanuméricos (exceto underscore)
    $tabela = preg_replace('/[^a-zA-Z0-9_]/', '', $tabela);
    $coluna = preg_replace('/[^a-zA-Z0-9_]/', '', $coluna);
    
    if (empty($tabela) || empty($coluna)) {
        return false;
    }
    
    // Escapa o nome da coluna para segurança adicional
    $coluna_escaped = $con->real_escape_string($coluna);
    
    // Query direta com escape (tabela já foi sanitizada e validada)
    $query = "SHOW COLUMNS FROM `$tabela` LIKE '$coluna_escaped'";
    $result = $con->query($query);
    
    return $result && $result->num_rows > 0;
}

// Monta queries para cada setor
$queries = [];
$params_por_query = []; // Armazena quantos parâmetros cada query precisa

// Filtrar tabelas baseado no tipo selecionado
if ($tipo_ramal === 'externo') {
    $tabelas_disponiveis = $setores_externos;
} else {
    $tabelas_disponiveis = $setores_internos;
}

// Se o setor selecionado não está disponível para o tipo atual, resetar para 'todos'
if ($setor_busca != 'todos' && !in_array($setor_busca, $tabelas_disponiveis)) {
    $setor_busca = 'todos';
}

$tabelas_para_buscar = ($setor_busca == 'todos') ? $tabelas_disponiveis : 
    (in_array($setor_busca, $tabelas_disponiveis) ? [$setor_busca] : []);

foreach ($tabelas_para_buscar as $tabela) {
    if (!in_array($tabela, $lista_setores)) continue;
    
    // Verifica quais colunas existem nesta tabela
    $tem_coluna_sub_setor = coluna_existe($conexao, $tabela, 'sub_setor');
    $tem_coluna_descricao = coluna_existe($conexao, $tabela, 'descricao');
    $tem_coluna_falar_com = coluna_existe($conexao, $tabela, 'falar_com');
    $tem_coluna_ramal = coluna_existe($conexao, $tabela, 'ramal');
    $tem_coluna_oculto = coluna_existe($conexao, $tabela, 'oculto');
    $tem_coluna_emergencia = coluna_existe($conexao, $tabela, 'emergencia');
    $tem_coluna_principal = coluna_existe($conexao, $tabela, 'principal');
    
    // Monta lista de colunas explicitamente para garantir mesmo número de colunas em todas as tabelas
    $colunas = "id";
    if ($tem_coluna_sub_setor) {
        $colunas .= ", sub_setor";
    } else {
        $colunas .= ", NULL as sub_setor";
    }
    if ($tem_coluna_descricao) {
        $colunas .= ", descricao";
    } else {
        $colunas .= ", NULL as descricao";
    }
    if ($tem_coluna_falar_com) {
        $colunas .= ", falar_com";
    } else {
        $colunas .= ", NULL as falar_com";
    }
    if ($tem_coluna_ramal) {
        $colunas .= ", ramal";
    } else {
        $colunas .= ", NULL as ramal";
    }
    if ($tem_coluna_emergencia) {
        $colunas .= ", emergencia";
    } else {
        $colunas .= ", 0 as emergencia";
    }
    if ($tem_coluna_oculto) {
        $colunas .= ", oculto";
    } else {
        $colunas .= ", 0 as oculto";
    }
    if ($tem_coluna_principal) {
        $colunas .= ", principal";
    } else {
        $colunas .= ", 0 as principal";
    }
    $colunas .= ", '$tabela' as setor";
    
    // Filtrar registros ocultos - não mostrar na tela principal (se a coluna existir)
    $where_oculto = $tem_coluna_oculto ? "oculto = 0" : "1=1";
    
    if (empty($termo_busca)) {
        $queries[] = "(SELECT $colunas FROM `$tabela` WHERE $where_oculto)";
        $params_por_query[] = 0; // Nenhum parâmetro necessário
    } else {
        // Monta condições de busca considerando apenas colunas que existem
        $condicoes_busca = [];
        $num_params = 0;
        if ($tem_coluna_descricao) {
            $condicoes_busca[] = "`descricao` LIKE ?";
            $num_params++;
        }
        if ($tem_coluna_falar_com) {
            $condicoes_busca[] = "`falar_com` LIKE ?";
            $num_params++;
        }
        if ($tem_coluna_ramal) {
            $condicoes_busca[] = "`ramal` LIKE ?";
            $num_params++;
        }
        if ($tem_coluna_sub_setor) {
            $condicoes_busca[] = "`sub_setor` LIKE ?";
            $num_params++;
        }
        $condicoes_busca[] = "'$tabela' LIKE ?";
        $num_params++;
        
        $where_busca = " AND (" . implode(" OR ", $condicoes_busca) . ")";
        $queries[] = "(SELECT $colunas FROM `$tabela` WHERE $where_oculto $where_busca)";
        $params_por_query[] = $num_params;
    }
}

// Executa busca se houver queries
if (!empty($queries)) {
    $sql_base = implode(" UNION ALL ", $queries);
    $sql_count = "SELECT COUNT(*) AS total FROM (" . $sql_base . ") AS t";
    $stmt_count = $conexao->prepare($sql_count);
    
    // Prepara parâmetros se houver termo de busca
    $params_count = [];
    $tipos_count = '';
    if (!empty($termo_busca)) {
        foreach ($params_por_query as $num_params) {
            if ($num_params > 0) {
                $tipos_count .= str_repeat('s', $num_params);
                // Adiciona o termo de busca para cada parâmetro necessário
                for ($i = 0; $i < $num_params; $i++) {
                    $params_count[] = $like_termo;
                }
            }
        }
        if (!empty($params_count)) {
            $stmt_count->bind_param($tipos_count, ...$params_count);
        }
    }
    
    // Conta total de resultados
    $stmt_count->execute();
    $total_resultados = intval($stmt_count->get_result()->fetch_assoc()['total'] ?? 0);
    $total_paginas = ($total_resultados > 0) ? ceil($total_resultados / $itens_por_pagina) : 0;
    $stmt_count->close();
    
    // Busca resultados paginados
    if ($total_resultados > 0) {
        // Prioriza ramais principais no topo (depois emergência) e mantém ordenação estável
        $sql_paginado = $sql_base . " ORDER BY principal DESC, emergencia DESC, setor, sub_setor, descricao LIMIT ? OFFSET ?";
        $stmt_paginado = $conexao->prepare($sql_paginado);
        
        if (!empty($termo_busca) && !empty($params_count)) {
            $tipos_paginado = $tipos_count . 'ii';
            $params_paginado = array_merge($params_count, [$itens_por_pagina, $offset]);
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
} // FIM do else (interno/externo)

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
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/ico.png">
    <link rel="apple-touch-icon" href="<?= BASE_PATH ?>/ico.png">
    <link rel="manifest" href="<?= BASE_PATH ?>/manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/styles.css">
</head>
<body>
    <div class="container">
        <div class="header-container">
            <div class="logo-section">
                <img src="<?= BASE_PATH ?>/logo-sgra2.png" alt="Logo São Gonçalo do Rio Abaixo" class="logo">
                <h1>Consulta de Ramais</h1>
            </div>
            <div class="header-actions">
                <a href="./emails.php">Emails</a>
                <?php if (!empty($_SESSION['is_admin'])): ?>
                    <a href="./admin.php">Painel</a>
                    <a href="./logout.php">Sair</a>
                <?php else: ?>
                    <a href="./admin.php">Admin</a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Abas de Navegação -->
        <div class="tabs-navigation" style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: 0; margin-bottom: 0;">
            <div style="max-width: 1200px; margin: 0 auto; display: flex; gap: 0;">
                <a href="?tipo=interno<?= !empty($termo_busca) ? '&busca=' . urlencode($termo_busca) : '' ?><?= $setor_busca != 'todos' ? '&setor=' . urlencode($setor_busca) : '' ?>" 
                   class="tab-link <?= $tipo_ramal === 'interno' ? 'active' : '' ?>" 
                   style="flex: 1; padding: 16px 24px; text-align: center; text-decoration: none; color: <?= $tipo_ramal === 'interno' ? '#2e7d32' : '#718096' ?>; font-weight: <?= $tipo_ramal === 'interno' ? '600' : '500' ?>; border-bottom: 3px solid <?= $tipo_ramal === 'interno' ? '#2e7d32' : 'transparent' ?>; transition: all 0.2s;">
                    🏢 Ramais Externos
                </a>
                <a href="?tipo=centro<?= !empty($termo_busca) ? '&busca=' . urlencode($termo_busca) : '' ?><?= $setor_busca != 'todos' ? '&setor=' . urlencode($setor_busca) : '' ?>" 
                   class="tab-link <?= $tipo_ramal === 'centro' ? 'active' : '' ?>" 
                   style="flex: 1; padding: 16px 24px; text-align: center; text-decoration: none; color: <?= $tipo_ramal === 'centro' ? '#2e7d32' : '#718096' ?>; font-weight: <?= $tipo_ramal === 'centro' ? '600' : '500' ?>; border-bottom: 3px solid <?= $tipo_ramal === 'centro' ? '#2e7d32' : 'transparent' ?>; transition: all 0.2s;">
                    🏛️ Centro Administrativo
                </a>
                <a href="?tipo=externo<?= !empty($termo_busca) ? '&busca=' . urlencode($termo_busca) : '' ?><?= $setor_busca != 'todos' ? '&setor=' . urlencode($setor_busca) : '' ?>" 
                   class="tab-link <?= $tipo_ramal === 'externo' ? 'active' : '' ?>" 
                   style="flex: 1; padding: 16px 24px; text-align: center; text-decoration: none; color: <?= $tipo_ramal === 'externo' ? '#2e7d32' : '#718096' ?>; font-weight: <?= $tipo_ramal === 'externo' ? '600' : '500' ?>; border-bottom: 3px solid <?= $tipo_ramal === 'externo' ? '#2e7d32' : 'transparent' ?>; transition: all 0.2s;">
                    📞 Telefones Externos
                </a>
            </div>
        </div>

        <div class="search-section">
            <form id="search-form" action="" method="GET">
                <input type="hidden" name="tipo" value="<?= h($tipo_ramal) ?>">
                <div class="form-group">
                    <label for="busca-input">🔍 Buscar</label>
                    <input type="text" id="busca-input" name="busca" placeholder="Digite nome, ramal ou descrição..." value="<?= h($termo_busca) ?>">
                </div>
                <div class="form-group">
                    <label for="setor-select">📂 <?= ($tipo_ramal === 'centro') ? 'Sub-setor' : 'Setor' ?></label>
                    <select id="setor-select" name="setor">
                        <option value="todos">Todos os Setores</option>
                        <?php 
                        if ($tipo_ramal === 'centro') {
                            foreach ($lista_subsetores_centro as $ss):
                        ?>
                            <option value="<?= h($ss) ?>" <?= ($setor_busca == $ss ? 'selected' : '') ?>>
                                <?= h($ss) ?>
                            </option>
                        <?php
                            endforeach;
                        } else {
                            $setores_para_exibir = ($tipo_ramal === 'externo') ? $setores_externos : $setores_internos;
                            foreach ($setores_para_exibir as $setor):
                        ?>
                            <option value="<?= h($setor) ?>" <?= ($setor_busca == $setor ? 'selected' : '') ?>>
                                <?= formatar_nome_setor($setor) ?>
                            </option>
                        <?php 
                            endforeach;
                        }
                        ?>
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
            <!-- Aviso sobre Transferência -->
            <p style="background: #fff9e6; color: #8c5d00; border-left: 5px solid #ffc107; padding: 16px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; line-height: 1.6; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <strong>Aviso Importante:</strong> Ramais internos <strong>não podem</strong> transferir ligações para ramais externos e vice-versa. As transferências são permitidas apenas entre ramais do mesmo tipo.
            </p>
            <p style="background: #ffebee; color: #b71c1c; border-left: 5px solid #d32f2f; padding: 16px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; line-height: 1.6; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <strong>Aviso Temporário:</strong> Temporariamente os novos ramais do <strong>Centro Administrativo</strong> só podem transferir ligações <strong>entre eles</strong>.
            </p>
            <?php if ($tipo_ramal === 'centro' && empty($todos_os_resultados)): ?>
                <p style="background: #eef7ff; color: #0b4f6c; border-left: 5px solid #1976d2; padding: 16px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; line-height: 1.6; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <strong>Centro Administrativo:</strong> esta aba está <strong>vazia</strong> no momento. Você poderá adicionar os ramais aqui posteriormente.
                </p>
            <?php endif; ?>
            
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
                                <th class="descricao-col">Falar com / Descrição</th>
                                <th class="ramal-col">Ramal</th>
                                <th class="acoes-col">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todos_os_resultados as $linha): ?>
                                <tr class="<?php echo (isset($linha['emergencia']) && $linha['emergencia']) ? 'emergencia-row' : ''; ?> <?php echo (isset($linha['principal']) && $linha['principal']) ? 'principal-row' : ''; ?>">
                                    <td class="setor-col"><?= formatar_nome_setor($linha['setor']) ?></td>
                                    <td class="sub-setor-col"><?= h(formatar_sub_setor($linha['sub_setor'] ?? null)) ?></td>
                                    <td class="descricao-col">
                                        <?php if (isset($linha['emergencia']) && $linha['emergencia']): ?>
                                            <span style="color: #d32f2f; font-weight: 600;">🚨 SOMENTE EMERGÊNCIAS - </span>
                                        <?php elseif (isset($linha['principal']) && $linha['principal']): ?>
                                            <span style="color: #1976d2; font-weight: 600;">⭐ PRINCIPAL - </span>
                                        <?php endif; ?>
                                        <?= h(formatar_contato($linha)) ?>
                                    </td>
                                    <td class="ramal-col" style="<?php 
                                        echo (isset($linha['emergencia']) && $linha['emergencia']) ? 'color: #d32f2f; font-weight: 700;' : ''; 
                                        echo (isset($linha['principal']) && $linha['principal']) ? 'color: #1976d2; font-weight: 700;' : ''; 
                                    ?>"><?= h($linha['ramal']) ?></td>
                                    <td class="acoes-col">
                                        <button class="btn-copiar <?php 
                                            echo (isset($linha['emergencia']) && $linha['emergencia']) ? 'btn-emergencia' : ''; 
                                            echo (isset($linha['principal']) && $linha['principal']) ? 'btn-principal' : ''; 
                                        ?>" data-ramal="<?= h($linha['ramal']) ?>">
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
                            <div class="card <?php 
                                echo (isset($linha['emergencia']) && $linha['emergencia']) ? 'card-emergencia' : ''; 
                                echo (isset($linha['principal']) && $linha['principal']) ? 'card-principal' : ''; 
                            ?>">
                                <div class="card-header">
                                    <h3 class="card-title"><?= formatar_nome_setor($linha['setor']) ?></h3>
                                    <span class="card-ramal" style="<?php 
                                        echo (isset($linha['emergencia']) && $linha['emergencia']) ? 'color: #d32f2f; background: #ffebee;' : ''; 
                                        echo (isset($linha['principal']) && $linha['principal']) ? 'color: #1976d2; background: #e3f2fd;' : ''; 
                                    ?>"><?= h($linha['ramal']) ?></span>
                                </div>
                                <div class="card-content">
                                    <div class="card-description">
                                        <?php if (isset($linha['emergencia']) && $linha['emergencia']): ?>
                                            <span style="color: #d32f2f; font-weight: 600; display: block; margin-bottom: 4px;">🚨 SOMENTE EMERGÊNCIAS</span>
                                        <?php elseif (isset($linha['principal']) && $linha['principal']): ?>
                                            <span style="color: #1976d2; font-weight: 600; display: block; margin-bottom: 4px;">⭐ PRINCIPAL</span>
                                        <?php endif; ?>
                                        <?= h(formatar_contato($linha)) ?>
                                    </div>
                                    <div class="card-subsetor"><?= h(formatar_sub_setor($linha['sub_setor'] ?? null)) ?></div>
                                </div>
                                <div class="card-actions">
                                    <button class="btn-copiar <?php 
                                        echo (isset($linha['emergencia']) && $linha['emergencia']) ? 'btn-emergencia' : ''; 
                                        echo (isset($linha['principal']) && $linha['principal']) ? 'btn-principal' : ''; 
                                    ?>" data-ramal="<?= h($linha['ramal']) ?>">
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
                                
                                // Garantir que o tipo seja preservado na paginação
                                $params['tipo'] = $tipo_ramal;
                                
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
        window.basePath = '<?= BASE_PATH ?>';
    </script>
    <script src="<?= BASE_PATH ?>/assets/js/main.js"></script>
</body>
</html>
<?php endif; // FIM DO BLOCO IF(!$is_ajax) ?>