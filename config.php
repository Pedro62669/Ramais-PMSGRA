<?php
// Define o caminho base da aplicação para links de assets
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('BASE_PATH', $base_path === '/' ? '' : $base_path);

// Centraliza credenciais do banco. Ajuste via variáveis de ambiente para produção
// Ex.: export DB_HOST=localhost DB_USER=user DB_PASS=secret DB_NAME=ramais_v2

/**
 * Detecta se a requisição está em HTTPS (inclui cenários atrás de proxy).
 */
function is_https_request(): bool {
	if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
	if (!empty($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443') return true;
	if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') return true;
	return false;
}

/**
 * Inicia a sessão com configurações seguras (compatível com HTTP local e mais rígido em HTTPS).
 * Chame isso no lugar de session_start().
 */
function start_app_session(): void {
	if (session_status() === PHP_SESSION_ACTIVE) return;

	// Endurecimento básico
	ini_set('session.use_strict_mode', '1');
	ini_set('session.use_only_cookies', '1');
	ini_set('session.cookie_httponly', '1');

	$secure = is_https_request();

	// PHP 7.3+ suporta array com samesite
	session_set_cookie_params([
		'lifetime' => 0,
		'path' => '/',
		'domain' => '',
		'secure' => $secure,
		'httponly' => true,
		'samesite' => 'Lax',
	]);

	session_start();
}

/**
 * CSRF token (para Admin/API).
 */
function get_csrf_token(): string {
	start_app_session();
	if (empty($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
		$_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
	}
	return $_SESSION['_csrf_token'];
}

function verify_csrf_token(?string $token): bool {
	start_app_session();
	if (!is_string($token) || $token === '') return false;
	$expected = $_SESSION['_csrf_token'] ?? '';
	return is_string($expected) && $expected !== '' && hash_equals($expected, $token);
}

function get_db_credentials(): array {
	$host = getenv('DB_HOST') ?: 'localhost';
	$user = getenv('DB_USER') ?: 'pmsgra';
	$pass = getenv('DB_PASS') ?: 'Pmsgra#ti2024';
	$name = getenv('DB_NAME') ?: 'ramais_v2';
	return [$host, $user, $pass, $name];
}

// Cache para conexão (reutilização de conexão)
$GLOBALS['_db_connection'] = null;

function get_db_connection(): mysqli {
	if ($GLOBALS['_db_connection'] !== null && $GLOBALS['_db_connection']->ping()) {
		return $GLOBALS['_db_connection'];
	}
	
	[$host, $user, $pass, $name] = get_db_credentials();
	$con = new mysqli($host, $user, $pass, $name);
	if ($con->connect_error) {
		error_log('Falha na conexão com o banco: ' . $con->connect_error);
		die('Falha na conexão: ' . $con->connect_error);
	}
	$con->set_charset('utf8mb4');
	$GLOBALS['_db_connection'] = $con;
	return $con;
}

// Cache para lista de setores (evita múltiplas queries SHOW TABLES)
const SETORES_CACHE_TTL = 300; // 5 minutos

/**
 * Busca lista de setores com cache
 * @param mysqli|null $conexao Conexão opcional (se não fornecida, cria nova)
 * @return array Lista de setores
 */
function get_lista_setores(?mysqli $conexao = null): array {
	// Verifica cache
	$now = time();
	if (isset($GLOBALS['_cached_setores']) && isset($GLOBALS['_cache_timestamp'])) {
		if (($now - $GLOBALS['_cache_timestamp']) < SETORES_CACHE_TTL) {
			return $GLOBALS['_cached_setores'];
		}
	}
	
	// Busca do banco
	if ($conexao === null) {
		$conexao = get_db_connection();
	}
	
	$tabelas = [];
	$resultado = $conexao->query("SHOW TABLES");
	if ($resultado) {
		while ($linha = $resultado->fetch_array()) {
			if ($linha[0] !== 'importar' && $linha[0] !== 'consulta') {
				$tabelas[] = $linha[0];
			}
		}
		$resultado->free();
	}
	
	// Atualiza cache
	$GLOBALS['_cached_setores'] = $tabelas;
	$GLOBALS['_cache_timestamp'] = $now;
	
	return $tabelas;
}

/**
 * Limpa o cache de setores (útil após alterações)
 */
function limpar_cache_setores(): void {
	unset($GLOBALS['_cached_setores'], $GLOBALS['_cache_timestamp']);
}

/**
 * Formata o nome do setor para exibição (remove underscores e capitaliza)
 * @param string $setor Nome do setor (ex: "acao_social")
 * @return string Nome formatado (ex: "Acao social")
 */
function formatar_nome_setor(string $setor): string {
	// Nomes “especiais” (melhor UX)
	if ($setor === 'centro_administrativo') return 'Centro Administrativo';
	if ($setor === 'externos') return 'Externos';
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
	
	// Verificar se é tentativa de transferir de interno para externo
	$origem_e_externo = ($tabela_origem === 'externos');
	$destino_e_externo = ($tabela_destino === 'externos');
	
	// Ramais internos não podem ser transferidos para externos
	if (!$origem_e_externo && $destino_e_externo) {
		return ['success' => false, 'message' => 'Ramais internos não podem ser transferidos para ramais externos'];
	}
	
	// Buscar o registro na tabela origem
	$stmt = $con->prepare("SELECT sub_setor, descricao, falar_com, ramal, emergencia, oculto, principal FROM `$tabela_origem` WHERE id = ?");
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
		$stmt_insert = $con->prepare("INSERT INTO `$tabela_destino` (sub_setor, descricao, falar_com, ramal, emergencia, oculto, principal) VALUES (?, ?, ?, ?, ?, ?, ?)");
		$stmt_insert->bind_param('ssssiii', 
			$registro['sub_setor'], 
			$registro['descricao'], 
			$registro['falar_com'], 
			$registro['ramal'],
			$registro['emergencia'] ?? 0,
			$registro['oculto'] ?? 0,
			$registro['principal'] ?? 0
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

/**
 * Verifica e cria a tabela de emails se não existir
 * @param mysqli|null $conexao Conexão opcional
 */
function garantir_tabela_emails(?mysqli $conexao = null): void {
	if ($conexao === null) {
		$conexao = get_db_connection();
	}
	
	$result = $conexao->query("SHOW TABLES LIKE 'emails'");
	if ($result->num_rows == 0) {
		$conexao->query("CREATE TABLE IF NOT EXISTS `emails` (
			`id` int NOT NULL AUTO_INCREMENT,
			`setor` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
			`email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
			`created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
			`updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `unique_email_setor` (`setor`, `email`),
			KEY `idx_setor` (`setor`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
	}
}

/**
 * Carrega emails do banco de dados
 * @param mysqli|null $conexao Conexão opcional
 * @return array Array com emails organizados por setor
 */
function carregar_emails_arquivo(?mysqli $conexao = null): array {
	if ($conexao === null) {
		$conexao = get_db_connection();
	}
	
	garantir_tabela_emails($conexao);
	
	$emails = [];
	$stmt = $conexao->prepare("SELECT id, setor, email FROM emails ORDER BY setor, email");
	$stmt->execute();
	$result = $stmt->get_result();
	
	while ($row = $result->fetch_assoc()) {
		$emails[] = [
			'id' => $row['id'],
			'setor' => $row['setor'],
			'email' => $row['email']
		];
	}
	
	$stmt->close();
	return $emails;
}

/**
 * Salva um email no banco de dados
 * @param string $setor Nome do setor
 * @param string $email Endereço de email
 * @param mysqli|null $conexao Conexão opcional
 * @return array ['success' => bool, 'message' => string, 'id' => int|null]
 */
function salvar_email_banco(string $setor, string $email, ?mysqli $conexao = null): array {
	if ($conexao === null) {
		$conexao = get_db_connection();
	}
	
	garantir_tabela_emails($conexao);
	
	// Verifica se já existe
	$stmt = $conexao->prepare("SELECT id FROM emails WHERE setor = ? AND email = ?");
	$stmt->bind_param('ss', $setor, $email);
	$stmt->execute();
	$result = $stmt->get_result();
	
	if ($result->num_rows > 0) {
		$stmt->close();
		return ['success' => false, 'message' => 'Este email já existe neste setor', 'id' => null];
	}
	$stmt->close();
	
	// Insere o email
	$stmt = $conexao->prepare("INSERT INTO emails (setor, email) VALUES (?, ?)");
	$stmt->bind_param('ss', $setor, $email);
	
	if ($stmt->execute()) {
		$id = $conexao->insert_id;
		$stmt->close();
		return ['success' => true, 'message' => 'Email adicionado com sucesso', 'id' => $id];
	} else {
		$error = $stmt->error;
		$stmt->close();
		return ['success' => false, 'message' => 'Erro ao adicionar email: ' . $error, 'id' => null];
	}
}

/**
 * Atualiza um email no banco de dados
 * @param int $id ID do email
 * @param string $setor Novo setor
 * @param string $email Novo email
 * @param mysqli|null $conexao Conexão opcional
 * @return array ['success' => bool, 'message' => string]
 */
function atualizar_email_banco(int $id, string $setor, string $email, ?mysqli $conexao = null): array {
	if ($conexao === null) {
		$conexao = get_db_connection();
	}
	
	garantir_tabela_emails($conexao);
	
	// Verifica se já existe outro registro com o mesmo setor e email
	$stmt = $conexao->prepare("SELECT id FROM emails WHERE setor = ? AND email = ? AND id != ?");
	$stmt->bind_param('ssi', $setor, $email, $id);
	$stmt->execute();
	$result = $stmt->get_result();
	
	if ($result->num_rows > 0) {
		$stmt->close();
		return ['success' => false, 'message' => 'Este email já existe neste setor'];
	}
	$stmt->close();
	
	// Atualiza o email
	$stmt = $conexao->prepare("UPDATE emails SET setor = ?, email = ? WHERE id = ?");
	$stmt->bind_param('ssi', $setor, $email, $id);
	
	if ($stmt->execute()) {
		$stmt->close();
		return ['success' => true, 'message' => 'Email atualizado com sucesso'];
	} else {
		$error = $stmt->error;
		$stmt->close();
		return ['success' => false, 'message' => 'Erro ao atualizar email: ' . $error];
	}
}

/**
 * Remove um email do banco de dados
 * @param int $id ID do email
 * @param mysqli|null $conexao Conexão opcional
 * @return array ['success' => bool, 'message' => string]
 */
function remover_email_banco(int $id, ?mysqli $conexao = null): array {
	if ($conexao === null) {
		$conexao = get_db_connection();
	}
	
	garantir_tabela_emails($conexao);
	
	$stmt = $conexao->prepare("DELETE FROM emails WHERE id = ?");
	$stmt->bind_param('i', $id);
	
	if ($stmt->execute()) {
		$stmt->close();
		return ['success' => true, 'message' => 'Email removido com sucesso'];
	} else {
		$error = $stmt->error;
		$stmt->close();
		return ['success' => false, 'message' => 'Erro ao remover email: ' . $error];
	}
}

/**
 * Obtém lista de setores únicos dos emails do banco de dados
 * @param mysqli|null $conexao Conexão opcional
 * @return array Lista de setores
 */
function obter_setores_emails(?mysqli $conexao = null): array {
	if ($conexao === null) {
		$conexao = get_db_connection();
	}
	
	garantir_tabela_emails($conexao);
	
	$setores = [];
	$stmt = $conexao->prepare("SELECT DISTINCT setor FROM emails ORDER BY setor");
	$stmt->execute();
	$result = $stmt->get_result();
	
	while ($row = $result->fetch_assoc()) {
		$setores[] = $row['setor'];
	}
	
	$stmt->close();
	return $setores;
}

/**
 * Garante que a tabela de Centro Administrativo exista (vazia por padrão).
 * Mantém o schema compatível com as tabelas de ramais existentes.
 */
function garantir_tabela_centro_administrativo(?mysqli $conexao = null): void {
	if ($conexao === null) {
		$conexao = get_db_connection();
	}

	$result = $conexao->query("SHOW TABLES LIKE 'centro_administrativo'");
	if ($result && $result->num_rows == 0) {
		$conexao->query("CREATE TABLE IF NOT EXISTS `centro_administrativo` (
			`id` int NOT NULL AUTO_INCREMENT,
			`sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
			`descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
			`falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
			`ramal` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
			`emergencia` tinyint(1) NOT NULL DEFAULT 0,
			`oculto` tinyint(1) NOT NULL DEFAULT 0,
			`principal` tinyint(1) NOT NULL DEFAULT 0,
			PRIMARY KEY (`id`),
			KEY `idx_ramal` (`ramal`),
			KEY `idx_sub_setor` (`sub_setor`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
	}
}


