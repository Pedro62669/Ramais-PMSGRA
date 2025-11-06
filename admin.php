<?php
session_start();
// Evitar cache para a página de login/painel
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
require_once __DIR__ . '/config.php';

// Autenticação simples via POST (usuário/senha do ambiente)
$admin_user = getenv('ADMIN_USER') ?: 'pedro';
$admin_pass = getenv('ADMIN_PASS') ?: 'DK0104';

if (isset($_POST['action']) && $_POST['action'] === 'login') {
	$user = trim($_POST['user'] ?? '');
	$pass = trim($_POST['pass'] ?? '');
	if (hash_equals($admin_user, $user) && hash_equals($admin_pass, $pass)) {
		$_SESSION['is_admin'] = true;
		header('Location: ./admin.php');
		exit;
	} else {
		$login_error = 'Credenciais inválidas';
	}
}

if (empty($_SESSION['is_admin'])) {
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login Admin - Ramais</title>
	<link rel="manifest" href="./manifest.json">
	<style>
		body{font-family:Arial,Helvetica,sans-serif;background:#f5f7f5;margin:0}
		.wrapper{max-width:400px;margin:60px auto;background:#fff;border:1px solid #e6efe6;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.06);padding:24px}
		.wrapper, input, button { box-sizing: border-box; }
		h1{margin:0 0 16px;font-size:20px;color:#2e7d32}
		input{width:100%;padding:12px 14px;border:1px solid #e6efe6;border-radius:10px;font-size:14px;margin-bottom:10px;display:block}
		button{width:100%;padding:12px 14px;border:0;border-radius:10px;background:#2e7d32;color:#fff;font-weight:700;cursor:pointer;display:block}
		.error{color:#b00020;margin:8px 0 0 0;font-size:14px}
		.back{display:inline-block;margin-top:12px;color:#2e7d32;text-decoration:none}
	</style>
</head>
<body>
	<div class="wrapper">
		<h1>Acesso Administrativo</h1>
		<form method="post" action="./admin.php">
			<input type="hidden" name="action" value="login">
			<input type="text" name="user" placeholder="Usuário" required>
			<input type="password" name="pass" placeholder="Senha" required>
			<button type="submit">Entrar</button>
			<?php if (!empty($login_error)): ?><div class="error"><?php echo h($login_error); ?></div><?php endif; ?>
		</form>
		<a class="back" href="./index.php">Voltar</a>
	</div>
</body>
</html>
<?php
exit;
}

// A partir daqui, usuário autenticado
$con = get_db_connection();

// Carregar lista de setores (tabelas)
$setores = [];
$res = $con->query("SHOW TABLES");
while ($row = $res->fetch_array()) {
	if ($row[0] !== 'importar' && $row[0] !== 'consulta') {
		$setores[] = $row[0];
	}
}

// Sanitização de tabela
function sanitize_table(string $t, array $allowed): ?string {
	return in_array($t, $allowed, true) ? $t : null;
}

// CRUD ações
$msg = '';
$msg_type = 'success'; // 'success' ou 'error'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crud'])) {
	$tabela = sanitize_table($_POST['tabela'] ?? '', $setores);
	if ($tabela) {
		$crud = $_POST['crud'];
		if ($crud === 'create') {
			$stmt = $con->prepare("INSERT INTO `$tabela` (sub_setor, descricao, falar_com, ramal) VALUES (?,?,?,?)");
			$sub = $_POST['sub_setor'] ?? null;
			$desc = $_POST['descricao'] ?? null;
			$talk = $_POST['falar_com'] ?? null;
			$ramal = $_POST['ramal'] ?? null;
			$stmt->bind_param('ssss', $sub, $desc, $talk, $ramal);
			$stmt->execute();
			$stmt->close();
			$msg = 'Registro criado com sucesso';
		} elseif ($crud === 'update') {
			$id = intval($_POST['id'] ?? 0);
			$stmt = $con->prepare("UPDATE `$tabela` SET sub_setor=?, descricao=?, falar_com=?, ramal=? WHERE id=?");
			$sub = $_POST['sub_setor'] ?? null;
			$desc = $_POST['descricao'] ?? null;
			$talk = $_POST['falar_com'] ?? null;
			$ramal = $_POST['ramal'] ?? null;
			$stmt->bind_param('ssssi', $sub, $desc, $talk, $ramal, $id);
			$stmt->execute();
			$stmt->close();
			$msg = 'Registro atualizado com sucesso';
		} elseif ($crud === 'delete') {
			$id = intval($_POST['id'] ?? 0);
			$stmt = $con->prepare("DELETE FROM `$tabela` WHERE id=?");
			$stmt->bind_param('i', $id);
			$stmt->execute();
			$stmt->close();
			$msg = 'Registro removido com sucesso';
		} elseif ($crud === 'transfer') {
			$id = intval($_POST['id'] ?? 0);
			$tabela_destino = sanitize_table($_POST['tabela_destino'] ?? '', $setores);
			if ($tabela_destino) {
				$resultado = transferir_ramal($con, $tabela, $tabela_destino, $id);
				$msg = $resultado['message'];
				$msg_type = $resultado['success'] ? 'success' : 'error';
				// Se transferiu com sucesso, redireciona para o setor destino
				if ($resultado['success']) {
					header('Location: ./admin.php?setor=' . urlencode($tabela_destino) . '&msg=' . urlencode($msg));
					exit;
				}
			} else {
				$msg = 'Setor de destino inválido';
				$msg_type = 'error';
			}
		}
	}
}

// Tabela selecionada para listagem
$tabela_sel = sanitize_table($_GET['setor'] ?? ($setores[0] ?? ''), $setores);
$registros = [];
if ($tabela_sel) {
	$stmt = $con->prepare("SELECT id, sub_setor, descricao, falar_com, ramal FROM `$tabela_sel` ORDER BY sub_setor, descricao LIMIT 500");
	$stmt->execute();
	$res = $stmt->get_result();
	while ($row = $res->fetch_assoc()) { $registros[] = $row; }
	$stmt->close();
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Painel Admin - Ramais</title>
	<link rel="manifest" href="./manifest.json">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<style>
		* {
			box-sizing: border-box;
		}
		
		body {
			font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
			background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
			margin: 0;
			padding: 20px;
			color: #2d3748;
			line-height: 1.6;
		}
		
		.container {
			max-width: 1400px;
			margin: 0 auto;
			background: #ffffff;
			border-radius: 16px;
			box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
			overflow: hidden;
		}
		
		.header {
			background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
			color: white;
			padding: 28px 32px;
			display: flex;
			justify-content: space-between;
			align-items: center;
			box-shadow: 0 4px 12px rgba(46, 125, 50, 0.2);
		}
		
		.header h1 {
			margin: 0;
			font-size: 28px;
			font-weight: 700;
			letter-spacing: -0.5px;
			display: flex;
			align-items: center;
			gap: 12px;
		}
		
		.header-actions {
			display: flex;
			gap: 12px;
		}
		
		.header-actions a {
			padding: 10px 20px;
			border-radius: 8px;
			text-decoration: none;
			font-weight: 500;
			font-size: 14px;
			transition: all 0.2s;
			display: inline-flex;
			align-items: center;
			gap: 6px;
		}
		
		.header-actions a:first-child {
			background: rgba(255, 255, 255, 0.15);
			color: white;
			backdrop-filter: blur(10px);
		}
		
		.header-actions a:first-child:hover {
			background: rgba(255, 255, 255, 0.25);
		}
		
		.header-actions a:last-child {
			background: rgba(255, 255, 255, 0.2);
			color: white;
		}
		
		.header-actions a:last-child:hover {
			background: rgba(255, 255, 255, 0.3);
		}
		
		.content {
			padding: 32px;
		}
		
		.section {
			background: #f8fafc;
			border-radius: 12px;
			padding: 24px;
			margin-bottom: 24px;
			border: 1px solid #e2e8f0;
		}
		
		.section-title {
			font-size: 18px;
			font-weight: 600;
			color: #1a202c;
			margin: 0 0 20px 0;
			display: flex;
			align-items: center;
			gap: 8px;
		}
		
		.form-inline {
			display: flex;
			gap: 12px;
			flex-wrap: wrap;
			align-items: flex-end;
		}
		
		.form-group {
			flex: 1;
			min-width: 180px;
		}
		
		.form-group label {
			display: block;
			font-size: 13px;
			font-weight: 500;
			color: #4a5568;
			margin-bottom: 6px;
		}
		
		select, input[type="text"] {
			width: 100%;
			padding: 12px 16px;
			border: 2px solid #e2e8f0;
			border-radius: 8px;
			font-size: 14px;
			font-family: inherit;
			background: white;
			color: #2d3748;
			transition: all 0.2s;
		}
		
		select:focus, input[type="text"]:focus {
			outline: none;
			border-color: #2e7d32;
			box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
		}
		
		select {
			cursor: pointer;
			background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
			background-position: right 12px center;
			background-repeat: no-repeat;
			background-size: 16px;
			padding-right: 40px;
		}
		
		button {
			padding: 12px 24px;
			border: none;
			border-radius: 8px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.2s;
			display: inline-flex;
			align-items: center;
			gap: 8px;
			font-family: inherit;
		}
		
		button.primary {
			background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
			color: white;
			box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
		}
		
		button.primary:hover {
			transform: translateY(-2px);
			box-shadow: 0 6px 16px rgba(46, 125, 50, 0.4);
		}
		
		button.primary:active {
			transform: translateY(0);
		}
		
		button.transfer {
			background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
			color: white;
			padding: 10px 16px;
			font-size: 13px;
			box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
		}
		
		button.transfer:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(255, 152, 0, 0.4);
		}
		
		button.delete {
			background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%);
			color: white;
			padding: 10px 16px;
			font-size: 13px;
			box-shadow: 0 2px 8px rgba(211, 47, 47, 0.3);
		}
		
		button.delete:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(211, 47, 47, 0.4);
		}
		
		button.save {
			background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
			color: white;
			padding: 10px 16px;
			font-size: 13px;
			box-shadow: 0 2px 8px rgba(25, 118, 210, 0.3);
		}
		
		button.save:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(25, 118, 210, 0.4);
		}
		
		.msg {
			padding: 16px 20px;
			border-radius: 10px;
			margin-bottom: 24px;
			font-weight: 500;
			display: flex;
			align-items: center;
			gap: 12px;
			animation: slideIn 0.3s ease-out;
		}
		
		.msg:not(.error) {
			background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
			color: #1b5e20;
			border-left: 4px solid #2e7d32;
		}
		
		.msg.error {
			background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
			color: #b71c1c;
			border-left: 4px solid #d32f2f;
		}
		
		@keyframes slideIn {
			from {
				opacity: 0;
				transform: translateY(-10px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}
		
		table {
			width: 100%;
			border-collapse: separate;
			border-spacing: 0;
			background: white;
			border-radius: 12px;
			overflow: hidden;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
		}
		
		table thead {
			background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
		}
		
		table th {
			padding: 16px;
			text-align: left;
			font-weight: 600;
			font-size: 13px;
			color: #4a5568;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			border-bottom: 2px solid #e2e8f0;
		}
		
		table td {
			padding: 16px;
			border-bottom: 1px solid #f1f5f9;
			vertical-align: middle;
		}
		
		table tbody tr {
			transition: all 0.2s;
		}
		
		table tbody tr:hover {
			background: #f8fafc;
		}
		
		table tbody tr:last-child td {
			border-bottom: none;
		}
		
		table input[type="text"] {
			padding: 8px 12px;
			border: 1px solid #e2e8f0;
			border-radius: 6px;
			font-size: 14px;
			width: 100%;
			min-width: 120px;
		}
		
		table input[type="text"]:focus {
			border-color: #2e7d32;
			box-shadow: 0 0 0 2px rgba(46, 125, 50, 0.1);
		}
		
		.actions {
			display: flex;
			flex-direction: column;
			gap: 8px;
			min-width: 120px;
		}
		
		.actions button {
			width: 100%;
			justify-content: center;
		}
		
		.info-text {
			color: #718096;
			font-size: 13px;
			margin-top: 16px;
			text-align: center;
		}
		
		.modal {
			display: none;
			position: fixed;
			z-index: 9999;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.6);
			backdrop-filter: blur(4px);
			overflow: auto;
			animation: fadeIn 0.2s;
		}
		
		@keyframes fadeIn {
			from { opacity: 0; }
			to { opacity: 1; }
		}
		
		.modal-content {
			background: white;
			margin: 8% auto;
			padding: 32px;
			border-radius: 16px;
			max-width: 520px;
			box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
			position: relative;
			animation: slideUp 0.3s ease-out;
		}
		
		@keyframes slideUp {
			from {
				opacity: 0;
				transform: translateY(30px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}
		
		.modal-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 24px;
			padding-bottom: 16px;
			border-bottom: 2px solid #f1f5f9;
		}
		
		.modal-header h3 {
			margin: 0;
			font-size: 22px;
			font-weight: 700;
			color: #1a202c;
		}
		
		.close {
			color: #a0aec0;
			font-size: 32px;
			font-weight: 300;
			cursor: pointer;
			line-height: 1;
			border: none;
			background: none;
			transition: color 0.2s;
			padding: 0;
			width: 32px;
			height: 32px;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		
		.close:hover {
			color: #2d3748;
		}
		
		.modal-body {
			margin-bottom: 24px;
		}
		
		.modal-body p {
			margin: 0 0 20px 0;
			color: #4a5568;
			font-size: 15px;
		}
		
		.modal-footer {
			display: flex;
			gap: 12px;
			justify-content: flex-end;
			padding-top: 20px;
			border-top: 2px solid #f1f5f9;
		}
		
		.modal-footer button {
			min-width: 120px;
		}
		
		.modal-footer button[type="button"] {
			background: #f7fafc;
			color: #4a5568;
			border: 2px solid #e2e8f0;
		}
		
		.modal-footer button[type="button"]:hover {
			background: #edf2f7;
			border-color: #cbd5e0;
		}
		
		.icon {
			width: 20px;
			height: 20px;
			display: inline-block;
			vertical-align: middle;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h1>⚙️ Painel Administrativo</h1>
			<div class="header-actions">
				<a href="./index.php">← Voltar ao site</a>
				<a href="./logout.php">Sair</a>
			</div>
		</div>
		
		<div class="content">
			<?php if (!empty($msg)): ?>
				<div class="msg <?php echo $msg_type === 'error' ? 'error' : ''; ?>">
					<?php echo $msg_type === 'error' ? '⚠️' : '✓'; ?>
					<?php echo h($msg); ?>
				</div>
			<?php endif; ?>
			<?php if (isset($_GET['msg'])): ?>
				<div class="msg">
					✓ <?php echo h($_GET['msg']); ?>
				</div>
			<?php endif; ?>

			<div class="section">
				<h2 class="section-title">📂 Selecionar Setor</h2>
				<form method="get" action="./admin.php">
					<div class="form-group">
						<label for="setor">Setor</label>
						<select id="setor" name="setor" onchange="this.form.submit()">
							<?php foreach ($setores as $s): ?>
								<option value="<?php echo h($s); ?>" <?php echo $s === $tabela_sel ? 'selected' : ''; ?>><?php echo formatar_nome_setor($s); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</form>
			</div>

			<div class="section">
				<h2 class="section-title">➕ Novo Registro</h2>
				<form method="post" class="form-inline" action="./admin.php?setor=<?php echo urlencode($tabela_sel); ?>">
					<input type="hidden" name="crud" value="create">
					<input type="hidden" name="tabela" value="<?php echo h($tabela_sel); ?>">
					<div class="form-group">
						<label for="sub_setor">Sub-setor</label>
						<input type="text" id="sub_setor" name="sub_setor" placeholder="Ex: Almoxarifado">
					</div>
					<div class="form-group">
						<label for="descricao">Descrição</label>
						<input type="text" id="descricao" name="descricao" placeholder="Ex: Recepção">
					</div>
					<div class="form-group">
						<label for="falar_com">Falar com</label>
						<input type="text" id="falar_com" name="falar_com" placeholder="Ex: João Silva">
					</div>
					<div class="form-group">
						<label for="ramal">Ramal</label>
						<input type="text" id="ramal" name="ramal" placeholder="Ex: 1885">
					</div>
					<div class="form-group">
						<label>&nbsp;</label>
						<button class="primary" type="submit">➕ Adicionar</button>
					</div>
				</form>
			</div>

			<div class="section">
				<h2 class="section-title">📋 Registros do Setor</h2>
				<table>
					<thead>
						<tr>
							<th>ID</th>
							<th>Sub-setor</th>
							<th>Descrição</th>
							<th>Falar com</th>
							<th>Ramal</th>
							<th>Ações</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($registros as $r): ?>
						<tr>
							<td><?php echo (int)$r['id']; ?></td>
							<td>
								<form method="post" class="form-inline" action="./admin.php?setor=<?php echo urlencode($tabela_sel); ?>">
									<input type="hidden" name="crud" value="update">
									<input type="hidden" name="tabela" value="<?php echo h($tabela_sel); ?>">
									<input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
									<input type="text" name="sub_setor" value="<?php echo h($r['sub_setor'] ?? ''); ?>">
							</td>
							<td><input type="text" name="descricao" value="<?php echo h($r['descricao'] ?? ''); ?>"></td>
							<td><input type="text" name="falar_com" value="<?php echo h($r['falar_com'] ?? ''); ?>"></td>
							<td><input type="text" name="ramal" value="<?php echo h($r['ramal'] ?? ''); ?>"></td>
							<td class="actions">
								<button class="save" type="submit">💾 Salvar</button>
								</form>
								<button class="transfer" type="button" data-id="<?php echo (int)$r['id']; ?>" data-tabela="<?php echo h($tabela_sel); ?>" data-contato="<?php echo h(formatar_contato($r)); ?>">↔️ Transferir</button>
								<form method="post" action="./admin.php?setor=<?php echo urlencode($tabela_sel); ?>" onsubmit="return confirm('Tem certeza que deseja remover este registro?');" style="display:inline">
									<input type="hidden" name="crud" value="delete">
									<input type="hidden" name="tabela" value="<?php echo h($tabela_sel); ?>">
									<input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
									<button class="delete" type="submit">🗑️ Excluir</button>
								</form>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<p class="info-text">📊 Exibindo no máximo 500 registros por setor</p>
		</div>
	</div>

	<!-- Modal de Transferência -->
	<div id="modalTransferir" class="modal">
		<div class="modal-content">
			<div class="modal-header">
				<h3>↔️ Transferir Ramal</h3>
				<button type="button" class="close" onclick="fecharModalTransferir()">&times;</button>
			</div>
			<div class="modal-body">
				<p id="modalInfo"></p>
				<form id="formTransferir" method="post" action="./admin.php?setor=<?php echo urlencode($tabela_sel); ?>">
					<input type="hidden" name="crud" value="transfer">
					<input type="hidden" name="tabela" id="tabelaOrigem">
					<input type="hidden" name="id" id="ramalId">
					<div class="form-group">
						<label for="tabelaDestino">Transferir para o setor:</label>
						<select id="tabelaDestino" name="tabela_destino" required>
							<option value="">Selecione um setor...</option>
							<?php foreach ($setores as $s): ?>
								<?php if ($s !== $tabela_sel): ?>
									<option value="<?php echo h($s); ?>"><?php echo formatar_nome_setor($s); ?></option>
								<?php endif; ?>
							<?php endforeach; ?>
						</select>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" onclick="fecharModalTransferir()">Cancelar</button>
				<button type="submit" form="formTransferir" class="primary">✓ Confirmar Transferência</button>
			</div>
		</div>
	</div>

	<script>
		// Aguardar o DOM estar pronto
		document.addEventListener('DOMContentLoaded', function() {
			// Função para abrir o modal
			function abrirModalTransferir(id, tabelaOrigem, contato) {
				try {
					const modal = document.getElementById('modalTransferir');
					const ramalId = document.getElementById('ramalId');
					const tabelaOrigemInput = document.getElementById('tabelaOrigem');
					const modalInfo = document.getElementById('modalInfo');
					const tabelaDestino = document.getElementById('tabelaDestino');
					
					if (!modal || !ramalId || !tabelaOrigemInput || !modalInfo || !tabelaDestino) {
						console.error('Elementos do modal não encontrados:', {
							modal: !!modal,
							ramalId: !!ramalId,
							tabelaOrigemInput: !!tabelaOrigemInput,
							modalInfo: !!modalInfo,
							tabelaDestino: !!tabelaDestino
						});
						alert('Erro ao abrir modal. Verifique o console para mais detalhes.');
						return;
					}
					
					ramalId.value = id;
					tabelaOrigemInput.value = tabelaOrigem;
					modalInfo.textContent = 'Transferir: ' + contato;
					tabelaDestino.value = '';
					modal.style.display = 'block';
				} catch (error) {
					console.error('Erro ao abrir modal:', error);
					alert('Erro ao abrir modal: ' + error.message);
				}
			}

			function fecharModalTransferir() {
				try {
					const modal = document.getElementById('modalTransferir');
					if (modal) {
						modal.style.display = 'none';
					}
				} catch (error) {
					console.error('Erro ao fechar modal:', error);
				}
			}

			// Adicionar event listeners aos botões de transferir
			const botoesTransferir = document.querySelectorAll('button.transfer');
			botoesTransferir.forEach(function(botao) {
				botao.addEventListener('click', function() {
					const id = parseInt(this.getAttribute('data-id'));
					const tabela = this.getAttribute('data-tabela');
					const contato = this.getAttribute('data-contato');
					abrirModalTransferir(id, tabela, contato);
				});
			});

			// Adicionar event listener ao botão de fechar
			const closeBtn = document.querySelector('.close');
			if (closeBtn) {
				closeBtn.addEventListener('click', fecharModalTransferir);
			}

			// Fechar modal ao clicar fora dele
			const modal = document.getElementById('modalTransferir');
			if (modal) {
				modal.addEventListener('click', function(event) {
					if (event.target === modal) {
						fecharModalTransferir();
					}
				});
			}

			// Fechar modal com ESC
			document.addEventListener('keydown', function(event) {
				if (event.key === 'Escape') {
					const modal = document.getElementById('modalTransferir');
					if (modal && modal.style.display === 'block') {
						fecharModalTransferir();
					}
				}
			});

			// Expor função globalmente para o onclick do botão fechar no HTML (fallback)
			window.fecharModalTransferir = fecharModalTransferir;
		});
	</script>
</body>
</html>


