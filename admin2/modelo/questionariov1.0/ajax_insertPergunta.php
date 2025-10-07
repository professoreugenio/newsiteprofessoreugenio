<?php

declare(strict_types=1);
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
header('Content-Type: application/json; charset=UTF-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Método não permitido.');
    }

    $idpub = isset($_POST['idpublicacao']) ? (int)$_POST['idpublicacao'] : 0;
    $tipo  = isset($_POST['tipocq']) ? (int)$_POST['tipocq'] : 0;
    $titulo = trim((string)($_POST['titulocq'] ?? ''));

    if ($idpub <= 0) throw new InvalidArgumentException('Publicação inválida.');
    if (!in_array($tipo, [1, 2, 3], true)) throw new InvalidArgumentException('Tipo inválido.');
    if ($titulo === '') throw new InvalidArgumentException('Informe o título.');

    $pdo = config::connect();
    $pdo->beginTransaction();

    // próxima ordem
    $stOrd = $pdo->prepare("SELECT COALESCE(MAX(ordemcq),0)+1 FROM a_curso_questionario WHERE idpublicacaocq = :p");
    $stOrd->bindValue(':p', $idpub, PDO::PARAM_INT);
    $stOrd->execute();
    $ordem = (int)$stOrd->fetchColumn();
    if ($ordem < 1) $ordem = 1;

    $opcaoa = $opcaob = $opcaoc = $opcaod = null;
    $resposta = null;

    if ($tipo === 1) {
        $resposta = trim((string)($_POST['respostacq'] ?? ''));
        if ($resposta === '') throw new InvalidArgumentException('Informe a resposta.');
    }

    if ($tipo === 2) {
        $opcaoa = trim((string)($_POST['opcaoa'] ?? ''));
        $opcaob = trim((string)($_POST['opcaob'] ?? ''));
        $opcaoc = trim((string)($_POST['opcaoc'] ?? ''));
        $opcaod = trim((string)($_POST['opcaod'] ?? ''));
        $correta = strtoupper(trim((string)($_POST['correta'] ?? '')));
        if ($opcaoa === '' || $opcaob === '' || $opcaoc === '' || $opcaod === '') {
            throw new InvalidArgumentException('Preencha todas as opções A, B, C e D.');
        }
        if (!in_array($correta, ['A', 'B', 'C', 'D'], true)) {
            throw new InvalidArgumentException('Selecione a opção correta.');
        }
        $resposta = $correta; // Armazena apenas a letra
    }

    if ($tipo === 3) {
        $opcaoa = trim((string)($_POST['opcaoa'] ?? ''));
        $opcaob = trim((string)($_POST['opcaob'] ?? ''));
        $opcaoc = trim((string)($_POST['opcaoc'] ?? ''));
        $opcaod = trim((string)($_POST['opcaod'] ?? ''));
        $vf_a = strtoupper(trim((string)($_POST['vf_a'] ?? '')));
        $vf_b = strtoupper(trim((string)($_POST['vf_b'] ?? '')));
        $vf_c = strtoupper(trim((string)($_POST['vf_c'] ?? '')));
        $vf_d = strtoupper(trim((string)($_POST['vf_d'] ?? '')));

        if ($opcaoa === '' || $opcaob === '' || $opcaoc === '' || $opcaod === '') {
            throw new InvalidArgumentException('Preencha todas as opções A, B, C e D.');
        }
        foreach ([$vf_a, $vf_b, $vf_c, $vf_d] as $vf) {
            if (!in_array($vf, ['V', 'F'], true)) throw new InvalidArgumentException('Selecione V/F para todas as opções.');
        }
        // Armazena padrão A=V;B=F;C=V;D=F
        $resposta = "A={$vf_a};B={$vf_b};C={$vf_c};D={$vf_d}";
    }

    $data = date('Y-m-d');
    $hora = date('H:i:s');

    $sql = "INSERT INTO a_curso_questionario
            (idpublicacaocq, tipocq, titulocq, idmodulocq, ordemcq, respostacq,
             opcaoa, opcaob, opcaoc, opcaod, visivelcq, datacq, horacq)
            VALUES
            (:idpub, :tipo, :titulo, :idmod, :ordem, :resposta,
             :oa, :ob, :oc, :od, 1, :data, :hora)";
    $st = $pdo->prepare($sql);
    $st->bindValue(':idpub', $idpub, PDO::PARAM_INT);
    $st->bindValue(':tipo', $tipo, PDO::PARAM_INT);
    $st->bindValue(':titulo', $titulo, PDO::PARAM_STR);
    // Se não utiliza módulo, pode gravar 0
    $st->bindValue(':idmod', 0, PDO::PARAM_INT);
    $st->bindValue(':ordem', $ordem, PDO::PARAM_INT);
    $st->bindValue(':resposta', $resposta, PDO::PARAM_STR);
    $st->bindValue(':oa', $opcaoa, $opcaoa !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $st->bindValue(':ob', $opcaob, $opcaob !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $st->bindValue(':oc', $opcaoc, $opcaoc !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $st->bindValue(':od', $opcaod, $opcaod !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $st->bindValue(':data', $data, PDO::PARAM_STR);
    $st->bindValue(':hora', $hora, PDO::PARAM_STR);
    $st->execute();

    $lastId = (int)$pdo->lastInsertId();
    $pdo->commit();

    echo json_encode(['success' => true, 'id' => $lastId], JSON_UNESCAPED_UNICODE);
} catch (Throwable $th) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $th->getMessage()], JSON_UNESCAPED_UNICODE);
}
