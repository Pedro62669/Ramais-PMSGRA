<?php
/**
 * Importa os ramais do arquivo novos_ramais.sql para a tabela centro_administrativo
 * Acesse via navegador quando estiver logado como admin
 */

require_once __DIR__ . '/config.php';
start_app_session();
$csrf_token = get_csrf_token();

// Verifica se é admin
if (empty($_SESSION['is_admin'])) {
	header('Location: ./admin.php');
	exit;
}

$con = get_db_connection();
garantir_tabela_centro_administrativo($con);
$db_info = ['db' => null, 'user' => null];
try {
	$rinfo = $con->query("SELECT DATABASE() AS db, CURRENT_USER() AS user");
	if ($rinfo) {
		$db_info = $rinfo->fetch_assoc() ?: $db_info;
	}
} catch (Throwable $e) {
	// ignora
}

$mensagem = '';
$tipo_mensagem = 'info';
$stats = [
	'total_encontrados' => 0,
	'inseridos' => 0,
	'duplicados' => 0,
	'erros' => 0,
];

function parse_novos_ramais_sql(string $conteudo): array {
	$items = [];
	// Captura tuplas no formato: ('Centro Administrativo','SETOR','NOME','NUMERO')
	preg_match_all("/\\(\\s*'([^']*)'\\s*,\\s*'([^']*)'\\s*,\\s*'([^']*)'\\s*,\\s*'([^']*)'\\s*\\)/u", $conteudo, $m, PREG_SET_ORDER);
	foreach ($m as $row) {
		$local = trim($row[1]);
		$setor = trim($row[2]);
		$nome  = trim($row[3]);
		$numero = trim($row[4]);
		if ($local === '' || $setor === '' || $nome === '' || $numero === '') continue;
		$items[] = [
			'local' => $local,
			'setor' => $setor,
			'nome' => $nome,
			'numero' => $numero,
		];
	}
	return $items;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['importar'])) {
	if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
		$mensagem = '❌ Token CSRF inválido. Recarregue a página e tente novamente.';
		$tipo_mensagem = 'error';
	} else {
	$arquivo = __DIR__ . '/novos_ramais.sql';
	if (!file_exists($arquivo)) {
		$mensagem = '❌ Arquivo novos_ramais.sql não encontrado.';
		$tipo_mensagem = 'error';
	} else {
		$conteudo = file_get_contents($arquivo);
		$itens = parse_novos_ramais_sql($conteudo ?: '');
		$stats['total_encontrados'] = count($itens);

		if ($stats['total_encontrados'] === 0) {
			$mensagem = '⚠️ Nenhum registro foi encontrado no arquivo. Verifique o formato do SQL.';
			$tipo_mensagem = 'error';
		} else {
			$con->begin_transaction();
			try {
				// Checagem de duplicado “leve” (mesma combinação)
				// OBS: não usa get_result() para compatibilidade (mysqlnd pode não estar habilitado)
				$stmt_check = $con->prepare("SELECT id FROM centro_administrativo WHERE sub_setor <=> ? AND falar_com <=> ? AND ramal <=> ? LIMIT 1");
				$stmt_ins = $con->prepare("INSERT INTO centro_administrativo (sub_setor, descricao, falar_com, ramal, emergencia, oculto, principal) VALUES (?, NULL, ?, ?, 0, 0, 0)");

				foreach ($itens as $item) {
					// Mapeamento:
					// - sub_setor: setor (ex.: 'FAZENDA')
					// - falar_com: nome (ex.: 'ADM THAISSA')
					// - ramal: numero (ex.: '3001')
					$sub_setor = $item['setor'];
					$falar_com = $item['nome'];
					$ramal = $item['numero'];

					$stmt_check->bind_param('sss', $sub_setor, $falar_com, $ramal);
					$stmt_check->execute();
					$stmt_check->store_result();
					if ($stmt_check->num_rows > 0) {
						$stats['duplicados']++;
						$stmt_check->free_result();
						continue;
					}
					$stmt_check->free_result();

					$stmt_ins->bind_param('sss', $sub_setor, $falar_com, $ramal);
					if ($stmt_ins->execute()) {
						$stats['inseridos']++;
					} else {
						$stats['erros']++;
					}
				}

				$stmt_check->close();
				$stmt_ins->close();

				$con->commit();
				$mensagem = "✓ Importação concluída. Encontrados: {$stats['total_encontrados']}, inseridos: {$stats['inseridos']}, duplicados: {$stats['duplicados']}, erros: {$stats['erros']}.";
				$tipo_mensagem = $stats['erros'] > 0 ? 'error' : 'success';
			} catch (Throwable $e) {
				$con->rollback();
				$mensagem = '❌ Erro ao importar: ' . $e->getMessage();
				$tipo_mensagem = 'error';
			}
		}
	}
	}
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Importar Centro Administrativo</title>
	<link rel="icon" type="image/png" href="<?= BASE_PATH ?>/ico.png">
	<link rel="apple-touch-icon" href="<?= BASE_PATH ?>/ico.png">
	<link rel="manifest" href="<?= BASE_PATH ?>/manifest.json">
	<link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/styles.css">
	<style>
		body{font-family: 'Inter', Arial, sans-serif;background:#f5f7fa;margin:0;padding:24px}
		.card{max-width:760px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 10px 40px rgba(0,0,0,.08);padding:24px}
		h1{margin:0 0 10px;font-size:20px;color:#1a202c}
		p{margin:0 0 14px;color:#4a5568;line-height:1.6}
		.msg{padding:12px 14px;border-radius:10px;margin:14px 0;border-left:4px solid #2e7d32;background:#e8f5e9;color:#1b5e20}
		.msg.error{border-left-color:#d32f2f;background:#ffebee;color:#b71c1c}
		.msg.info{border-left-color:#1976d2;background:#eef7ff;color:#0b4f6c}
		.actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}
		button{padding:12px 16px;border:0;border-radius:10px;background:#2e7d32;color:#fff;font-weight:700;cursor:pointer}
		a.btn{padding:12px 16px;border-radius:10px;text-decoration:none;background:#edf2f7;color:#2d3748;font-weight:700;border:1px solid #e2e8f0}
		code{background:#f7fafc;border:1px solid #e2e8f0;padding:2px 6px;border-radius:6px}
		.stats{margin-top:10px;font-size:13px;color:#718096}
	</style>
</head>
<body>
	<div class="card">
		<h1>Importar ramais — Centro Administrativo</h1>
		<p style="margin-top: 6px; color:#718096; font-size: 13px;">
			Banco atual: <code><?= h($db_info['db'] ?? '') ?></code> | Usuário: <code><?= h($db_info['user'] ?? '') ?></code>
		</p>
		<p>Este importador lê o arquivo <code>novos_ramais.sql</code> e insere os registros na tabela <code>centro_administrativo</code>.</p>
		<p><strong>Mapeamento:</strong> Setor → Sub-setor, Nome → Falar com, Número → Ramal.</p>

		<?php if (!empty($mensagem)): ?>
			<div class="msg <?= h($tipo_mensagem) ?>"><?= h($mensagem) ?></div>
			<?php if ($stats['total_encontrados'] > 0): ?>
				<div class="stats">
					Encontrados: <?= (int)$stats['total_encontrados'] ?> |
					Inseridos: <?= (int)$stats['inseridos'] ?> |
					Duplicados: <?= (int)$stats['duplicados'] ?> |
					Erros: <?= (int)$stats['erros'] ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<div class="actions">
			<form method="post" action="./importar_centro_administrativo.php">
				<input type="hidden" name="importar" value="1">
				<input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">
				<button type="submit">📥 Importar agora</button>
			</form>
			<a class="btn" href="./admin.php">← Voltar ao Painel</a>
			<a class="btn" href="./index.php?tipo=centro">Ver na Home</a>
		</div>
	</div>
</body>
</html>

