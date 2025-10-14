<?php

/**
 * afiliados1.0/ajax_afiliadosProdutoUpdate.php
 * Atualiza produto de afiliado.
 * Requer: POST { id (encrypt ou puro), nomeap, valorap, comissaoap, visivelap }
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php'; // manter para compat, mas autenticação real abaixo é por COOKIE

$response = ['ok' => false, 'msg' => ''];

function json_out(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Converte "000.000.000,00" => float decimal com ponto (padrão BR -> DB)
 */
function money_to_decimal(string $v): float
{
    $v = trim($v);
    // remove separador de milhar (.)
    $v = str_replace('.', '', $v);
    // troca vírgula por ponto
    $v = str_replace(',', '.', $v);
    // remove espaços
    $v = str_replace(' ', '', $v);
    return is_numeric($v) ? (float)$v : 0.0;
}

try {
    // ===== Autenticação por COOKIE (sem SESSION) =====
    if (!empty($_COOKIE['adminstart'])) {
        $decUser = function_exists('encrypt') ? encrypt($_COOKIE['adminstart'], 'd') : $_COOKIE['adminstart'];
    } elseif (!empty($_COOKIE['startusuario'])) {
        $decUser = function_exists('encrypt') ? encrypt($_COOKIE['startusuario'], 'd') : $_COOKIE['startusuario'];
    } else {
        throw new Exception('Usuário não autenticado (cookies ausentes).');
    }

    if (!$decUser || strpos((string)$decUser, '&') === false) {
        throw new Exception('Token de usuário inválido.');
    }
    $expUser = explode('&', (string)$decUser);
    $idUser  = (int)($expUser[0] ?? 0);
    if ($idUser <= 0) {
        throw new Exception('Usuário inválido.');
    }

    // ===== Entrada =====
    $idParam      = (string)($_POST['id'] ?? '');
    $nomeap       = trim((string)($_POST['nomeap'] ?? ''));
    $valorapBr    = (string)($_POST['valorap'] ?? '0,00');
    $comissaoBr   = (string)($_POST['comissaoap'] ?? '0,00');
    $visivelapStr = (string)($_POST['visivelap'] ?? '0');
    $urlproduto   = (string)($_POST['urlproduto'] ?? '');

    if ($idParam === '') {
        throw new InvalidArgumentException('ID não informado.');
    }
    if ($nomeap === '') {
        throw new InvalidArgumentException('Informe o nome do produto.');
    }
    if (mb_strlen($nomeap) > 200) {
        throw new InvalidArgumentException('Nome do produto excede 200 caracteres.');
    }

    // Decodifica ID (encrypt ou puro)
    $decId = $idParam;
    if (function_exists('encrypt')) {
        try {
            $tryDec = encrypt($idParam, 'd');
            if ($tryDec && is_string($tryDec)) {
                $decId = $tryDec;
            }
        } catch (Throwable $e) {
            // se falhar decriptografia, mantém idParam como possível id puro
        }
    }
    $parts = explode('&', (string)$decId);
    $id    = (int)($parts[0] ?? $decId);
    if ($id <= 0) {
        throw new InvalidArgumentException('ID inválido.');
    }

    // Normaliza valores monetários (padrão #45)
    $valorap   = money_to_decimal($valorapBr);
    $comissao  = money_to_decimal($comissaoBr);
    $visivelap = ($visivelapStr === '1' || $visivelapStr === 'true' || $visivelapStr === 'on') ? 1 : 0;

    // DB
    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica existência do produto
    $chk = $con->prepare("SELECT codigoprodutoafiliado FROM a_site_afiliados_produto WHERE codigoprodutoafiliado = :id LIMIT 1");
    $chk->bindValue(':id', $id, PDO::PARAM_INT);
    $chk->execute();
    if (!$chk->fetch(PDO::FETCH_ASSOC)) {
        throw new RuntimeException('Produto não encontrado.');
    }

    // Atualiza
    $sql = "
        UPDATE a_site_afiliados_produto
           SET nomeap = :nomeap,
               valorap = :valorap,
               urlprodutoap = :urlproduto,
               comissaoap = :comissaoap,
               visivelap = :visivelap
         WHERE codigoprodutoafiliado = :id
         LIMIT 1
    ";
    $upd = $con->prepare($sql);
    $upd->bindValue(':nomeap', $nomeap, PDO::PARAM_STR);
    $upd->bindValue(':urlproduto', $urlproduto, PDO::PARAM_STR);
    // usar PARAM_STR para garantir precisão de decimais no PDO/MySQL
    $upd->bindValue(':valorap', (string)$valorap, PDO::PARAM_STR);
    $upd->bindValue(':comissaoap', (string)$comissao, PDO::PARAM_STR);
    $upd->bindValue(':visivelap', $visivelap, PDO::PARAM_INT);
    $upd->bindValue(':id', $id, PDO::PARAM_INT);
    $upd->execute();

    // Recria id encryptado para retorno
    $idenc = function_exists('encrypt') ? encrypt((string)$id) : (string)$id;

    $response['ok']   = true;
    $response['id']   = $id;
    $response['idenc'] = $idenc;
    $response['rows'] = $upd->rowCount();
    $response['msg']  = $upd->rowCount() > 0 ? 'Produto atualizado com sucesso.' : 'Nenhuma alteração aplicada.';
    json_out($response, 200);
} catch (Throwable $e) {
    $code = ($e instanceof InvalidArgumentException) ? 400 : 500;
    $response['ok']  = false;
    $response['msg'] = $e->getMessage();
    json_out($response, $code);
}
