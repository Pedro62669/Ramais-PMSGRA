<?php
// Centraliza credenciais do banco. Ajuste via variáveis de ambiente para produção
// Ex.: export DB_HOST=localhost DB_USER=user DB_PASS=secret DB_NAME=ramais

function get_db_credentials(): array {
	$host = getenv('DB_HOST') ?: 'localhost';
	$user = getenv('DB_USER') ?: 'pmsgra';
	$pass = getenv('DB_PASS') ?: 'Pmsgra#ti2024';
	$name = getenv('DB_NAME') ?: 'ramais';
	return [$host, $user, $pass, $name];
}

function get_db_connection(): mysqli {
	[$host, $user, $pass, $name] = get_db_credentials();
	$con = new mysqli($host, $user, $pass, $name);
	if ($con->connect_error) {
		die('Falha na conexão: ' . $con->connect_error);
	}
	$con->set_charset('utf8mb4');
	return $con;
}

/**
 * Formata o nome do setor para exibição (remove underscores e capitaliza)
 * @param string $setor Nome do setor (ex: "acao_social")
 * @return string Nome formatado (ex: "Acao social")
 */
function formatar_nome_setor(string $setor): string {
	return ucfirst(str_replace('_', ' ', $setor));
}

/**
 * Formata o texto de contato combinando falar_com e descricao
 * @param array $linha Array com as chaves 'falar_com' e 'descricao'
 * @return string Texto formatado do contato
 */
function formatar_contato(array $linha): string {
	$falar_com = trim($linha['falar_com'] ?? '');
	$descricao = trim($linha['descricao'] ?? '');
	
	if (!empty($falar_com) && !empty($descricao)) {
		return $falar_com . ' (' . $descricao . ')';
	} elseif (!empty($falar_com)) {
		return $falar_com;
	} else {
		return $descricao;
	}
}

/**
 * Formata o sub-setor com fallback para 'Geral' se vazio
 * @param string|null $sub_setor Sub-setor ou null
 * @return string Sub-setor formatado ou 'Geral'
 */
function formatar_sub_setor(?string $sub_setor): string {
	return !empty($sub_setor) ? $sub_setor : 'Geral';
}

/**
 * Escapa string para HTML de forma segura
 * @param string|null $string String a ser escapada
 * @return string String escapada
 */
function h(?string $string): string {
	return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Transfere um ramal de um setor para outro
 * @param mysqli $con Conexão com o banco de dados
 * @param string $tabela_origem Tabela de origem
 * @param string $tabela_destino Tabela de destino
 * @param int $id ID do registro a ser transferido
 * @return array ['success' => bool, 'message' => string]
 */
function transferir_ramal(mysqli $con, string $tabela_origem, string $tabela_destino, int $id): array {
	// Validação básica
	if ($tabela_origem === $tabela_destino) {
		return ['success' => false, 'message' => 'O setor de origem e destino são iguais'];
	}
	
	// Buscar o registro na tabela origem
	$stmt = $con->prepare("SELECT sub_setor, descricao, falar_com, ramal FROM `$tabela_origem` WHERE id = ?");
	$stmt->bind_param('i', $id);
	$stmt->execute();
	$resultado = $stmt->get_result();
	
	if ($resultado->num_rows === 0) {
		$stmt->close();
		return ['success' => false, 'message' => 'Registro não encontrado'];
	}
	
	$registro = $resultado->fetch_assoc();
	$stmt->close();
	
	// Iniciar transação para garantir atomicidade
	$con->begin_transaction();
	
	try {
		// Inserir na tabela destino
		$stmt_insert = $con->prepare("INSERT INTO `$tabela_destino` (sub_setor, descricao, falar_com, ramal) VALUES (?, ?, ?, ?)");
		$stmt_insert->bind_param('ssss', 
			$registro['sub_setor'], 
			$registro['descricao'], 
			$registro['falar_com'], 
			$registro['ramal']
		);
		$stmt_insert->execute();
		$stmt_insert->close();
		
		// Remover da tabela origem
		$stmt_delete = $con->prepare("DELETE FROM `$tabela_origem` WHERE id = ?");
		$stmt_delete->bind_param('i', $id);
		$stmt_delete->execute();
		$stmt_delete->close();
		
		// Confirmar transação
		$con->commit();
		
		return ['success' => true, 'message' => 'Ramal transferido com sucesso'];
	} catch (Exception $e) {
		// Reverter em caso de erro
		$con->rollback();
		return ['success' => false, 'message' => 'Erro ao transferir: ' . $e->getMessage()];
	}
}


