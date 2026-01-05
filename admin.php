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

// Carregar lista de setores (tabelas) - usando função com cache
$setores = get_lista_setores($con);

// Sanitização de tabela
function sanitize_table(string $t, array $allowed): ?string {
	return in_array($t, $allowed, true) ? $t : null;
}

// CRUD ações para emails
$msg_email = '';
$msg_email_type = 'success';
$setores_emails = obter_setores_emails($con);
$setor_email_sel = $_GET['setor_email'] ?? ($setores_emails[0] ?? '');
$emails_registros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crud_email'])) {
	$crud_email = $_POST['crud_email'];
	
	if ($crud_email === 'create_email') {
		$setor_email = trim($_POST['setor_email'] ?? '');
		$email_novo = trim($_POST['email_novo'] ?? '');
		$novo_setor = trim($_POST['novo_setor'] ?? '');
		
		// Se foi fornecido um novo setor, usa ele
		if (!empty($novo_setor)) {
			$setor_email = strtolower(str_replace(' ', '_', trim($novo_setor)));
		}
		
		if (empty($setor_email) || empty($email_novo)) {
			$msg_email = 'É necessário selecionar um setor existente OU criar um novo setor, além de informar o email';
			$msg_email_type = 'error';
		} elseif (!filter_var($email_novo, FILTER_VALIDATE_EMAIL)) {
			$msg_email = 'Email inválido';
			$msg_email_type = 'error';
		} else {
			$resultado = salvar_email_banco($setor_email, $email_novo, $con);
			$msg_email = $resultado['message'];
			$msg_email_type = $resultado['success'] ? 'success' : 'error';
			
			if ($resultado['success']) {
				$setor_email_sel = $setor_email;
				if (!empty($novo_setor)) {
					$msg_email .= ' (novo setor criado)';
				}
				// Atualiza lista de setores
				$setores_emails = obter_setores_emails($con);
			}
		}
	} elseif ($crud_email === 'update_email') {
		$id_email = intval($_POST['id_email'] ?? 0);
		$setor_email = trim($_POST['setor_email'] ?? '');
		$email_editado = trim($_POST['email_editado'] ?? '');
		
		if ($id_email > 0 && !empty($setor_email) && !empty($email_editado)) {
			if (!filter_var($email_editado, FILTER_VALIDATE_EMAIL)) {
				$msg_email = 'Email inválido';
				$msg_email_type = 'error';
			} else {
				$resultado = atualizar_email_banco($id_email, $setor_email, $email_editado, $con);
				$msg_email = $resultado['message'];
				$msg_email_type = $resultado['success'] ? 'success' : 'error';
				
				if ($resultado['success']) {
					$setor_email_sel = $setor_email;
					// Atualiza lista de setores
					$setores_emails = obter_setores_emails($con);
				}
			}
		} else {
			$msg_email = 'Dados inválidos';
			$msg_email_type = 'error';
		}
	} elseif ($crud_email === 'delete_email') {
		$id_email = intval($_POST['id_email'] ?? 0);
		
		if ($id_email > 0) {
			// Busca o setor antes de deletar para manter a seleção
			$stmt = $con->prepare("SELECT setor FROM emails WHERE id = ?");
			$stmt->bind_param('i', $id_email);
			$stmt->execute();
			$result = $stmt->get_result();
			if ($row = $result->fetch_assoc()) {
				$setor_email_sel = $row['setor'];
			}
			$stmt->close();
			
			$resultado = remover_email_banco($id_email, $con);
			$msg_email = $resultado['message'];
			$msg_email_type = $resultado['success'] ? 'success' : 'error';
			
			// Atualiza lista de setores
			$setores_emails = obter_setores_emails($con);
		} else {
			$msg_email = 'ID inválido';
			$msg_email_type = 'error';
		}
	}
}

// Carrega emails do setor selecionado
if (!empty($setor_email_sel) && in_array($setor_email_sel, $setores_emails)) {
	$stmt = $con->prepare("SELECT id, setor, email FROM emails WHERE setor = ? ORDER BY email");
	$stmt->bind_param('s', $setor_email_sel);
	$stmt->execute();
	$result = $stmt->get_result();
	
	while ($row = $result->fetch_assoc()) {
		$emails_registros[] = [
			'id' => $row['id'],
			'setor' => $row['setor'],
			'email' => $row['email']
		];
	}
	$stmt->close();
}

// CRUD ações
$msg = '';
$msg_type = 'success'; // 'success' ou 'error'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crud'])) {
	$tabela = sanitize_table($_POST['tabela'] ?? '', $setores);
	if ($tabela) {
		$crud = $_POST['crud'];
		if ($crud === 'create') {
			$stmt = $con->prepare("INSERT INTO `$tabela` (sub_setor, descricao, falar_com, ramal, emergencia, oculto, principal) VALUES (?,?,?,?,?,?,?)");
			$sub = $_POST['sub_setor'] ?? null;
			$desc = $_POST['descricao'] ?? null;
			$talk = $_POST['falar_com'] ?? null;
			$ramal = $_POST['ramal'] ?? null;
			$emergencia = isset($_POST['emergencia']) && $_POST['emergencia'] ? 1 : 0;
			$oculto = isset($_POST['oculto']) && $_POST['oculto'] ? 1 : 0;
			$principal = isset($_POST['principal']) && $_POST['principal'] ? 1 : 0;
			// Garantir exclusividade mútua - prioridade: Emergência > Principal > Oculto
			if ($emergencia + $oculto + $principal > 1) {
				// Se mais de uma está marcada, manter apenas uma (prioridade)
				if ($emergencia) {
					$oculto = 0;
					$principal = 0;
				} elseif ($principal) {
					$emergencia = 0;
					$oculto = 0;
				} else {
					$emergencia = 0;
					$principal = 0;
				}
			}
			$stmt->bind_param('ssssiii', $sub, $desc, $talk, $ramal, $emergencia, $oculto, $principal);
			$stmt->execute();
			$stmt->close();
			$msg = 'Registro criado com sucesso';
		} elseif ($crud === 'update') {
			$id = intval($_POST['id'] ?? 0);
			$stmt = $con->prepare("UPDATE `$tabela` SET sub_setor=?, descricao=?, falar_com=?, ramal=?, emergencia=?, oculto=?, principal=? WHERE id=?");
			$sub = $_POST['sub_setor'] ?? null;
			$desc = $_POST['descricao'] ?? null;
			$talk = $_POST['falar_com'] ?? null;
			$ramal = $_POST['ramal'] ?? null;
			$emergencia = isset($_POST['emergencia']) && $_POST['emergencia'] ? 1 : 0;
			$oculto = isset($_POST['oculto']) && $_POST['oculto'] ? 1 : 0;
			$principal = isset($_POST['principal']) && $_POST['principal'] ? 1 : 0;
			// Garantir exclusividade mútua - prioridade: Emergência > Principal > Oculto
			if ($emergencia + $oculto + $principal > 1) {
				// Se mais de uma está marcada, manter apenas uma (prioridade)
				if ($emergencia) {
					$oculto = 0;
					$principal = 0;
				} elseif ($principal) {
					$emergencia = 0;
					$oculto = 0;
				} else {
					$emergencia = 0;
					$principal = 0;
				}
			}
			$stmt->bind_param('ssssiiii', $sub, $desc, $talk, $ramal, $emergencia, $oculto, $principal, $id);
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
	$stmt = $con->prepare("SELECT id, sub_setor, descricao, falar_com, ramal, emergencia, oculto, principal FROM `$tabela_sel` ORDER BY sub_setor, descricao LIMIT 500");
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
			padding: 0;
		}
		
		/* Tabs Navigation */
		.tabs {
			display: flex;
			background: #f8fafc;
			border-bottom: 2px solid #e2e8f0;
			padding: 0 32px;
		}
		
		.tab {
			padding: 16px 24px;
			cursor: pointer;
			font-weight: 500;
			color: #718096;
			border-bottom: 3px solid transparent;
			transition: all 0.2s;
			background: none;
			border: none;
			font-size: 15px;
			font-family: inherit;
		}
		
		.tab:hover {
			color: #2e7d32;
			background: rgba(46, 125, 50, 0.05);
		}
		
		.tab.active {
			color: #2e7d32;
			border-bottom-color: #2e7d32;
			font-weight: 600;
		}
		
		.tab-content {
			display: none;
			padding: 32px;
		}
		
		.tab-content.active {
			display: block;
		}
		
		.section {
			background: white;
			border-radius: 8px;
			padding: 20px;
			margin-bottom: 20px;
			border: 1px solid #e2e8f0;
		}
		
		.section-title {
			font-size: 16px;
			font-weight: 600;
			color: #1a202c;
			margin: 0 0 16px 0;
			display: flex;
			align-items: center;
			gap: 8px;
		}
		
		.form-inline {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
			gap: 16px;
			align-items: end;
		}
		
		.form-group {
			display: flex;
			flex-direction: column;
		}
		
		.form-group.full-width {
			grid-column: 1 / -1;
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
		
		.table-wrapper {
			overflow-x: auto;
			border-radius: 8px;
			border: 1px solid #e2e8f0;
		}
		
		table {
			width: 100%;
			border-collapse: collapse;
			background: white;
			min-width: 1000px;
		}
		
		table thead {
			background: #f8fafc;
		}
		
		table th {
			padding: 12px 16px;
			text-align: left;
			font-weight: 600;
			font-size: 12px;
			color: #4a5568;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			border-bottom: 2px solid #e2e8f0;
		}
		
		table td {
			padding: 12px 16px;
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
			gap: 6px;
			flex-wrap: wrap;
		}
		
		.actions button {
			padding: 8px 12px;
			font-size: 12px;
			white-space: nowrap;
		}
		
		@media (max-width: 768px) {
			.actions {
				flex-direction: column;
			}
			.actions button {
				width: 100%;
			}
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
		
		.emergencia-row {
			background-color: #ffebee !important;
			border-left: 4px solid #d32f2f;
		}
		
		.emergencia-row:hover {
			background-color: #ffcdd2 !important;
		}
		
		.oculto-row {
			opacity: 0.7;
		}
		
		.oculto-row:hover {
			opacity: 1;
		}
		
		.principal-row {
			background-color: #e3f2fd !important;
			border-left: 4px solid #1976d2;
		}
		
		.principal-row:hover {
			background-color: #bbdefb !important;
		}
		
		.status-checkbox-row:disabled,
		.status-checkbox:disabled {
			opacity: 0.5;
			cursor: not-allowed !important;
			pointer-events: none;
		}
		
		.status-checkbox-row:disabled + span,
		.status-checkbox:disabled + span {
			opacity: 0.5;
			cursor: not-allowed !important;
		}
		
		/* Avisos e Alertas */
		.warning-box {
			background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
			border-left: 4px solid #ff9800;
			border-radius: 8px;
			padding: 16px 20px;
			margin-bottom: 20px;
			box-shadow: 0 2px 8px rgba(0,0,0,0.1);
			display: flex;
			align-items: flex-start;
			gap: 12px;
		}
		
		.warning-box-icon {
			font-size: 24px;
			flex-shrink: 0;
		}
		
		.warning-box-content {
			flex: 1;
		}
		
		.warning-box-title {
			color: #856404;
			display: block;
			margin-bottom: 6px;
			font-weight: 600;
			font-size: 15px;
		}
		
		.warning-box-text {
			color: #856404;
			font-size: 14px;
			line-height: 1.6;
			margin: 0;
		}
		
		.warning-box-text ul {
			margin: 8px 0 0 20px;
			padding: 0;
			color: #856404;
		}
		
		.warning-box-text li {
			margin-bottom: 4px;
		}
		
		.warning-box-small {
			background: #fff3cd;
			border-left: 3px solid #ff9800;
			border-radius: 6px;
			padding: 12px;
			margin-top: 12px;
		}
		
		.warning-box-small-title {
			color: #856404;
			display: block;
			margin-bottom: 4px;
			font-size: 13px;
			font-weight: 600;
		}
		
		.warning-box-small-text {
			color: #856404;
			font-size: 12px;
			line-height: 1.5;
		}
		
		@media (max-width: 767px) {
			.warning-box {
				padding: 12px 16px;
				margin-bottom: 16px;
			}
			
			.warning-box-icon {
				font-size: 20px;
			}
			
			.warning-box-title {
				font-size: 14px;
			}
			
			.warning-box-text {
				font-size: 13px;
			}
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
				<div class="msg <?php echo $msg_type === 'error' ? 'error' : ''; ?>" style="margin: 20px 32px;">
					<?php echo $msg_type === 'error' ? '⚠️' : '✓'; ?>
					<?php echo h($msg); ?>
				</div>
			<?php endif; ?>
			<?php if (isset($_GET['msg'])): ?>
				<div class="msg" style="margin: 20px 32px;">
					✓ <?php echo h($_GET['msg']); ?>
				</div>
			<?php endif; ?>
			
			<!-- Tabs Navigation -->
			<div class="tabs">
				<button class="tab active" data-tab="ramais" onclick="showTab('ramais', this)">📞 Ramais</button>
				<button class="tab" data-tab="emails" onclick="showTab('emails', this)">📧 Emails</button>
			</div>
			
			<!-- Tab: Ramais -->
			<div id="tab-ramais" class="tab-content active">
				<!-- Aviso sobre Transferência -->
				<div class="warning-box" style="margin-bottom: 24px;">
					<span class="warning-box-icon">⚠️</span>
					<div class="warning-box-content">
						<strong class="warning-box-title">Restrição de Transferência entre Ramais</strong>
						<p class="warning-box-text">
							<strong>Ramais internos não podem transferir ligações para ramais externos e vice-versa.</strong><br>
							As transferências só são permitidas entre ramais do mesmo tipo:
							<ul>
								<li>Ramais internos ↔ Ramais internos (entre diferentes setores internos)</li>
								<li>Ramais externos ↔ Ramais externos (dentro do setor externos)</li>
							</ul>
							Esta restrição é aplicada automaticamente no sistema para manter a organização das ligações.
						</p>
					</div>
				</div>
				
				<div class="section">
					<h2 class="section-title">📂 Selecionar Setor</h2>
					<form method="get" action="./admin.php">
						<div class="form-group" style="max-width: 300px;">
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
					<h2 class="section-title">➕ Novo Ramal</h2>
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
							<input type="text" id="ramal" name="ramal" placeholder="Ex: 1885" required>
						</div>
						<div class="form-group full-width">
							<div style="display: flex; gap: 24px; flex-wrap: wrap;">
								<label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
									<input type="checkbox" id="emergencia" name="emergencia" class="status-checkbox" data-group="status" style="width: auto; margin: 0;">
									<span>🚨 Emergências</span>
								</label>
								<label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
									<input type="checkbox" id="oculto" name="oculto" class="status-checkbox" data-group="status" style="width: auto; margin: 0;">
									<span>👁️ Oculto</span>
								</label>
								<label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
									<input type="checkbox" id="principal" name="principal" class="status-checkbox" data-group="status" style="width: auto; margin: 0;">
									<span>⭐ Principal</span>
								</label>
							</div>
						</div>
						<div class="form-group">
							<label>&nbsp;</label>
							<button class="primary" type="submit">➕ Adicionar Ramal</button>
						</div>
					</form>
				</div>

				<div class="section">
					<h2 class="section-title">📋 Registros do Setor</h2>
					<div class="table-wrapper">
						<table>
					<thead>
						<tr>
							<th>ID</th>
							<th>Sub-setor</th>
							<th>Descrição</th>
							<th>Falar com</th>
							<th>Ramal</th>
							<th>Status</th>
							<th>Ações</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($registros as $r): ?>
						<tr id="row-<?php echo (int)$r['id']; ?>" class="<?php echo ($r['emergencia'] ?? 0) ? 'emergencia-row' : ''; ?> <?php echo ($r['oculto'] ?? 0) ? 'oculto-row' : ''; ?> <?php echo ($r['principal'] ?? 0) ? 'principal-row' : ''; ?>">
							<td><?php echo (int)$r['id']; ?></td>
							<td>
								<form method="post" class="form-inline status-form" action="./admin.php?setor=<?php echo urlencode($tabela_sel); ?>">
									<input type="hidden" name="crud" value="update">
									<input type="hidden" name="tabela" value="<?php echo h($tabela_sel); ?>">
									<input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
									<input type="text" name="sub_setor" value="<?php echo h($r['sub_setor'] ?? ''); ?>">
							</td>
							<td><input type="text" name="descricao" value="<?php echo h($r['descricao'] ?? ''); ?>"></td>
							<td><input type="text" name="falar_com" value="<?php echo h($r['falar_com'] ?? ''); ?>"></td>
							<td><input type="text" name="ramal" value="<?php echo h($r['ramal'] ?? ''); ?>"></td>
							<td>
								<div style="display: flex; flex-direction: column; gap: 8px;">
									<label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer;">
										<input type="checkbox" name="emergencia" class="status-checkbox-row" data-id="<?php echo (int)$r['id']; ?>" data-tabela="<?php echo h($tabela_sel); ?>" data-field="emergencia" <?php echo ($r['emergencia'] ?? 0) ? 'checked' : ''; ?> style="width: auto; margin: 0;">
										<span style="color: <?php echo ($r['emergencia'] ?? 0) ? '#d32f2f' : '#666'; ?>; font-weight: <?php echo ($r['emergencia'] ?? 0) ? '600' : '400'; ?>;">🚨 Emergência</span>
									</label>
									<label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer;">
										<input type="checkbox" name="oculto" class="status-checkbox-row" data-id="<?php echo (int)$r['id']; ?>" data-tabela="<?php echo h($tabela_sel); ?>" data-field="oculto" <?php echo ($r['oculto'] ?? 0) ? 'checked' : ''; ?> style="width: auto; margin: 0;">
										<span style="color: <?php echo ($r['oculto'] ?? 0) ? '#ff9800' : '#666'; ?>; font-weight: <?php echo ($r['oculto'] ?? 0) ? '600' : '400'; ?>;">👁️ Oculto</span>
									</label>
									<label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer;">
										<input type="checkbox" name="principal" class="status-checkbox-row" data-id="<?php echo (int)$r['id']; ?>" data-tabela="<?php echo h($tabela_sel); ?>" data-field="principal" <?php echo ($r['principal'] ?? 0) ? 'checked' : ''; ?> style="width: auto; margin: 0;">
										<span style="color: <?php echo ($r['principal'] ?? 0) ? '#1976d2' : '#666'; ?>; font-weight: <?php echo ($r['principal'] ?? 0) ? '600' : '400'; ?>;">⭐ Principal</span>
									</label>
								</div>
							</td>
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
			
			<!-- Tab: Emails -->
			<div id="tab-emails" class="tab-content">
				<?php if (!empty($msg_email)): ?>
					<div class="msg <?php echo $msg_email_type === 'error' ? 'error' : ''; ?>">
						<?php echo $msg_email_type === 'error' ? '⚠️' : '✓'; ?>
						<?php echo h($msg_email); ?>
					</div>
				<?php endif; ?>
				
				<?php
				// Verifica se há emails no banco
				$stmt_check = $con->query("SELECT COUNT(*) as total FROM emails");
				$total_emails = $stmt_check->fetch_assoc()['total'];
				$arquivo_existe = file_exists(__DIR__ . '/emails.html');
				
				if ($total_emails == 0 && $arquivo_existe): ?>
					<div class="section" style="background: #fff3cd; border-left: 4px solid #ff9800;">
						<h2 class="section-title">📥 Importar Emails do Arquivo HTML</h2>
						<p style="color: #856404; margin-bottom: 16px;">
							A tabela de emails está vazia. Você pode importar os dados do arquivo <code>emails.html</code> para o banco de dados.
						</p>
						<a href="./importar_emails.php" style="display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
							📥 Importar Emails
						</a>
					</div>
				<?php endif; ?>
				
				<div class="section">
					<h2 class="section-title">📂 Selecionar Setor</h2>
					<form method="get" action="./admin.php">
						<input type="hidden" name="setor" value="<?php echo h($tabela_sel); ?>">
						<div class="form-group" style="max-width: 300px;">
							<label for="setor_email">Setor</label>
							<select id="setor_email" name="setor_email" onchange="this.form.submit()">
								<?php foreach ($setores_emails as $s): ?>
									<option value="<?php echo h($s); ?>" <?php echo $s === $setor_email_sel ? 'selected' : ''; ?>><?php echo formatar_nome_setor($s); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</form>
				</div>
				
				<div class="section">
					<h2 class="section-title">➕ Novo Email</h2>
					<form method="post" action="./admin.php?setor=<?php echo urlencode($tabela_sel); ?>&setor_email=<?php echo urlencode($setor_email_sel); ?>" id="form-novo-email">
						<input type="hidden" name="crud_email" value="create_email">
						
						<!-- Opção: Setor Existente -->
						<div style="margin-bottom: 20px;">
							<label style="display: block; font-weight: 500; margin-bottom: 8px; color: #4a5568;">📂 Usar Setor Existente</label>
							<select id="setor_email_novo" name="setor_email" onchange="toggleSetorOptions()" style="width: 100%; max-width: 400px;">
								<option value="">-- Selecione um setor --</option>
								<?php foreach ($setores_emails as $s): ?>
									<option value="<?php echo h($s); ?>" <?php echo $s === $setor_email_sel ? 'selected' : ''; ?>><?php echo formatar_nome_setor($s); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						
						<!-- Divisor visual -->
						<div style="display: flex; align-items: center; margin: 24px 0; color: #cbd5e0;">
							<div style="flex: 1; height: 1px; background: #e2e8f0;"></div>
							<span style="padding: 0 12px; font-size: 12px; font-weight: 500;">OU</span>
							<div style="flex: 1; height: 1px; background: #e2e8f0;"></div>
						</div>
						
						<!-- Opção: Criar Novo Setor -->
						<div style="margin-bottom: 20px;">
							<label for="novo_setor_input" style="display: block; font-weight: 500; margin-bottom: 8px; color: #4a5568;">✨ Criar Novo Setor</label>
							<input type="text" id="novo_setor_input" name="novo_setor" placeholder="Digite o nome do novo setor (ex: novo_setor)" onchange="toggleSetorOptions()" style="width: 100%; max-width: 400px; text-transform: lowercase;">
							<small style="display: block; color: #718096; font-size: 12px; margin-top: 6px;">O nome será convertido automaticamente para o formato padrão</small>
						</div>
						
						<!-- Campo Email -->
						<div style="margin-bottom: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
							<label for="email_novo" style="display: block; font-weight: 500; margin-bottom: 8px; color: #4a5568;">📧 Endereço de Email</label>
							<input type="email" id="email_novo" name="email_novo" placeholder="exemplo@saogoncalo.mg.gov.br" required style="width: 100%; max-width: 500px;">
						</div>
						
						<!-- Botão de ação -->
						<div>
							<button class="primary" type="submit" style="min-width: 200px;">➕ Adicionar Email</button>
						</div>
					</form>
					<script>
						function toggleSetorOptions() {
							const setorSelect = document.getElementById('setor_email_novo');
							const novoSetorInput = document.getElementById('novo_setor_input');
							
							// Se selecionou um setor, limpa o campo de novo setor
							if (setorSelect.value) {
								novoSetorInput.value = '';
								novoSetorInput.style.borderColor = '#e2e8f0';
								setorSelect.style.borderColor = '#2e7d32';
							}
							
							// Se digitou novo setor, limpa o select
							if (novoSetorInput.value.trim()) {
								setorSelect.value = '';
								setorSelect.style.borderColor = '#e2e8f0';
								novoSetorInput.style.borderColor = '#2e7d32';
							}
						}
						
						document.getElementById('form-novo-email').addEventListener('submit', function(e) {
							const setorSelect = document.getElementById('setor_email_novo');
							const novoSetorInput = document.getElementById('novo_setor_input');
							
							if (!setorSelect.value && !novoSetorInput.value.trim()) {
								e.preventDefault();
								alert('⚠️ Por favor, selecione um setor existente OU digite um nome para criar um novo setor.');
								return false;
							}
							
							// Se novo setor foi digitado, limpa o select
							if (novoSetorInput.value.trim()) {
								setorSelect.value = '';
							}
						});
					</script>
				</div>
				
				<div class="section">
					<h2 class="section-title">📋 Emails do Setor</h2>
					<?php if (!empty($emails_registros)): ?>
						<div class="table-wrapper">
							<table>
								<thead>
									<tr>
										<th>Setor</th>
										<th>Email</th>
										<th>Ações</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($emails_registros as $r): ?>
									<tr id="email-row-<?php echo (int)$r['id']; ?>">
										<td>
											<form method="post" action="./admin.php?setor=<?php echo urlencode($tabela_sel); ?>&setor_email=<?php echo urlencode($setor_email_sel); ?>" style="display: inline;">
												<input type="hidden" name="crud_email" value="update_email">
												<input type="hidden" name="id_email" value="<?php echo (int)$r['id']; ?>">
												<select name="setor_email" style="width: 180px; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;">
													<?php foreach ($setores_emails as $s): ?>
														<option value="<?php echo h($s); ?>" <?php echo $s === $r['setor'] ? 'selected' : ''; ?>><?php echo formatar_nome_setor($s); ?></option>
													<?php endforeach; ?>
												</select>
										</td>
										<td>
												<input type="email" name="email_editado" value="<?php echo h($r['email']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px;" required>
										</td>
										<td class="actions">
												<button class="save" type="submit">💾 Salvar</button>
											</form>
											<form method="post" action="./admin.php?setor=<?php echo urlencode($tabela_sel); ?>&setor_email=<?php echo urlencode($setor_email_sel); ?>" onsubmit="return confirm('Tem certeza que deseja remover este email?');" style="display:inline">
												<input type="hidden" name="crud_email" value="delete_email">
												<input type="hidden" name="id_email" value="<?php echo (int)$r['id']; ?>">
												<button class="delete" type="submit">🗑️ Excluir</button>
											</form>
										</td>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php else: ?>
						<p style="color: #718096; text-align: center; padding: 24px;">Nenhum email encontrado para este setor.</p>
					<?php endif; ?>
				</div>
			</div>
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
							<?php 
							// Separar setores internos e externos
							$setores_internos_admin = array_filter($setores, function($s) {
								return $s !== 'externos';
							});
							$origem_e_externo = ($tabela_sel === 'externos');
							
							// Se origem é interno, só pode transferir para outros internos
							// Se origem é externo, pode transferir para qualquer lugar
							$setores_disponiveis = $origem_e_externo ? $setores : $setores_internos_admin;
							
							foreach ($setores_disponiveis as $s): 
								if ($s !== $tabela_sel): 
							?>
								<option value="<?php echo h($s); ?>"><?php echo formatar_nome_setor($s); ?></option>
							<?php 
								endif;
							endforeach; 
							?>
						</select>
						<div class="warning-box-small">
							<?php if (!$origem_e_externo): ?>
								<strong class="warning-box-small-title">⚠️ Restrição Aplicada</strong>
								<span class="warning-box-small-text">
									Este é um ramal <strong>interno</strong>. Ele só pode ser transferido para outros setores internos. 
									Ramais externos não aparecem na lista acima.
								</span>
							<?php else: ?>
								<strong class="warning-box-small-title">ℹ️ Informação</strong>
								<span class="warning-box-small-text">
									Este é um ramal <strong>externo</strong>. Ele pode ser transferido para qualquer setor (interno ou externo).
								</span>
							<?php endif; ?>
						</div>
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
		// Função para controlar as tabs
		function showTab(tabName, button) {
			// Esconde todas as tabs
			document.querySelectorAll('.tab-content').forEach(content => {
				content.classList.remove('active');
			});
			
			// Remove active de todos os botões
			document.querySelectorAll('.tab').forEach(tab => {
				tab.classList.remove('active');
			});
			
			// Mostra a tab selecionada
			document.getElementById('tab-' + tabName).classList.add('active');
			
			// Ativa o botão correspondente
			if (button) {
				button.classList.add('active');
			} else {
				document.querySelector(`.tab[data-tab="${tabName}"]`).classList.add('active');
			}
		}
		
		// Restaurar posição do scroll após reload
		(function() {
			// Prioridade 1: Tentar restaurar para uma linha específica
			const savedRowId = sessionStorage.getItem('adminScrollToRow');
			if (savedRowId !== null) {
				sessionStorage.removeItem('adminScrollToRow');
				
				function scrollToRow() {
					const row = document.getElementById(savedRowId);
					if (row) {
						// Calcular posição com offset para não ficar colado no topo
						const rowTop = row.getBoundingClientRect().top + window.pageYOffset;
						const offset = 150; // Espaço do topo
						window.scrollTo({
							top: rowTop - offset,
							behavior: 'instant' // Sem animação para ser mais rápido
						});
						
						// Destacar a linha brevemente para feedback visual
						row.style.transition = 'background-color 0.3s';
						const originalBg = row.style.backgroundColor;
						row.style.backgroundColor = '#fff3cd';
						setTimeout(function() {
							row.style.backgroundColor = originalBg;
						}, 1000);
						
						console.log('📍 Scroll restaurado para linha:', savedRowId);
						return true;
					}
					return false;
				}
				
				// Tentar múltiplas vezes até encontrar o elemento
				let attempts = 0;
				const maxAttempts = 10;
				
				function tryScroll() {
					attempts++;
					if (scrollToRow()) {
						return; // Sucesso
					}
					
					if (attempts < maxAttempts) {
						setTimeout(tryScroll, 100);
					} else {
						console.warn('⚠️ Linha não encontrada após', maxAttempts, 'tentativas');
					}
				}
				
				// Iniciar tentativas
				if (document.readyState === 'complete') {
					setTimeout(tryScroll, 50);
				} else {
					document.addEventListener('DOMContentLoaded', function() {
						setTimeout(tryScroll, 100);
					});
					window.addEventListener('load', function() {
						setTimeout(tryScroll, 50);
					});
				}
				return; // Não continuar para o fallback
			}
			
			// Prioridade 2: Fallback - restaurar posição do scroll
			const savedScrollPosition = sessionStorage.getItem('adminScrollPosition');
			if (savedScrollPosition !== null) {
				sessionStorage.removeItem('adminScrollPosition');
				const scrollPos = parseInt(savedScrollPosition, 10);
				
				function restoreScroll() {
					window.scrollTo({
						top: scrollPos,
						behavior: 'instant'
					});
					console.log('📍 Posição do scroll restaurada:', scrollPos);
				}
				
				if (document.readyState === 'complete') {
					setTimeout(restoreScroll, 50);
				} else {
					window.addEventListener('load', function() {
						setTimeout(restoreScroll, 50);
					});
					if (document.readyState === 'loading') {
						document.addEventListener('DOMContentLoaded', function() {
							setTimeout(restoreScroll, 100);
						});
					} else {
						setTimeout(restoreScroll, 100);
					}
				}
			}
		})();
		
		// Aguardar o DOM estar pronto
		document.addEventListener('DOMContentLoaded', function() {
			
			// --- LÓGICA DO MODAL DE TRANSFERÊNCIA ---

			// Função para abrir o modal
			function abrirModalTransferir(id, tabelaOrigem, contato) {
				const modal = document.getElementById('modalTransferir');
				if (modal) {
					document.getElementById('ramalId').value = id;
					document.getElementById('tabelaOrigem').value = tabelaOrigem;
					document.getElementById('modalInfo').textContent = 'Transferir: ' + contato;
					document.getElementById('tabelaDestino').value = '';
					modal.style.display = 'block';
				}
			}

			function fecharModalTransferir() {
				const modal = document.getElementById('modalTransferir');
				if (modal) modal.style.display = 'none';
			}

			// Adicionar event listeners aos botões de transferir
			document.querySelectorAll('button.transfer').forEach(botao => {
				botao.addEventListener('click', function() {
					abrirModalTransferir(
						this.getAttribute('data-id'),
						this.getAttribute('data-tabela'),
						this.getAttribute('data-contato')
					);
				});
			});

			// Fechar modal
			const modal = document.getElementById('modalTransferir');
			if (modal) {
				modal.querySelector('.close').addEventListener('click', fecharModalTransferir);
				modal.addEventListener('click', e => { if (e.target === modal) fecharModalTransferir(); });
			}
			document.addEventListener('keydown', e => { if (e.key === 'Escape') fecharModalTransferir(); });
			window.fecharModalTransferir = fecharModalTransferir;
			
			// --- LÓGICA DE ATUALIZAÇÃO DE STATUS SEM RECARREGAR ---

			const tabelaRegistros = document.querySelector('table tbody');
			if (tabelaRegistros) {
				tabelaRegistros.addEventListener('change', async function(e) {
					if (!e.target.classList.contains('status-checkbox-row')) return;

					const checkbox = e.target;
					const id = checkbox.dataset.id;
					const tabela = checkbox.dataset.tabela;
					const field = checkbox.dataset.field;
					const value = checkbox.checked;
					const row = checkbox.closest('tr');

					// Atualiza a UI imediatamente para feedback rápido
					updateRowUI(row, field, value);

					try {
						const response = await fetch('./api.php', {
							method: 'POST',
							headers: { 'Content-Type': 'application/json' },
							body: JSON.stringify({ id, tabela, field, value })
						});

						const result = await response.json();

						if (!response.ok || !result.success) {
							throw new Error(result.message || 'Erro desconhecido na API.');
						}
						
						showToast('✓ Status atualizado com sucesso!');

					} catch (error) {
						console.error('Erro ao atualizar status:', error);
						showToast('⚠️ Erro: ' + error.message, true);
						// Reverte a UI em caso de erro
						checkbox.checked = !value;
						updateRowUI(row, field, !value);
					}
				});
			}

			function updateRowUI(row, changedField, isChecked) {
				const checkboxes = row.querySelectorAll('.status-checkbox-row');
				
				// Lógica de exclusividade mútua na UI
				checkboxes.forEach(cb => {
					const fieldName = cb.dataset.field;
					
					// Atualiza a classe da linha
					row.classList.remove(`${fieldName}-row`);
					if (isChecked && fieldName === changedField) {
						row.classList.add(`${fieldName}-row`);
					}

					// Desmarca e habilita outros checkboxes
					if (fieldName !== changedField) {
						if (isChecked) {
							cb.checked = false;
							cb.disabled = true;
						} else {
							cb.disabled = false;
						}
					}
				});
				
				// Caso especial para 'oculto' - ajusta opacidade
				if (row.classList.contains('oculto-row')) {
					row.classList.add('oculto-row'); 
				} else {
					row.classList.remove('oculto-row');
				}
			}
			
			// --- LÓGICA PARA CHECKBOXES DO FORMULÁRIO DE CRIAÇÃO ---
			
			const formCheckboxes = document.querySelectorAll('.status-checkbox[data-group="status"]');
			formCheckboxes.forEach(checkbox => {
				checkbox.addEventListener('change', function() {
					if (this.checked) {
						formCheckboxes.forEach(other => {
							if (other !== this) other.disabled = true;
						});
					} else {
						formCheckboxes.forEach(other => other.disabled = false);
					}
				});
			});

			// Inicializar estado dos checkboxes ao carregar a página
			document.querySelectorAll('tr[id^="row-"]').forEach(row => {
				const checkedBox = row.querySelector('.status-checkbox-row:checked');
				if (checkedBox) {
					updateRowUI(row, checkedBox.dataset.field, true);
				}
			});
		});

		// --- FUNÇÃO DE FEEDBACK VISUAL (TOAST) ---
		
		let toastTimeout;
		function showToast(message, isError = false) {
			let toast = document.getElementById('toast-notification');
			if (!toast) {
				toast = document.createElement('div');
				toast.id = 'toast-notification';
				document.body.appendChild(toast);
				Object.assign(toast.style, {
					position: 'fixed',
					bottom: '20px',
					left: '50%',
					transform: 'translateX(-50%)',
					padding: '12px 24px',
					borderRadius: '8px',
					color: 'white',
					fontWeight: '500',
					zIndex: '10000',
					transition: 'opacity 0.3s, transform 0.3s',
					opacity: '0',
					boxShadow: '0 4px 15px rgba(0,0,0,0.2)',
					pointerEvents: 'none'
				});
			}

			toast.textContent = message;
			toast.style.background = isError 
				? 'linear-gradient(135deg, #d32f2f, #b71c1c)' 
				: 'linear-gradient(135deg, #2e7d32, #1b5e20)';
			
			toast.style.opacity = '1';
			toast.style.transform = 'translateX(-50%) translateY(0)';

			clearTimeout(toastTimeout);
			toastTimeout = setTimeout(() => {
				toast.style.opacity = '0';
				toast.style.transform = 'translateX(-50%) translateY(20px)';
			}, 3000);
		}
	</script>
</body>
</html>


