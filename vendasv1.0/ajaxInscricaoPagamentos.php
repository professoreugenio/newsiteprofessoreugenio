<?php

/**
 * vendasv1.0/ajaxInscricaoPagamentos.php
 * Etapa 3 — Pagamento
 *
 * Recebe via POST:
 *  - metodo: 'pix' | 'cartao'
 *  - plano_slug: 'anual' | 'vitalicio'
 *  - plano_label: 'Plano Anual' | 'Plano Vitalício'
 *  - nomecurso
 *  - valor       (ex.: 299.90 ou "1.299,90" ou "R$ 1.299,90")
 *  - valor_fmt   (ex.: "R$ 1.299,90") [opcional, se vier, será usado]
 *  - [PIX]   chavepix, payloadpix
 *  - [Cartão] parcelas, bandeira, checkout_pagseguro, checkout_mercadopago
 *
 * Responde: {"status":"ok"} e, em seguida, dispara e-mail em background.
 */

declare(strict_types=1);

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 1));

require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Envia JSON e fecha a resposta imediatamente (para não travar o front) */
function json_quit(array $payload, int $httpCode = 200): void
{
    if (!headers_sent()) {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Connection: close');
    }
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
    if (ob_get_length()) {
        @ob_end_clean();
    }
    header('Content-Length: ' . strlen($json));
    echo $json;

    // Fecha a requisição no PHP-FPM/NGINX (se disponível)
    if (function_exists('fastcgi_finish_request')) {
        @fastcgi_finish_request();
    } else {
        @flush();
        @ob_flush();
    }
}

/** Normaliza valores monetários em string para float e devolve float + formato BR */
function normaliza_valor(string $raw, ?string $fmtPreferencial = null): array
{
    $raw = trim($raw);
    if ($fmtPreferencial !== null && $fmtPreferencial !== '') {
        // Quando já vem formatado (ex. "R$ 1.299,90"), priorizamos o fmt fornecido
        $fmt = $fmtPreferencial;
    } else {
        // Formataremos após converter para float
        $fmt = '';
    }

    if ($raw !== '') {
        $tmp = str_replace(['R$', ' '], '', $raw);
        if (strpos($tmp, ',') !== false && strpos($tmp, '.') !== false) {
            // "1.234,56" -> "1234.56"
            $tmp = str_replace('.', '', $tmp);
            $tmp = str_replace(',', '.', $tmp);
        } elseif (strpos($tmp, ',') !== false) {
            // "199,90" -> "199.90"
            $tmp = str_replace(',', '.', $tmp);
        }
        $dec = (float)$tmp;
    } else {
        $dec = 0.0;
    }

    if ($fmt === '') {
        $fmt = 'R$ ' . number_format($dec, 2, ',', '.');
    }

    return [$dec, $fmt];
}

try {
    // ===== Validação básica do método HTTP =====
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_quit(['status' => 'erro', 'mensagem' => 'Método inválido.'], 405);
        exit;
    }

    // ===== Sessão obrigatória =====
    $idUsuarioSess = $_SESSION['idUsuario']  ?? '';
    $chaveTurma    = $_SESSION['chaveTurma'] ?? '';
    if (!$idUsuarioSess || !$chaveTurma) {
        json_quit(['status' => 'erro', 'mensagem' => 'Sessão expirada ou inválida.'], 400);
        exit;
    }


    // ----- Busca dados da turma
    $stmtTurma = $con->prepare("
        SELECT codcursost, nometurma
        FROM new_sistema_cursos_turmas
        WHERE chave = :chaveTurma
        LIMIT 1
    ");
    $stmtTurma->bindParam(':chaveTurma', $chaveTurma);
    $stmtTurma->execute();
    $rwTurma = $stmtTurma->fetch(PDO::FETCH_ASSOC);

    if (!$rwTurma) {
        http_response_code(404); // Turma não encontrada
        exit;
    }

    $idCursoTurma = (int)$rwTurma['codcursost'];
    $NomeTurma    = $rwTurma['nometurma'] ?? '';

    // ----- Busca nome do curso (para e-mail)
    $stmtCurso = $con->prepare("
        SELECT nome
        FROM new_sistema_categorias_PJA
        WHERE codigocategorias = :idcurso
        LIMIT 1
    ");
    $stmtCurso->bindParam(':idcurso', $idCursoTurma, PDO::PARAM_INT);
    $stmtCurso->execute();
    $rwCurso = $stmtCurso->fetch(PDO::FETCH_ASSOC);
    $nmCurso = $rwCurso['nome'] ?? 'Curso';

    // ===== Decodifica ID (padrão do projeto) =====
    $idUsuario = @encrypt($idUsuarioSess, 'd');
    if (!ctype_digit((string)$idUsuario)) {
        $idUsuario = preg_replace('/\D+/', '', (string)$idUsuarioSess);
    }
    $idUsuario = (int)$idUsuario;
    if ($idUsuario <= 0) {
        json_quit(['status' => 'erro', 'mensagem' => 'Usuário inválido.'], 400);
        exit;
    }

    // ===== Coleta de dados =====
    $metodo        = strtolower(trim($_POST['metodo']       ?? '')); // 'pix' | 'cartao'
    $planoSlug     = trim((string)($_POST['plano_slug']     ?? '')); // 'anual' | 'vitalicio'
    $planoLabel    = trim((string)($_POST['plano_label']    ?? '')); // 'Plano Anual' | 'Plano Vitalício'
    $nomeCurso     = trim((string)($_POST['nomecurso']      ?? 'Curso Online'));
    $valorRaw      = trim((string)($_POST['valor']          ?? ''));
    $valorFmtPost  = trim((string)($_POST['valor_fmt']      ?? ''));

    // PIX
    $chavePix      = trim((string)($_POST['chavepix']       ?? ''));
    $payloadPix    = trim((string)($_POST['payloadpix']     ?? ''));

    // Cartão
    $parcelas      = trim((string)($_POST['parcelas']       ?? '')); // "1","6","12"
    $bandeira      = trim((string)($_POST['bandeira']       ?? '')); // "visa","mastercard"...
    $checkoutUrl   = trim((string)($_POST['checkout_url']   ?? '')); // legado (opcional)
    $checkoutPagSeguro   = trim((string)($_POST['checkout_pagseguro']   ?? ''));
    $checkoutMercadoPago = trim((string)($_POST['checkout_mercadopago'] ?? ''));

    // ===== Valida método =====
    if (!in_array($metodo, ['pix', 'cartao'], true)) {
        json_quit(['status' => 'erro', 'mensagem' => 'Método de pagamento inválido.'], 422);
        exit;
    }

    // ===== Normaliza valor =====
    [$valorPlanoDec, $valorPlanoFmt] = normaliza_valor($valorRaw, $valorFmtPost);

    // ===== Busca dados do usuário =====
    $nome  = '';
    $email = '';
    try {
        $pdo = config::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $su = $pdo->prepare("SELECT nome, email FROM new_sistema_cadastro WHERE codigocadastro = :id LIMIT 1");
        $su->execute([':id' => $idUsuario]);
        if ($rw = $su->fetch(PDO::FETCH_ASSOC)) {
            $nome  = $rw['nome']  ?? '';
            $email = $rw['email'] ?? '';
        }
    } catch (Throwable $e) {
        // Não bloqueia — seguimos com nome/email em branco
    }

    // ===== Monta variáveis para o e-mail =====
    $assunto = 'INSTRUÇÕES DE PAGAMENTO — ' . $nomeCurso;
    $subject = '=?UTF-8?B?' . base64_encode($assunto) . '?=';

    $emailpara = $email;
    $nomepara  = $nome;

    $metodoPagamento  = ($metodo === 'pix') ? 'Pix' : 'Cartão de Crédito';
    $planoSelecionado = $planoLabel ?: (strtolower($planoSlug) === 'anual' ? 'Plano Anual' : 'Plano Vitalício');

    $parcelasLabel   = '';
    $valorParcelaFmt = '';
    if ($metodo === 'cartao' && ctype_digit($parcelas) && (int)$parcelas > 1 && $valorPlanoDec > 0) {
        $valorParcela = $valorPlanoDec / (int)$parcelas;
        $valorParcelaFmt = 'R$ ' . number_format($valorParcela, 2, ',', '.');
        $parcelasLabel   = $parcelas . 'x de ' . $valorParcelaFmt;
    }

    // ===== Responde imediatamente ao front =====
    json_quit(['status' => 'ok']);

    // ===== E-mail em background (não bloqueante) =====
    ignore_user_abort(true);
    @set_time_limit(30);

    if (filter_var($emailpara, FILTER_VALIDATE_EMAIL)) {
        try {
            // Evita que qualquer debug sujar a resposta
            ob_start();

            // As variáveis abaixo ficam disponíveis no body:
            // $assunto, $subject, $nome, $emailpara, $nomecurso,
            // $metodoPagamento, $planoSelecionado, $valorPlanoFmt,
            // $parcelasLabel, $valorParcelaFmt, $chavePix, $payloadPix,
            // $bandeira, $checkoutPagSeguro, $checkoutMercadoPago, $checkoutUrl (legado)

            include APP_ROOT . '/modulos_mail/modulo_mail_headers.php';
            include APP_ROOT . '/modulos_mail/modulo_mail_body_InscricaoPagamentos.php';
            include APP_ROOT . '/modulos_mail/modulo_mail_send.php';

            // Descarte/log opcional
            $out = ob_get_clean();
            // if (!empty($out)) { @file_put_contents(APP_ROOT.'/logs/mail_pgto_'.date('Ymd_His').'.log', $out.PHP_EOL, FILE_APPEND); }
        } catch (Throwable $e) {
            if (ob_get_length()) {
                @ob_end_clean();
            }
            // (Opcional) log de erro
            // @file_put_contents(APP_ROOT.'/logs/mail_pgto_err_'.date('Ymd_His').'.log', $e->getMessage().PHP_EOL, FILE_APPEND);
        }
    } else {
        // (Opcional) log de e-mail inválido/ausente
        // @file_put_contents(APP_ROOT.'/logs/mail_pgto_skip_'.date('Ymd_His').'.log', "Email inválido para ID {$idUsuario}".PHP_EOL, FILE_APPEND);
    }

    exit;
} catch (Throwable $e) {
    // Em caso de falha geral, ainda tentamos responder algo para não travar o front
    json_quit([
        'status'   => 'erro',
        'mensagem' => 'Falha ao processar sua solicitação.',
        'detalhe'  => $e->getMessage()
    ], 500);
    exit;
}
