<?php
// Desabilitar exibição de erros em produção
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
session_start();

// =================== CONFIGURAÇÕES ===================
require_once __DIR__ . '/config.php';
// =====================================================

// DETECTA SE A REQUISIÇÃO É AJAX (CHAVE PARA O LIVE SEARCH)
$is_ajax = isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1;

// Configurações de paginação
$itens_por_pagina = 20;
$pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

// Conexão com o banco de dados
$conexao = get_db_connection();

// Garante que a tabela existe
garantir_tabela_emails($conexao);

// Processa busca e filtros
$termo_busca = trim($_GET['busca'] ?? '');
$setor_busca = $_GET['setor'] ?? 'todos';
$lista_setores = obter_setores_emails($conexao);

// Monta query SQL
$where_conditions = [];
$params = [];
$types = '';

if ($setor_busca != 'todos') {
    $where_conditions[] = "setor = ?";
    $params[] = $setor_busca;
    $types .= 's';
}

if (!empty($termo_busca)) {
    $where_conditions[] = "(email LIKE ? OR setor LIKE ?)";
    $like_termo = "%" . $termo_busca . "%";
    $params[] = $like_termo;
    $params[] = $like_termo;
    $types .= 'ss';
}

$where_clause = !empty($where_conditions) ? " WHERE " . implode(" AND ", $where_conditions) : "";

// Conta total de resultados
$sql_count = "SELECT COUNT(*) AS total FROM emails" . $where_clause;
$stmt_count = $conexao->prepare($sql_count);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_resultados = intval($stmt_count->get_result()->fetch_assoc()['total'] ?? 0);
$total_paginas = ($total_resultados > 0) ? ceil($total_resultados / $itens_por_pagina) : 0;
$stmt_count->close();

// Busca resultados paginados
$emails_paginados = [];
if ($total_resultados > 0) {
    $sql = "SELECT id, setor, email FROM emails" . $where_clause . " ORDER BY setor, email LIMIT ? OFFSET ?";
    $stmt = $conexao->prepare($sql);
    
    if (!empty($params)) {
        $types_paginado = $types . 'ii';
        $params_paginado = array_merge($params, [$itens_por_pagina, $offset]);
        $stmt->bind_param($types_paginado, ...$params_paginado);
    } else {
        $stmt->bind_param('ii', $itens_por_pagina, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $emails_paginados[] = [
            'id' => $row['id'],
            'setor' => $row['setor'],
            'email' => $row['email']
        ];
    }
    $stmt->close();
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
    <title>Consulta de Emails</title>
    <link rel="manifest" href="./manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/styles.css">
    <style>
        .email-link {
            color: #1976d2;
            text-decoration: none;
        }
        .email-link:hover {
            text-decoration: underline;
        }
        .email-col {
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-container">
            <div class="logo-section">
                <img src="logo-sgra2.png" alt="Logo São Gonçalo do Rio Abaixo" class="logo">
                <h1>Consulta de Emails</h1>
            </div>
            <div class="header-actions">
                <a href="./index.php">Ramais</a>
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
                    <input type="text" id="busca-input" name="busca" placeholder="Digite o email ou setor..." value="<?= h($termo_busca) ?>">
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
                <?php if (!empty($emails_paginados)): ?>
                    <div class="results-info">
                        Mostrando <?= count($emails_paginados) ?> de <?= $total_resultados ?> resultados
                        (Página <?= $pagina_atual ?> de <?= $total_paginas ?>)
                    </div>
                    
                    <!-- Desktop Table -->
                    <table class="results-table desktop-table">
                        <thead>
                            <tr>
                                <th class="setor-col">Setor</th>
                                <th class="email-col">Email</th>
                                <th class="acoes-col">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($emails_paginados as $item): ?>
                                <tr>
                                    <td class="setor-col"><?= formatar_nome_setor($item['setor']) ?></td>
                                    <td class="email-col">
                                        <a href="mailto:<?= h($item['email']) ?>" class="email-link">
                                            <?= h($item['email']) ?>
                                        </a>
                                    </td>
                                    <td class="acoes-col">
                                        <button class="btn-copiar" data-email="<?= h($item['email']) ?>">
                                            📋 Copiar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Mobile Cards -->
                    <div class="mobile-card">
                        <?php foreach ($emails_paginados as $item): ?>
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title"><?= formatar_nome_setor($item['setor']) ?></h3>
                                </div>
                                <div class="card-content">
                                    <div class="card-description">
                                        <a href="mailto:<?= h($item['email']) ?>" class="email-link">
                                            <?= h($item['email']) ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <button class="btn-copiar" data-email="<?= h($item['email']) ?>">
                                        📋 Copiar Email
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
        /**
         * Script para busca de emails e interações
         */
        (function() {
            'use strict';
            
            const elements = {
                buscaInput: null,
                setorSelect: null,
                contentContainer: null
            };
            
            let debounceTimer = null;
            const DEBOUNCE_DELAY = 400;
            
            function init() {
                elements.buscaInput = document.getElementById('busca-input');
                elements.setorSelect = document.getElementById('setor-select');
                elements.contentContainer = document.getElementById('content-container');
                
                if (!elements.buscaInput || !elements.setorSelect || !elements.contentContainer) {
                    console.warn('Elementos necessários não encontrados');
                    return;
                }
                
                setupEventListeners();
            }
            
            function setupEventListeners() {
                elements.buscaInput.addEventListener('keyup', handleSearchInput);
                elements.setorSelect.addEventListener('change', () => performSearch(1));
                document.addEventListener('click', handleDocumentClick);
            }
            
            function handleSearchInput() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    performSearch(1);
                }, DEBOUNCE_DELAY);
            }
            
            function performSearch(page = 1) {
                const termoBusca = elements.buscaInput.value.trim();
                const setorBusca = elements.setorSelect.value;
                
                elements.contentContainer.classList.add('loading');
                
                const params = new URLSearchParams({
                    busca: termoBusca,
                    setor: setorBusca,
                    pagina: page,
                    ajax: 1
                });
                
                const url = window.location.pathname + '?' + params.toString();
                window.history.pushState({ path: url }, '', url);
                
                fetch(url)
                    .then(response => {
                        if (!response.ok) throw new Error('Erro na resposta do servidor');
                        return response.text();
                    })
                    .then(html => {
                        elements.contentContainer.innerHTML = html;
                        elements.contentContainer.classList.remove('loading');
                    })
                    .catch(error => {
                        console.error('Erro na busca:', error);
                        elements.contentContainer.innerHTML = '<p class="no-results">Ocorreu um erro ao buscar. Tente novamente.</p>';
                        elements.contentContainer.classList.remove('loading');
                    });
            }
            
            function handleDocumentClick(e) {
                const pageLink = e.target.closest('.pagination a');
                if (pageLink) {
                    e.preventDefault();
                    const url = new URL(pageLink.href);
                    const page = parseInt(url.searchParams.get('pagina') || '1', 10);
                    performSearch(page);
                    return;
                }
                
                const copyButton = e.target.closest('.btn-copiar');
                if (copyButton) {
                    handleCopyEmail(copyButton);
                }
            }
            
            function handleCopyEmail(button) {
                const email = button.getAttribute('data-email') || '';
                const originalText = button.innerText;
                
                button.innerText = 'Copiado!';
                button.classList.add('copiado');
                
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(email)
                        .then(() => {
                            resetCopyButton(button, originalText);
                        })
                        .catch(() => {
                            fallbackCopyTextToClipboard(email, button, originalText);
                        });
                } else {
                    fallbackCopyTextToClipboard(email, button, originalText);
                }
            }
            
            function resetCopyButton(button, originalText) {
                setTimeout(() => {
                    button.innerText = originalText;
                    button.classList.remove('copiado');
                }, 1500);
            }
            
            function fallbackCopyTextToClipboard(text, button, originalText) {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.cssText = 'position:fixed;top:0;left:0;opacity:0;';
                
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    const successful = document.execCommand('copy');
                    if (successful) {
                        resetCopyButton(button, originalText);
                    } else {
                        alert('Não foi possível copiar o email.');
                        button.innerText = originalText;
                        button.classList.remove('copiado');
                    }
                } catch (err) {
                    alert('Não foi possível copiar o email.');
                    button.innerText = originalText;
                    button.classList.remove('copiado');
                }
                
                document.body.removeChild(textArea);
            }
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
</body>
</html>
<?php endif; // FIM DO BLOCO IF(!$is_ajax) ?>

