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


