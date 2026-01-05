<?php
// api.php

// Define um cabeçalho de resposta como JSON
header('Content-Type: application/json');

// Inclui o arquivo de configuração e funções do banco de dados
require_once __DIR__ . '/config.php';

// Inicia a sessão para verificar a autenticação do administrador
session_start();

// Garante que apenas administradores autenticados possam acessar esta API
if (empty($_SESSION['is_admin'])) {
	// Se não for admin, retorna um erro 403 (Proibido) e encerra o script
	http_response_code(403);
	echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
	exit;
}

// Verifica se a requisição é do tipo POST, que é o método esperado para alterações
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	// Se não for POST, retorna um erro 405 (Método não permitido)
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
	exit;
}

// Obtém e decodifica os dados JSON enviados no corpo da requisição
$data = json_decode(file_get_contents('php://input'), true);

// Validação básica dos dados recebidos
if (
	!isset($data['tabela'], $data['id'], $data['field'], $data['value']) ||
	!is_string($data['tabela']) ||
	!is_numeric($data['id']) ||
	!is_string($data['field']) ||
	!is_bool($data['value'])
) {
	// Se dados essenciais estiverem faltando ou forem do tipo incorreto, retorna erro 400 (Requisição inválida)
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
	exit;
}

// Atribui os dados a variáveis locais para facilitar o uso
$tabela = $data['tabela'];
$id = (int)$data['id'];
$field = $data['field'];
$value = $data['value'];

// Lista dos campos permitidos para alteração para evitar injeção de SQL
$allowed_fields = ['emergencia', 'oculto', 'principal'];
if (!in_array($field, $allowed_fields, true)) {
	// Se o campo não for permitido, retorna erro 400
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'Campo não permitido para atualização.']);
	exit;
}

// Conecta-se ao banco de dados
$con = get_db_connection();

// Inicia uma transação para garantir a integridade dos dados
// Se algo der errado, todas as alterações são desfeitas
$con->begin_transaction();

try {
	// Primeiro, desmarca todos os outros campos de status se o valor for 'true'
	// Isso garante que apenas um status (emergência, oculto ou principal) possa estar ativo por vez
	if ($value) {
		foreach ($allowed_fields as $f) {
			if ($f !== $field) {
				// Prepara a query para desmarcar os outros campos
				$stmt = $con->prepare("UPDATE `$tabela` SET `$f` = 0 WHERE `id` = ?");
				if (!$stmt) throw new Exception('Erro ao preparar query de limpeza: ' . $con->error);
				$stmt->bind_param('i', $id);
				$stmt->execute();
				$stmt->close();
			}
		}
	}

	// Agora, atualiza o campo que foi efetivamente alterado pelo usuário
	$stmt = $con->prepare("UPDATE `$tabela` SET `$field` = ? WHERE `id` = ?");
	if (!$stmt) throw new Exception('Erro ao preparar query de atualização: ' . $con->error);
	$update_value = $value ? 1 : 0; // Converte o booleano para inteiro (1 ou 0)
	$stmt->bind_param('ii', $update_value, $id);
	$stmt->execute();

	// Verifica se a atualização foi bem-sucedida (se alguma linha foi afetada)
	if ($stmt->affected_rows === 0) {
		// Se nenhuma linha foi alterada, pode ser que o registro não exista
		throw new Exception('Nenhum registro encontrado com o ID fornecido.');
	}
	$stmt->close();

	// Se tudo correu bem, confirma as alterações no banco de dados
	$con->commit();

	// Retorna uma resposta de sucesso para o cliente
	echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso.']);

} catch (Exception $e) {
	// Se qualquer erro ocorreu durante a transação, desfaz todas as alterações
	$con->rollback();
	// Retorna um erro 500 (Erro interno do servidor) com a mensagem de erro
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}
