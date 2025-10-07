<?php

/**
 * afiliados1.0/ajax_afiliadosProdutoInsert.php
 * Cadastra novo produto de afiliado e retorna JSON com idenc para redirecionar.
 * Espera: POST { nomeap, valorap, comissaoap, visivelap }
 * Saída: { ok:true, id:<int>, idenc:<string>, pasta:<string> }
 */

declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

$response = ['ok' => false, 'msg' => ''];

function json_out(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Converte "000.000.000,00" -> "1234.56"
function money_to_decimal(string $v): string
{
    $v = str_replace(['.', ' '], '', trim($v));
    $v = str_replace(',', '.', $v);
    return (string)(is_numeric($v) ? $v : '0');
}

// "Jan, Fev, Mar, Abr, Mai, Jun, Jul, Ago, Set, Out, Nov, Dez"
function mes_pt_abbr(int $n): string
{
    $m = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    return $m[$n] ?? date('M');
}

// Gera pasta no padrão: Set202509_101757558570
function gerar_pasta_produto(): string
{
    $mes = mes_pt_abbr((int)date('n'));
    $ym  = date('Ym');
    // 12–15 dígitos pseudo-únicos baseados em microtime
    $seed = preg_replace('/\D/', '', (string)round(microtime(true) * 1000000));
    $seed = substr($seed, 0, 12);
    return $mes . $ym . '_' . $seed;
}

try {
    // ===== Autenticação por COOKIE =====
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
    $nomeap       = trim((string)($_POST['nomeap'] ?? ''));
    $valorapBr    = (string)($_POST['valorap'] ?? '0,00');
    $comissaoBr   = (string)($_POST['comissaoap'] ?? '0,00');
    $visivelapStr = (string)($_POST['visivelap'] ?? '0');
    $urlproduto   = (string)($_POST['urlproduto'] ?? '');

    if ($nomeap === '') {
        throw new InvalidArgumentException('Informe o nome do produto.');
    }
    if (mb_strlen($nomeap) > 200) {
        throw new InvalidArgumentException('Nome do produto excede 200 caracteres.');
    }

    $valorap   = money_to_decimal($valorapBr);
    $comissao  = money_to_decimal($comissaoBr);
    $visivelap = ($visivelapStr === '1' || $visivelapStr === 'true' || $visivelapStr === 'on') ? 1 : 0;

    // ===== DB =====
    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Gera pasta única
    $pasta = gerar_pasta_produto();

    // INSERT
    $sql = "
    INSERT INTO a_site_afiliados_produto
      (nomeap, valorap, comissaoap, visivelap, urlprodutoap, pastaap, dataap, horaap)
    VALUES
      (:nomeap, :valorap, :comissaoap, :visivelap, :urlproduto, :pastaap, CURDATE(), CURTIME())
  ";
    $ins = $con->prepare($sql);
    $ins->bindValue(':nomeap',     $nomeap,  PDO::PARAM_STR);
    $ins->bindValue(':valorap',    $valorap, PDO::PARAM_STR);   // usar string p/ preservar decimais
    $ins->bindValue(':comissaoap', $comissao, PDO::PARAM_STR);
    $ins->bindValue(':visivelap',  $visivelap, PDO::PARAM_INT);
    $ins->bindValue(':urlproduto', $urlproduto, PDO::PARAM_STR);
    $ins->bindValue(':pastaap',    $pasta, PDO::PARAM_STR);
    $ins->execute();

    $id = (int)$con->lastInsertId();
    if ($id <= 0) {
        throw new RuntimeException('Falha ao obter ID do novo produto.');
    }

    // Cria diretórios: fotos/produtosafiliados/<pasta>/imagem
    $dirFS = APP_ROOT . '/fotos/produtosafiliados/' . $pasta . '/imagem';
    if (!is_dir($dirFS) && !@mkdir($dirFS, 0775, true)) {
        throw new RuntimeException('Não foi possível criar o diretório de imagens.');
    }

    // Retorno com idenc esperado pelo front
    $idenc = function_exists('encrypt') ? encrypt((string)$id) : (string)$id;

    $response['ok']    = true;
    $response['id']    = $id;
    $response['idenc'] = $idenc;     // <- CHAVE QUE O FRONT USA
    $response['pasta'] = $pasta;
    json_out($response, 200);
} catch (Throwable $e) {
    $code = ($e instanceof InvalidArgumentException) ? 400 : 500;
    $response['ok']  = false;
    $response['msg'] = $e->getMessage();
    json_out($response, $code);
}
