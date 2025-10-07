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

    $cod   = isset($_POST['codigo']) ? (int)$_POST['codigo'] : 0;
    $idpub = isset($_POST['idpublicacao']) ? (int)$_POST['idpublicacao'] : 0;
    $tipo  = isset($_POST['tipocq']) ? (int)$_POST['tipocq'] : 0;
    $titulo = trim((string)($_POST['titulocq'] ?? ''));

    if ($cod <= 0 || $idpub <= 0) throw new InvalidArgumentException('Identificadores inválidos.');
    if (!in_array($tipo, [1, 2, 3], true)) throw new InvalidArgumentException('Tipo inválido.');
    if ($titulo === '') throw new InvalidArgumentException('Informe o título.');

    // Campos opcionais
    $oa = $ob = $oc = $od = null;
    $resposta = null;

    if ($tipo === 1) {
        $resposta = trim((string)($_POST['respostacq'] ?? ''));
        if ($resposta === '') throw new InvalidArgumentException('Informe a resposta.');
    }

    if ($tipo === 2) {
        $oa = trim((string)($_POST['opcaoa'] ?? ''));
        $ob = trim((string)($_POST['opcaob'] ?? ''));
        $oc = trim((string)($_POST['opcaoc'] ?? ''));
        $od = trim((string)($_POST['opcaod'] ?? ''));
        $correta = strtoupper(trim((string)($_POST['correta'] ?? '')));
        if ($oa === '' || $ob === '' || $oc === '' || $od === '') {
            throw new InvalidArgumentException('Preencha A, B, C e D.');
        }
        if (!in_array($correta, ['A', 'B', 'C', 'D'], true)) {
            throw new InvalidArgumentException('Selecione a opção correta.');
        }
        $resposta = $correta; // armazena a letra
    }

    if ($tipo === 3) {
        $oa = trim((string)($_POST['opcaoa'] ?? ''));
        $ob = trim((string)($_POST['opcaob'] ?? ''));
        $oc = trim((string)($_POST['opcaoc'] ?? ''));
        $od = trim((string)($_POST['opcaod'] ?? ''));
        $vf_a = strtoupper(trim((string)($_POST['vf_a'] ?? '')));
        $vf_b = strtoupper(trim((string)($_POST['vf_b'] ?? '')));
        $vf_c = strtoupper(trim((string)($_POST['vf_c'] ?? '')));
        $vf_d = strtoupper(trim((string)($_POST['vf_d'] ?? '')));
        if ($oa === '' || $ob === '' || $oc === '' || $od === '') {
            throw new InvalidArgumentException('Preencha A, B, C e D.');
        }
        foreach ([$vf_a, $vf_b, $vf_c, $vf_d] as $vf) {
            if (!in_array($vf, ['V', 'F'], true)) {
                throw new InvalidArgumentException('Selecione V/F para todas as opções.');
            }
        }
        $resposta = "A={$vf_a};B={$vf_b};C={$vf_c};D={$vf_d}";
    }

    $pdo = config::connect();

    $sql = "UPDATE a_curso_questionario
            SET tipocq = :tipo,
                titulocq = :titulo,
                respostacq = :resposta,
                opcaoa = :oa,
                opcaob = :ob,
                opcaoc = :oc,
                opcaod = :od
            WHERE codigoquestionario = :cod
              AND idpublicacaocq = :idpub";

    $st = $pdo->prepare($sql);
    $st->bindValue(':tipo', $tipo, PDO::PARAM_INT);
    $st->bindValue(':titulo', $titulo, PDO::PARAM_STR);
    $st->bindValue(':resposta', $resposta, PDO::PARAM_STR);
    // Se nulos, grava NULL
    if ($oa !== null && $oa !== '') {
        $st->bindValue(':oa', $oa, PDO::PARAM_STR);
    } else {
        $st->bindValue(':oa', null, PDO::PARAM_NULL);
    }
    if ($ob !== null && $ob !== '') {
        $st->bindValue(':ob', $ob, PDO::PARAM_STR);
    } else {
        $st->bindValue(':ob', null, PDO::PARAM_NULL);
    }
    if ($oc !== null && $oc !== '') {
        $st->bindValue(':oc', $oc, PDO::PARAM_STR);
    } else {
        $st->bindValue(':oc', null, PDO::PARAM_NULL);
    }
    if ($od !== null && $od !== '') {
        $st->bindValue(':od', $od, PDO::PARAM_STR);
    } else {
        $st->bindValue(':od', null, PDO::PARAM_NULL);
    }
    $st->bindValue(':cod', $cod, PDO::PARAM_INT);
    $st->bindValue(':idpub', $idpub, PDO::PARAM_INT);
    $st->execute();

    if ($st->rowCount() < 1) {
        // Pode já estar com os mesmos dados; ainda assim consideramos sucesso.
        echo json_encode(['success' => true, 'message' => 'Nenhuma alteração ou já atualizado.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $th) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $th->getMessage()], JSON_UNESCAPED_UNICODE);
}
