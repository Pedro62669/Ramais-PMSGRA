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
			<?php if (!empty($login_error)): ?><div class="error"><?php echo htmlspecialchars($login_error); ?></div><?php endif; ?>
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
	<style>
		body{font-family:Arial,Helvetica,sans-serif;background:#f5f7f5;margin:0}
		.container{max-width:1100px;margin:24px auto;background:#fff;border:1px solid #e6efe6;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.06);padding:20px}
		h1{margin:0 0 16px;font-size:22px;color:#2e7d32}
		.topbar{display:flex;gap:12px;align-items:center;justify-content:space-between;margin-bottom:16px}
		select,input,button{padding:10px 12px;border:1px solid #e6efe6;border-radius:10px;font-size:14px}
		button.primary{background:#2e7d32;color:#fff;border:0}
		table{width:100%;border-collapse:collapse}
		th,td{border-bottom:1px solid #e6efe6;padding:10px;text-align:left}
		.actions{display:flex;gap:8px}
		.msg{color:#2e7d32;margin:8px 0 0 0}
		.err{color:#b00020}
		.form-inline{display:flex;gap:8px;flex-wrap:wrap}
	</style>
</head>
<body>
	<div class="container">
		<div class="topbar">
			<h1>Painel Administrativo</h1>
			<div>
				<a href="./index.php" style="text-decoration:none; margin-right:8px">Voltar ao site</a>
				<a href="./logout.php" style="text-decoration:none">Sair</a>
			</div>
		</div>
		<?php if (!empty($msg)): ?><div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

		<form method="get" class="form-inline" action="./admin.php">
			<label for="setor">Setor:</label>
			<select id="setor" name="setor" onchange="this.form.submit()">
				<?php foreach ($setores as $s): ?>
					<option value="<?php echo htmlspecialchars($s); ?>" <?php echo $s === $tabela_sel ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_',' ', htmlspecialchars($s))); ?></option>
				<?php endforeach; ?>
			</select>
		</form>

		<h2 style="margin-top:16px">Novo registro</h2>
		<form method="post" class="form-inline" action="./admin.php?setor=<?php echo urlencode($tabela_sel); ?>">
			<input type="hidden" name="crud" value="create">
			<input type="hidden" name="tabela" value="<?php echo htmlspecialchars($tabela_sel); ?>">
			<input type="text" name="sub_setor" placeholder="Sub-setor">
			<input type="text" name="descricao" placeholder="Descrição">
			<input type="text" name="falar_com" placeholder="Falar com">
			<input type="text" name="ramal" placeholder="Ramal">
			<button class="primary" type="submit">Adicionar</button>
		</form>

		<h2 style="margin-top:16px">Registros</h2>
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
							<input type="hidden" name="tabela" value="<?php echo htmlspecialchars($tabela_sel); ?>">
							<input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
							<input type="text" name="sub_setor" value="<?php echo htmlspecialchars($r['sub_setor'] ?? ''); ?>" style="min-width:140px">
					</td>
					<td><input type="text" name="descricao" value="<?php echo htmlspecialchars($r['descricao'] ?? ''); ?>" style="min-width:200px"></td>
					<td><input type="text" name="falar_com" value="<?php echo htmlspecialchars($r['falar_com'] ?? ''); ?>" style="min-width:160px"></td>
					<td><input type="text" name="ramal" value="<?php echo htmlspecialchars($r['ramal'] ?? ''); ?>" style="min-width:120px"></td>
					<td class="actions">
						<button class="primary" type="submit">Salvar</button>
						</form>
						<form method="post" action="./admin.php?setor=<?php echo urlencode($tabela_sel); ?>" onsubmit="return confirm('Remover este registro?');">
							<input type="hidden" name="crud" value="delete">
							<input type="hidden" name="tabela" value="<?php echo htmlspecialchars($tabela_sel); ?>">
							<input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
							<button type="submit" style="background:#b00020;color:#fff;border:0;border-radius:10px;padding:10px 12px">Excluir</button>
						</form>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p style="color:#6b7a6d;margin-top:8px">Exibindo no máximo 500 registros.</p>
	</div>
</body>
</html>

