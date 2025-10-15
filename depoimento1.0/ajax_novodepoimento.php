<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 1)); // pasta raiz do site
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
header('Content-Type: application/json; charset=utf-8');
@date_default_timezone_set('America/Fortaleza');

function decryptUserEnc(string $enc): int
{
    if ($enc === '') return 0;
    try {
        $dec = encrypt($enc, 'd');
        if (strpos($dec, '&') !== false) {
            $p = explode('&', $dec);
            $id = (int)($p[0] ?? 0);
        } else {
            $id = (int)$dec;
        }
        return $id > 0 ? $id : 0;
    } catch (Throwable $e) {
        return 0;
    }
}

try {
    $idUserEnc = $_POST['idUserEnc'] ?? '';
    $texto     = trim((string)($_POST['texto'] ?? ''));
    $permissao = isset($_POST['permissao']) ? (int)$_POST['permissao'] : null;

    $idAluno = decryptUserEnc($idUserEnc);
    if ($idAluno <= 0) throw new Exception('Usuário inválido.');
    if ($texto === '') throw new Exception('Escreva seu depoimento.');
    if ($permissao !== 0 && $permissao !== 1) throw new Exception('Informe a permissão (permitir/não permitir).');

    $st = config::connect()->prepare("
    INSERT INTO a_curso_forum (idusuarioCF, textoCF, permissaoCF, dataCF, horaCF)
    VALUES (:u, :t, :p, :d, :h)
  ");
    $st->execute([
        ':u' => $idAluno,
        ':t' => $texto,
        ':p' => $permissao,
        ':d' => date('Y-m-d'),
        ':h' => date('H:i:s'),
    ]);

    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
