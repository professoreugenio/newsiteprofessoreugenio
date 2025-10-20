<?php
define('BASEPATH', true);
$sessionLifetime = 8 * 60 * 60; // 8 horas em segundos
session_set_cookie_params($sessionLifetime, '/', '.professoreugenio.com', true, true);
session_start();
$local = "localhost";
$banco = "appsrcc_cursos";
$usuario = "appsrcc_admcurso";
$senha = "mastersysadmcurso2018";

$conexao = mysqli_connect($local, $usuario, $senha, $banco);
// localhost, usuário, senha e banco

if (!$conexao) {

  echo "Error: Falha ao conectar-se com o banco de dados MySQL." . PHP_EOL;
  echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
  echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
  exit;
}
