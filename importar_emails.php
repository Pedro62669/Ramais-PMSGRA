<?php
/**
 * Script de importação de emails do arquivo HTML para o banco de dados
 * Acesse via navegador quando estiver logado como admin
 */

session_start();
require_once __DIR__ . '/config.php';

// Verifica se é admin
if (empty($_SESSION['is_admin'])) {
    header('Location: ./admin.php');
    exit;
}

$con = get_db_connection();
garantir_tabela_emails($con);

$mensagem = '';
$tipo_mensagem = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['importar'])) {
    // Carrega dados do arquivo HTML
    $arquivo = __DIR__ . '/emails.html';
    if (!file_exists($arquivo)) {
        $mensagem = '❌ Arquivo emails.html não encontrado.';
        $tipo_mensagem = 'error';
    } else {
        $conteudo = file_get_contents($arquivo);
        $emails_importados = 0;
        $emails_duplicados = 0;
        $emails_erro = 0;
        
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
                        } else {
                            $emails_erro++;
                        }
                        $stmt->close();
                    }
                }
            }
            
            $con->commit();
            
            $mensagem = "✅ Importação concluída!<br>";
            $mensagem .= "   • Emails importados: <strong>$emails_importados</strong><br>";
            $mensagem .= "   • Emails duplicados (ignorados): <strong>$emails_duplicados</strong><br>";
            if ($emails_erro > 0) {
                $mensagem .= "   • Erros: <strong>$emails_erro</strong><br>";
            }
            $tipo_mensagem = 'success';
            
        } catch (Exception $e) {
            $con->rollback();
            $mensagem = '❌ Erro na importação: ' . h($e->getMessage());
            $tipo_mensagem = 'error';
        }
    }
}

// Verifica quantos emails já existem
$result = $con->query("SELECT COUNT(*) as total FROM emails");
$total_existente = $result->fetch_assoc()['total'];
$con->close();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Emails - Admin</title>
    <link rel="stylesheet" href="./assets/css/styles.css">
    <style>
        body {
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }
        .msg {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-weight: 500;
        }
        .msg.success {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            color: #1b5e20;
            border-left: 4px solid #2e7d32;
        }
        .msg.error {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            color: #b71c1c;
            border-left: 4px solid #d32f2f;
        }
        .msg.info {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1565c0;
            border-left: 4px solid #1976d2;
        }
        button {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            color: white;
            margin-top: 16px;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.4);
        }
        .info-box {
            background: #f8fafc;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            border-left: 4px solid #2e7d32;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Importar Emails do Arquivo HTML</h1>
        
        <?php if (!empty($mensagem)): ?>
            <div class="msg <?php echo $tipo_mensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <p><strong>Status atual:</strong></p>
            <p>Emails no banco de dados: <strong><?php echo $total_existente; ?></strong></p>
            <?php if ($total_existente > 0): ?>
                <p style="color: #718096; font-size: 14px; margin-top: 8px;">
                    ⚠️ A tabela já possui registros. Emails duplicados serão ignorados automaticamente.
                </p>
            <?php endif; ?>
        </div>
        
        <form method="post">
            <p>Este script irá importar todos os emails do arquivo <code>emails.html</code> para o banco de dados.</p>
            <button type="submit" name="importar" value="1">📥 Importar Emails</button>
        </form>
        
        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e2e8f0;">
            <a href="./admin.php" style="color: #2e7d32; text-decoration: none; font-weight: 500;">← Voltar ao Painel Admin</a>
        </div>
    </div>
</body>
</html>

