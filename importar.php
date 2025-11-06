<?php
require_once __DIR__ . '/config.php';

// Conexão (usando função centralizada)
$conexao = get_db_connection();

echo "<h1>Iniciando importação hierárquica...</h1>";

// Função de conversão de caracteres
function converter_para_utf8_se_preciso($string) {
    return mb_check_encoding($string, 'UTF-8') ? $string : mb_convert_encoding($string, 'UTF-8', 'Windows-1252');
}

// Início do processo
$diretorio = __DIR__;
$arquivos = glob($diretorio . '/*.csv');

foreach ($arquivos as $arquivo) {
    $nomeTabela = pathinfo($arquivo, PATHINFO_FILENAME);
    echo "<hr>Processando: <strong>" . basename($arquivo) . "</strong><br>";

    // --- 1. Criar a tabela com a nova coluna 'sub_setor' ---
    $sqlCreateTable = "
        CREATE TABLE IF NOT EXISTS `$nomeTabela` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `sub_setor` VARCHAR(255) DEFAULT NULL,
            `descricao` VARCHAR(255) DEFAULT NULL,
            `falar_com` VARCHAR(255) DEFAULT NULL,
            `ramal` VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    if (!$conexao->query($sqlCreateTable)) {
        echo "<span style='color: red;'>Erro ao criar tabela $nomeTabela: " . $conexao->error . "</span><br>";
        continue;
    }

    // --- 2. Importar os dados, identificando os sub-setores ---
    if (($handle = fopen($arquivo, "r")) !== FALSE) {
        fgetcsv($handle); // Pula cabeçalho

        $sub_setor_atual = null; // Variável para guardar o sub-setor corrente

        while (($dados = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (empty(array_filter($dados))) { continue; }

            $descricao = isset($dados[0]) ? trim($dados[0]) : '';
            $falar_com = isset($dados[1]) ? trim($dados[1]) : '';
            $ramal     = isset($dados[2]) ? trim($dados[2]) : '';

            // Lógica para identificar um sub-setor:
            // Se a coluna 'ramal' e 'falar_com' estão vazias, mas a 'descricao' não, é um título.
            if (!empty($descricao) && empty($falar_com) && empty($ramal)) {
                $sub_setor_atual = converter_para_utf8_se_preciso($descricao);
                continue; // Pula para a próxima linha, não insere o título como um ramal
            }

            // Converte os dados para UTF-8
            $descricao_final = converter_para_utf8_se_preciso($descricao);
            $falar_com_final = converter_para_utf8_se_preciso($falar_com);
            $ramal_final = converter_para_utf8_se_preciso($ramal);

            // Insere no banco com o sub-setor atual
            $stmt = $conexao->prepare("INSERT INTO `$nomeTabela` (sub_setor, descricao, falar_com, ramal) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $sub_setor_atual, $descricao_final, $falar_com_final, $ramal_final);
            $stmt->execute();
            $stmt->close();
        }
        fclose($handle);
        echo "<span style='color: green;'>Dados importados com sucesso para a tabela <strong>$nomeTabela</strong>!</span><br>";
    }
}

$conexao->close();
echo "<hr><h2>Processo finalizado!</h2>";

?>