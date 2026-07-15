<?php
// sugestao.php — Endpoint público para receber sugestões de melhorias enviadas pelos usuários.

header('Content-Type: application/json');

require_once __DIR__ . '/config.php';
start_app_session();

// Apenas POST é aceito
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
	exit;
}

// Aceita tanto JSON quanto form-urlencoded
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
	$data = $_POST;
}

// Proteção CSRF (o token é gerado na página inicial pública)
if (!verify_csrf_token($data['csrf_token'] ?? null)) {
	http_response_code(403);
	echo json_encode(['success' => false, 'message' => 'Sessão expirada. Recarregue a página e tente novamente.']);
	exit;
}

$mensagem = (string)($data['mensagem'] ?? '');
$nome = (string)($data['nome'] ?? '');

$resultado = salvar_sugestao($mensagem, $nome);

if (!$resultado['success']) {
	http_response_code(400);
}
echo json_encode($resultado);
