<?php
/**
 * Script de migração: emails.html -> banco de dados
 * Execute este script uma vez para migrar os dados do arquivo emails.html para o banco de dados
 */

require_once __DIR__ . '/config.php';

// Verifica se é admin
session_start();
if (empty($_SESSION['is_admin'])) {
    die('Acesso negado. É necessário estar logado como administrador.');
}

$con = get_db_connection();

// Verifica se a tabela existe, se não, cria
$result = $con->query("SHOW TABLES LIKE 'emails'");
if ($result->num_rows == 0) {
    $con->query("CREATE TABLE `emails` (
        `id` int NOT NULL AUTO_INCREMENT,
        `setor` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
        `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_email_setor` (`setor`, `email`),
        KEY `idx_setor` (`setor`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    echo "✓ Tabela 'emails' criada.\n";
}

// Verifica se já existem dados
$result = $con->query("SELECT COUNT(*) as total FROM emails");
$total_existente = $result->fetch_assoc()['total'];

if ($total_existente > 0) {
    echo "⚠️ A tabela já possui $total_existente registros.\n";
    echo "Deseja continuar mesmo assim? (s/n): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    if (strtolower($line) !== 's') {
        die("Migração cancelada.\n");
    }
}

// Carrega dados do arquivo HTML (função antiga)
$arquivo = __DIR__ . '/emails.html';
if (!file_exists($arquivo)) {
    die("❌ Arquivo emails.html não encontrado.\n");
}

$conteudo = file_get_contents($arquivo);
$emails_importados = 0;
$emails_duplicados = 0;

// Regex para encontrar divs com classes de setores e seus emails
preg_match_all('/<div\s+class\s*=\s*["\']([^"\']+)["\']>.*?<ul>(.*?)<\/ul>.*?<\/div>/s', $conteudo, $matches, PREG_SET_ORDER);

$con->begin_transaction();

try {
    foreach ($matches as $match) {
        $setor = trim($match[1]);
        $ul_content = $match[2];
        
        // Extrai os emails da lista
        preg_match_all('/<li>(.*?)<\/li>/', $ul_content, $email_matches);
        
        foreach ($email_matches[1] as $email) {
            $email_limpo = trim(strip_tags($email));
            if (!empty($email_limpo)) {
                // Tenta inserir
                $stmt = $con->prepare("INSERT IGNORE INTO emails (setor, email) VALUES (?, ?)");
                $stmt->bind_param('ss', $setor, $email_limpo);
                
                if ($stmt->execute()) {
                    if ($con->affected_rows > 0) {
                        $emails_importados++;
                    } else {
                        $emails_duplicados++;
                    }
                }
                $stmt->close();
            }
        }
    }
    
    $con->commit();
    
    echo "\n✅ Migração concluída!\n";
    echo "   - Emails importados: $emails_importados\n";
    echo "   - Emails duplicados (ignorados): $emails_duplicados\n";
    echo "\n💡 Você pode agora deletar o arquivo emails.html se desejar.\n";
    
} catch (Exception $e) {
    $con->rollback();
    echo "❌ Erro na migração: " . $e->getMessage() . "\n";
}

$con->close();

