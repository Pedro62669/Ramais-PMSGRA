<?php
// Evitar cache para a página de login/painel
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
require_once __DIR__ . '/config.php';
start_app_session();

$csrf_token = get_csrf_token();
$csrf_error = false;

// Autenticação simples via POST (usuário/senha do ambiente)
$admin_user = getenv('ADMIN_USER') ?: 'pedro';
$admin_pass = getenv('ADMIN_PASS') ?: 'DK0104';

if (isset($_POST['action']) && $_POST['action'] === 'login') {
	$user = trim($_POST['user'] ?? '');
	$pass = trim($_POST['pass'] ?? '');
	if (hash_equals($admin_user, $user) && hash_equals($admin_pass, $pass)) {
		// Mitiga session fixation
		session_regenerate_id(true);
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
	<link rel="icon" type="image/png" href="<?= BASE_PATH ?>/ico.png">
	<link rel="apple-touch-icon" href="<?= BASE_PATH ?>/ico.png">
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
			<input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
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

// Validação CSRF para qualquer POST autenticado (exceto login)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
		$csrf_error = true;
	}
}

// Garantir que o setor Centro Administrativo exista (mesmo vazio) e que apareça na lista de setores
garantir_tabela_centro_administrativo($con);
limpar_cache_setores();

// Carregar lista de setores (tabelas) - usando função com cache
$setores = get_lista_setores($con);

// Tabelas do sistema que NÃO são setores de ramais (não têm as colunas de ramal,
// como sub_setor). Precisam ficar fora de qualquer listagem/consulta de ramais.
$tabelas_sistema = ['emails', 'sugestoes'];

// Apenas as tabelas que realmente são setores de ramais
$setores_ramais = array_values(array_filter($setores, fn($s) => !in_array($s, $tabelas_sistema, true)));

// Organiza setores para UX no Admin
$setores_centro_admin = array_values(array_filter($setores, function($s) { return $s === 'centro_administrativo'; }));
$setores_externos_admin = array_values(array_filter($setores, function($s) { return $s === 'externos'; }));
$setores_internos_admin = array_values(array_filter($setores, function($s) use ($tabelas_sistema) {
	return $s !== 'externos' && $s !== 'centro_administrativo' && !in_array($s, $tabelas_sistema, true);
}));

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

if (!$csrf_error && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crud_email'])) {
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

// CRUD ações para sugestões enviadas pelos usuários
$msg_sugestao = '';
$msg_sugestao_type = 'success';
if (!$csrf_error && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crud_sugestao'])) {
	$acao_sugestao = $_POST['crud_sugestao'];
	$id_sugestao = intval($_POST['id_sugestao'] ?? 0);

	if ($id_sugestao > 0) {
		if ($acao_sugestao === 'status') {
			$novo_status = $_POST['status'] ?? '';
			$resultado = atualizar_status_sugestao($id_sugestao, $novo_status, $con);
			$msg_sugestao = $resultado['message'];
			$msg_sugestao_type = $resultado['success'] ? 'success' : 'error';
		} elseif ($acao_sugestao === 'delete') {
			$resultado = remover_sugestao($id_sugestao, $con);
			$msg_sugestao = $resultado['message'];
			$msg_sugestao_type = $resultado['success'] ? 'success' : 'error';
		}
	} else {
		$msg_sugestao = 'ID inválido';
		$msg_sugestao_type = 'error';
	}
}

// Carrega as sugestões e a contagem de não lidas
$sugestoes = listar_sugestoes($con);
$sugestoes_novas = contar_sugestoes_novas($con);

// CRUD ações
$msg = '';
$msg_type = 'success'; // 'success' ou 'error'
if (!$csrf_error && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crud'])) {
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
			header('Location: ./admin.php?setor=' . urlencode($tabela) . '&msg=' . urlencode('Ramal criado com sucesso'));
			exit;
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
$setor_param = $_GET['setor'] ?? 'todos';
$tabela_sel  = $setor_param === 'todos' ? 'todos' : sanitize_table($setor_param, $setores_ramais);
if ($tabela_sel === null) $tabela_sel = 'todos';

$q       = trim($_GET['q'] ?? '');
$q_param = $q !== '' ? '&q=' . urlencode($q) : '';
$sort    = $_GET['sort'] ?? 'ramal_desc';
if (!in_array($sort, ['ramal_asc', 'ramal_desc', 'default'], true)) $sort = 'ramal_desc';
$sort_param = $sort !== 'default' ? '&sort=' . urlencode($sort) : '';
$order_by = match($sort) {
	'ramal_asc'  => 'ORDER BY CAST(ramal AS UNSIGNED) ASC, sub_setor, descricao',
	'ramal_desc' => 'ORDER BY CAST(ramal AS UNSIGNED) DESC, sub_setor, descricao',
	default      => 'ORDER BY sub_setor, descricao',
};

$registros = [];
if ($tabela_sel === 'todos') {
	$like = $q !== '' ? '%' . $q . '%' : null;
	foreach ($setores_ramais as $t) {
		if ($like !== null) {
			$setor_bate = stripos(str_replace('_', ' ', $t), $q) !== false;
			if ($setor_bate) {
				$stmt = $con->prepare("SELECT id, sub_setor, descricao, falar_com, ramal, emergencia, oculto, principal FROM `$t`");
			} else {
				$stmt = $con->prepare("SELECT id, sub_setor, descricao, falar_com, ramal, emergencia, oculto, principal FROM `$t` WHERE (COALESCE(sub_setor,'') LIKE ? OR COALESCE(descricao,'') LIKE ? OR COALESCE(falar_com,'') LIKE ? OR COALESCE(ramal,'') LIKE ?)");
				$stmt->bind_param('ssss', $like, $like, $like, $like);
			}
		} else {
			$stmt = $con->prepare("SELECT id, sub_setor, descricao, falar_com, ramal, emergencia, oculto, principal FROM `$t`");
		}
		$stmt->execute();
		$res = $stmt->get_result();
		while ($row = $res->fetch_assoc()) {
			$row['_tabela'] = $t;
			$registros[] = $row;
		}
		$stmt->close();
	}
	usort($registros, function($a, $b) use ($sort) {
		$ra = (int)preg_replace('/\D/', '', $a['ramal'] ?? '0');
		$rb = (int)preg_replace('/\D/', '', $b['ramal'] ?? '0');
		if ($sort === 'ramal_asc')  return $ra <=> $rb;
		if ($sort === 'ramal_desc') return $rb <=> $ra;
		$cmp = strcmp($a['sub_setor'] ?? '', $b['sub_setor'] ?? '');
		return $cmp !== 0 ? $cmp : strcmp($a['descricao'] ?? '', $b['descricao'] ?? '');
	});
	$registros = array_slice($registros, 0, 500);
} elseif ($tabela_sel) {
	if ($q !== '') {
		$like = '%' . $q . '%';
		$stmt = $con->prepare("SELECT id, sub_setor, descricao, falar_com, ramal, emergencia, oculto, principal FROM `$tabela_sel` WHERE (COALESCE(sub_setor,'') LIKE ? OR COALESCE(descricao,'') LIKE ? OR COALESCE(falar_com,'') LIKE ? OR COALESCE(ramal,'') LIKE ?) {$order_by} LIMIT 500");
		$stmt->bind_param('ssss', $like, $like, $like, $like);
	} else {
		$stmt = $con->prepare("SELECT id, sub_setor, descricao, falar_com, ramal, emergencia, oculto, principal FROM `$tabela_sel` {$order_by} LIMIT 500");
	}
	$stmt->execute();
	$res = $stmt->get_result();
	while ($row = $res->fetch_assoc()) {
		$row['_tabela'] = $tabela_sel;
		$registros[] = $row;
	}
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
	<link rel="icon" type="image/png" href="<?= BASE_PATH ?>/ico.png">
	<link rel="apple-touch-icon" href="<?= BASE_PATH ?>/ico.png">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="./admin.css">
</head>
<body>
	<div class="container">
		<script>
			window.csrfToken = '<?php echo h($csrf_token); ?>';
		</script>
		<div class="header">
			<h1>⚙️ Painel Administrativo</h1>
			<div class="header-actions">
				<a href="./index.php">← Voltar ao site</a>
				<a href="./logout.php">Sair</a>
			</div>
		</div>
		
		<div class="content">
			<?php if ($csrf_error): ?>
				<div class="msg error" style="margin: 20px 32px;">
					⚠️ Token de segurança inválido (CSRF). Recarregue a página e tente novamente.
				</div>
			<?php endif; ?>
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
				<button class="tab" data-tab="sugestoes" onclick="showTab('sugestoes', this)">💡 Sugestões<?php if ($sugestoes_novas > 0): ?> <span class="tab-badge"><?php echo (int)$sugestoes_novas; ?></span><?php endif; ?></button>
			</div>
			
			<!-- Tab: Ramais -->
			<div id="tab-ramais" class="tab-content active">

				<?php
				$stmt_centro_count = $con->query("SELECT COUNT(*) as total FROM centro_administrativo");
				$total_centro = $stmt_centro_count ? (int)($stmt_centro_count->fetch_assoc()['total'] ?? 0) : 0;
				$arquivo_centro_existe = file_exists(__DIR__ . '/novos_ramais.sql');
				if ($total_centro === 0 && $arquivo_centro_existe):
				?>
					<div class="section" style="background: #eef7ff; border-left: 4px solid #1976d2;">
						<h2 class="section-title">🏛️ Importar Ramais — Centro Administrativo</h2>
						<p style="color: #0b4f6c; margin-bottom: 16px;">
							A tabela do <strong>Centro Administrativo</strong> está vazia. Você pode importar os dados do arquivo <code>novos_ramais.sql</code>.
						</p>
						<a href="./importar_centro_administrativo.php" style="display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
							📥 Importar Centro Administrativo
						</a>
					</div>
				<?php endif; ?>

				<!-- Cadastro -->
				<div class="section" id="section-novo-ramal">
					<h2 class="section-title" style="flex-wrap:wrap; gap:8px;">
						<span>➕ Cadastrar Novo Ramal</span>
						<span id="badge-subsetor" class="badge-subsetor" style="display:none;">
							<span>Sub-setor: </span><strong id="badge-subsetor-texto"></strong>
							<button type="button" onclick="limparSubSetorForm()" title="Limpar pré-preenchimento">×</button>
						</span>
					</h2>
					<form method="post" class="form-inline" action="./admin.php">
						<input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
						<input type="hidden" name="crud" value="create">
						<div class="form-group">
							<label for="create_tabela">Setor</label>
							<select id="create_tabela" name="tabela">
								<?php if (!empty($setores_centro_admin)): ?>
									<optgroup label="🏛️ Centro Administrativo">
										<?php foreach ($setores_centro_admin as $s): ?>
											<option value="<?php echo h($s); ?>" <?php echo $s === $tabela_sel ? 'selected' : ''; ?>><?php echo formatar_nome_setor($s); ?></option>
										<?php endforeach; ?>
									</optgroup>
								<?php endif; ?>
								<?php if (!empty($setores_externos_admin)): ?>
									<optgroup label="📞 Ramais Externos">
										<?php foreach ($setores_externos_admin as $s): ?>
											<option value="<?php echo h($s); ?>" <?php echo $s === $tabela_sel ? 'selected' : ''; ?>><?php echo formatar_nome_setor($s); ?></option>
										<?php endforeach; ?>
									</optgroup>
								<?php endif; ?>
								<?php if (!empty($setores_internos_admin)): ?>
									<optgroup label="🏢 Setores Internos">
										<?php foreach ($setores_internos_admin as $s): ?>
											<option value="<?php echo h($s); ?>" <?php echo $s === $tabela_sel ? 'selected' : ''; ?>><?php echo formatar_nome_setor($s); ?></option>
										<?php endforeach; ?>
									</optgroup>
								<?php endif; ?>
							</select>
						</div>
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

				<!-- Busca + Registros -->
				<div class="section">
					<h2 class="section-title">🔍 Buscar e Gerenciar Ramais</h2>
					<form method="get" action="./admin.php" style="margin-bottom: 20px;">
						<?php if ($sort !== 'default'): ?>
							<input type="hidden" name="sort" value="<?php echo h($sort); ?>">
						<?php endif; ?>
						<div class="form-group" style="max-width: 280px;">
							<label for="setor">Setor</label>
							<select id="setor" name="setor" onchange="this.form.submit()">
								<option value="todos" <?php echo $tabela_sel === 'todos' ? 'selected' : ''; ?>>📋 Todos os setores</option>
								<?php if (!empty($setores_centro_admin)): ?>
									<optgroup label="🏛️ Centro Administrativo">
										<?php foreach ($setores_centro_admin as $s): ?>
											<option value="<?php echo h($s); ?>" <?php echo $s === $tabela_sel ? 'selected' : ''; ?>><?php echo formatar_nome_setor($s); ?></option>
										<?php endforeach; ?>
									</optgroup>
								<?php endif; ?>
								<?php if (!empty($setores_externos_admin)): ?>
									<optgroup label="📞 Ramais Externos">
										<?php foreach ($setores_externos_admin as $s): ?>
											<option value="<?php echo h($s); ?>" <?php echo $s === $tabela_sel ? 'selected' : ''; ?>><?php echo formatar_nome_setor($s); ?></option>
										<?php endforeach; ?>
									</optgroup>
								<?php endif; ?>
								<?php if (!empty($setores_internos_admin)): ?>
									<optgroup label="🏢 Setores Internos">
										<?php foreach ($setores_internos_admin as $s): ?>
											<option value="<?php echo h($s); ?>" <?php echo $s === $tabela_sel ? 'selected' : ''; ?>><?php echo formatar_nome_setor($s); ?></option>
										<?php endforeach; ?>
									</optgroup>
								<?php endif; ?>
							</select>
						</div>
						<div class="form-group" style="max-width: 420px;">
							<label for="q">Buscar no setor</label>
							<input type="text" id="q" name="q" placeholder="Nome, ramal, descrição ou sub-setor..." value="<?php echo h($q); ?>">
						</div>
						<div class="form-group">
							<label>&nbsp;</label>
							<div style="display:flex; gap:10px; flex-wrap:wrap;">
								<button class="primary" type="submit" style="padding: 12px 18px;">🔍 Buscar</button>
								<?php if (!empty($q)): ?>
									<a href="./admin.php?setor=<?php echo urlencode($tabela_sel); ?>" style="padding: 12px 18px; border-radius: 8px; text-decoration: none; background: #edf2f7; color: #2d3748; font-weight: 600; border: 2px solid #e2e8f0;">Limpar</a>
								<?php endif; ?>
							</div>
						</div>
					</form>
					<?php if (!empty($q)): ?>
						<p class="info-text" style="margin-bottom: 16px;">🔎 Filtro ativo: <strong><?php echo h($q); ?></strong> — <?php echo count($registros); ?> resultado(s)</p>
					<?php endif; ?>
					<?php $mostrar_setor = ($tabela_sel === 'todos'); ?>
					<div class="table-wrapper">
						<table>
					<thead>
						<?php
						$base_link = './admin.php?setor=' . urlencode($tabela_sel) . ($q !== '' ? '&q=' . urlencode($q) : '');
						$next_sort  = $sort === 'ramal_asc' ? 'ramal_desc' : 'ramal_asc';
						$sort_icon  = $sort === 'ramal_asc' ? '↑' : ($sort === 'ramal_desc' ? '↓' : '⇅');
						$sort_ativo = in_array($sort, ['ramal_asc', 'ramal_desc'], true) ? 'ativo' : '';
						?>
						<tr>
							<th>ID</th>
							<th>Sub-setor</th>
							<th>Descrição</th>
							<th>Falar com</th>
							<th><a href="<?= h($base_link) ?>&sort=<?= $next_sort ?>" class="sort-link <?= $sort_ativo ?>" title="Ordenar por ramal">Ramal <?= $sort_icon ?></a></th>
							<?php if ($mostrar_setor): ?><th>Setor</th><?php endif; ?>
							<th>Status</th>
							<th>Ações</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($registros as $r):
							$r_tab = $r['_tabela'];
						?>
						<tr id="row-<?php echo (int)$r['id']; ?>" class="<?php echo ($r['emergencia'] ?? 0) ? 'emergencia-row' : ''; ?> <?php echo ($r['oculto'] ?? 0) ? 'oculto-row' : ''; ?> <?php echo ($r['principal'] ?? 0) ? 'principal-row' : ''; ?>">
							<td><?php echo (int)$r['id']; ?></td>
							<td>
								<form method="post" class="form-inline status-form" action="./admin.php?setor=<?php echo urlencode($r_tab); ?><?php echo h($q_param); ?>">
									<input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
									<input type="hidden" name="crud" value="update">
									<input type="hidden" name="tabela" value="<?php echo h($r_tab); ?>">
									<input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
									<input type="text" name="sub_setor" value="<?php echo h($r['sub_setor'] ?? ''); ?>">
							</td>
							<td><input type="text" name="descricao" value="<?php echo h($r['descricao'] ?? ''); ?>"></td>
							<td><input type="text" name="falar_com" value="<?php echo h($r['falar_com'] ?? ''); ?>"></td>
							<td><input type="text" name="ramal" value="<?php echo h($r['ramal'] ?? ''); ?>"></td>
							<?php if ($mostrar_setor): ?>
							<td style="white-space:nowrap; font-size:13px; color:#4a5568;"><?php echo h(formatar_nome_setor($r_tab)); ?></td>
							<?php endif; ?>
							<td>
								<div style="display: flex; flex-direction: column; gap: 8px;">
									<label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer;">
										<input type="checkbox" name="emergencia" class="status-checkbox-row" data-id="<?php echo (int)$r['id']; ?>" data-tabela="<?php echo h($r_tab); ?>" data-field="emergencia" <?php echo ($r['emergencia'] ?? 0) ? 'checked' : ''; ?> style="width: auto; margin: 0;">
										<span style="color: <?php echo ($r['emergencia'] ?? 0) ? '#d32f2f' : '#666'; ?>; font-weight: <?php echo ($r['emergencia'] ?? 0) ? '600' : '400'; ?>;">🚨 Emergência</span>
									</label>
									<label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer;">
										<input type="checkbox" name="oculto" class="status-checkbox-row" data-id="<?php echo (int)$r['id']; ?>" data-tabela="<?php echo h($r_tab); ?>" data-field="oculto" <?php echo ($r['oculto'] ?? 0) ? 'checked' : ''; ?> style="width: auto; margin: 0;">
										<span style="color: <?php echo ($r['oculto'] ?? 0) ? '#ff9800' : '#666'; ?>; font-weight: <?php echo ($r['oculto'] ?? 0) ? '600' : '400'; ?>;">👁️ Oculto</span>
									</label>
									<label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer;">
										<input type="checkbox" name="principal" class="status-checkbox-row" data-id="<?php echo (int)$r['id']; ?>" data-tabela="<?php echo h($r_tab); ?>" data-field="principal" <?php echo ($r['principal'] ?? 0) ? 'checked' : ''; ?> style="width: auto; margin: 0;">
										<span style="color: <?php echo ($r['principal'] ?? 0) ? '#1976d2' : '#666'; ?>; font-weight: <?php echo ($r['principal'] ?? 0) ? '600' : '400'; ?>;">⭐ Principal</span>
									</label>
								</div>
							</td>
							<td class="actions">
								<button class="save" type="submit">💾 Salvar</button>
								</form>
								<button class="usar" type="button" data-sub-setor="<?php echo h($r['sub_setor'] ?? ''); ?>" data-tabela="<?php echo h($r_tab); ?>" title="Pré-preencher cadastro com este sub-setor">↗ Usar</button>
								<button class="transfer" type="button" data-id="<?php echo (int)$r['id']; ?>" data-tabela="<?php echo h($r_tab); ?>" data-contato="<?php echo h(formatar_contato($r)); ?>">↔️ Transferir</button>
								<form method="post" action="./admin.php?setor=<?php echo urlencode($r_tab); ?><?php echo h($q_param); ?>" onsubmit="return confirm('Tem certeza que deseja remover este registro?');" style="display:inline">
									<input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
									<input type="hidden" name="crud" value="delete">
									<input type="hidden" name="tabela" value="<?php echo h($r_tab); ?>">
									<input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
									<button class="delete" type="submit">🗑️ Excluir</button>
								</form>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
						</table>
					</div>
					<p class="info-text">📊 <?php echo count($registros); ?> registro(s) exibido(s) <?php echo $tabela_sel === 'todos' ? 'em todos os setores' : 'neste setor'; ?> (máx. 500)</p>
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
						<input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
						
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
												<input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
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
												<input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
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

			<!-- Tab: Sugestões -->
			<div id="tab-sugestoes" class="tab-content">
				<?php if (!empty($msg_sugestao)): ?>
					<div class="msg <?php echo $msg_sugestao_type === 'error' ? 'error' : ''; ?>">
						<?php echo $msg_sugestao_type === 'error' ? '⚠️' : '✓'; ?>
						<?php echo h($msg_sugestao); ?>
					</div>
				<?php endif; ?>

				<div class="section">
					<h2 class="section-title">💡 Sugestões dos usuários
						<span style="font-weight:500;color:#718096;font-size:14px;">
							(<?php echo count($sugestoes); ?> no total<?php echo $sugestoes_novas > 0 ? ', ' . (int)$sugestoes_novas . ' nova(s)' : ''; ?>)
						</span>
					</h2>

					<?php if (empty($sugestoes)): ?>
						<p style="color:#718096;text-align:center;padding:24px;">Nenhuma sugestão recebida ainda.</p>
					<?php else: ?>
						<div class="sugestoes-lista">
						<?php foreach ($sugestoes as $s):
							$st = $s['status'] ?? 'nova';
							$nome_sug = trim((string)($s['nome'] ?? ''));
							$data_sug = !empty($s['created_at']) ? date('d/m/Y H:i', strtotime($s['created_at'])) : '';
						?>
							<div class="sugestao-card sugestao-status-<?php echo h($st); ?>">
								<div class="sugestao-card-topo">
									<div class="sugestao-meta">
										<strong><?php echo $nome_sug !== '' ? h($nome_sug) : 'Anônimo'; ?></strong>
										<?php if ($data_sug !== ''): ?><span class="sugestao-data"><?php echo h($data_sug); ?></span><?php endif; ?>
									</div>
									<span class="sugestao-badge badge-<?php echo h($st); ?>"><?php echo h(ucfirst($st)); ?></span>
								</div>
								<p class="sugestao-msg"><?php echo nl2br(h($s['mensagem'] ?? '')); ?></p>
								<div class="sugestao-acoes">
									<?php
									$labels_status = ['lida' => '👁 Marcar como lida', 'resolvida' => '✓ Resolvida', 'nova' => '↩ Voltar p/ nova'];
									foreach ($labels_status as $status_val => $label_status):
										if ($status_val === $st) continue;
									?>
										<form method="post" action="./admin.php?tab=sugestoes" style="display:inline">
											<input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
											<input type="hidden" name="crud_sugestao" value="status">
											<input type="hidden" name="id_sugestao" value="<?php echo (int)$s['id']; ?>">
											<input type="hidden" name="status" value="<?php echo h($status_val); ?>">
											<button type="submit" class="sug-btn"><?php echo $label_status; ?></button>
										</form>
									<?php endforeach; ?>
									<form method="post" action="./admin.php?tab=sugestoes" style="display:inline" onsubmit="return confirm('Remover esta sugestão permanentemente?');">
										<input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
										<input type="hidden" name="crud_sugestao" value="delete">
										<input type="hidden" name="id_sugestao" value="<?php echo (int)$s['id']; ?>">
										<button type="submit" class="sug-btn sug-btn-remover">🗑 Remover</button>
									</form>
								</div>
							</div>
						<?php endforeach; ?>
						</div>
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
					<input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
					<input type="hidden" name="crud" value="transfer">
					<input type="hidden" name="tabela" id="tabelaOrigem">
					<input type="hidden" name="id" id="ramalId">
					<div class="form-group">
						<label for="tabelaDestino">Transferir para o setor:</label>
						<select id="tabelaDestino" name="tabela_destino" required>
							<option value="">Selecione um setor...</option>
							<?php if (!empty($setores_centro_admin)): ?>
								<optgroup label="🏛️ Centro Administrativo">
									<?php foreach ($setores_centro_admin as $s): ?>
										<option value="<?php echo h($s); ?>"><?php echo formatar_nome_setor($s); ?></option>
									<?php endforeach; ?>
								</optgroup>
							<?php endif; ?>
							<?php if (!empty($setores_externos_admin)): ?>
								<optgroup label="📞 Ramais Externos">
									<?php foreach ($setores_externos_admin as $s): ?>
										<option value="<?php echo h($s); ?>"><?php echo formatar_nome_setor($s); ?></option>
									<?php endforeach; ?>
								</optgroup>
							<?php endif; ?>
							<?php if (!empty($setores_internos_admin)): ?>
								<optgroup label="🏢 Setores Internos">
									<?php foreach ($setores_internos_admin as $s): ?>
										<option value="<?php echo h($s); ?>"><?php echo formatar_nome_setor($s); ?></option>
									<?php endforeach; ?>
								</optgroup>
							<?php endif; ?>
						</select>
						<div class="warning-box-small">
							<strong class="warning-box-small-title">ℹ️ Informação</strong>
							<span class="warning-box-small-text">
								Ramais <strong>internos</strong> só podem ser transferidos para setores internos/centro. A restrição é validada automaticamente ao confirmar.
							</span>
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

	<script src="./admin.js"></script>
	<script>
		(function () {
			var t = new URLSearchParams(location.search).get('tab');
			if (t && document.getElementById('tab-' + t) && typeof showTab === 'function') {
				showTab(t);
			}
		})();
	</script>
</body>
</html>


