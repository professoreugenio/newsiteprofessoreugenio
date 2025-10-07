<?php

/**
 * vendasv1.0/ajaxInscricaoPlano.php
 * Atualiza o campo "plano" e responde JSON imediatamente.
 * O envio de e-mail acontece APÓS fechar a resposta (não bloqueia o redirect do front).
 */

declare(strict_types=1);
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 1));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// -------- Helpers --------
function json_quit(array $payload): void
{
    // Garante JSON limpo
    if (ob_get_length()) {
        @ob_end_clean();
    }
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
    // Cabeçalhos
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Connection: close');
    header('Content-Length: ' . strlen($json));
    // Envia e fecha
    echo $json;
    // Fecha a resposta no PHP-FPM/NGINX
    if (function_exists('fastcgi_finish_request')) {
        @fastcgi_finish_request();
    } else {
        @flush();
        @ob_flush();
    }
}
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        json_quit(['status' => 'erro', 'mensagem' => 'Método inválido.']);
        exit;
    }
    // Sessões
    $idUsuarioSess = $_SESSION['idUsuario']  ?? '';
    $chaveTurma    = $_SESSION['chaveTurma'] ?? '';
    if (!$idUsuarioSess || !$chaveTurma) {
        http_response_code(400);
        json_quit(['status' => 'erro', 'mensagem' => 'Sessão expirada ou inválida.']);
        exit;
    }
    // Decodifica ID (padrão do projeto)
    $idUsuario = @encrypt($idUsuarioSess, 'd');
    if (!ctype_digit((string)$idUsuario)) {
        $idUsuario = preg_replace('/\D+/', '', (string)$idUsuarioSess);
    }
    $idUsuario = (int)$idUsuario;
    if ($idUsuario <= 0) {
        http_response_code(400);
        json_quit(['status' => 'erro', 'mensagem' => 'Usuário inválido.']);
        exit;
    }
    // Plano
    $planoStr = strtolower(trim($_POST['plano'] ?? ''));
    $planoStr = str_replace(['í', 'Í'], 'i', $planoStr); // vitalício -> vitalicio
    if ($planoStr === 'anual') {
        $planoCode = 1;
        $nmPlano = 'ANUAL';
    } elseif ($planoStr === 'vitalicio') {
        $planoCode = 2;
        $nmPlano = 'VITALÍCIO';
    } else {
        http_response_code(422);
        json_quit(['status' => 'erro', 'mensagem' => 'Plano inválido.']);
        exit;
    }


    // ===== Valor do plano (opcional, vindo do front) =====
    $valorPlanoRaw = isset($_POST['valorplano']) ? trim((string)$_POST['valorplano']) : '';
    $valorPlanoDec = null;
    $valorPlanoFmt = '';


    $dec = encrypt($_COOKIE['nav'], $action = 'd');
    $exp = explode('&', $dec);
    $af = $exp[6] ?? '0';

    $con = config::connect();
    $query = $con->prepare("SELECT * FROM a_site_afiliados_chave WHERE chaveafiliadoSA = :chave ");
    $query->bindParam(":chave", $af);
    $query->execute();
    $rwNome = $query->fetch(PDO::FETCH_ASSOC);
    $idAfiliado = $rwNome['idusuarioSA'] ?? 0;


    $sql = "INSERT INTO a_site_afiliados_cache
            (idafiliadochaveac, idprodutoac, idclienteac, valorac, statusac, dataac, horaac)
            VALUES (:idafiliado, :idproduto, :idcliente, :valor, :status, :data, :hora)";
    $status    = 0;
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':idafiliado', $idAfiliado, PDO::PARAM_INT);
    $stmt->bindParam(':idproduto',  $chaveTurma,  PDO::PARAM_INT);
    $stmt->bindParam(':idcliente',  $idUsuario,  PDO::PARAM_INT);
    $stmt->bindParam(':valor',      $valorPlanoRaw);
    $stmt->bindParam(':status',     $status, PDO::PARAM_INT);
    $stmt->bindParam(':data',       $data);
    $stmt->bindParam(':hora',       $hora);
    $stmt->execute();




    if ($valorPlanoRaw !== '') {
        // aceita "199.9", "199.90", "1.299,90", "R$ 1.299,90"
        $tmp = str_replace(['R$', ' '], '', $valorPlanoRaw);
        if (strpos($tmp, ',') !== false && strpos($tmp, '.') !== false) {
            // formato BR (1.234,56) -> remove . de milhar e troca , por .
            $tmp = str_replace('.', '', $tmp);
            $tmp = str_replace(',', '.', $tmp);
        } elseif (strpos($tmp, ',') !== false && strpos($tmp, '.') === false) {
            // "199,90" -> "199.90"
            $tmp = str_replace(',', '.', $tmp);
        }
        $valorPlanoDec = (float)$tmp;
        if ($valorPlanoDec > 0) {
            $valorPlanoFmt = 'R$ ' . number_format($valorPlanoDec, 2, ',', '.');
        }
    }

    // Nome do curso (para o e-mail)
    $nmCurso = trim((string)($_POST['nomecurso'] ?? ''));
    if ($nmCurso === '') {
        $nmCurso = 'Curso Online';
    }
    // Conexão
    $pdo = config::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Confere inscrição
    $chk = $pdo->prepare("
        SELECT 1
          FROM new_sistema_inscricao_PJA
         WHERE chaveturma = :chaveturma
           AND codigousuario = :idusuario
         LIMIT 1
    ");
    $chk->execute([':chaveturma' => $chaveTurma, ':idusuario' => $idUsuario]);
    if (!$chk->fetchColumn()) {
        http_response_code(404);
        json_quit(['status' => 'erro', 'mensagem' => 'Inscrição não encontrada para este usuário/turma.']);
        exit;
    }
    // Atualiza plano
    $upd = $pdo->prepare("
        UPDATE new_sistema_inscricao_PJA
           SET plano = :plano
         WHERE chaveturma = :chaveturma
           AND codigousuario = :idusuario
         LIMIT 1
    ");
    $upd->execute([':plano' => $planoCode, ':chaveturma' => $chaveTurma, ':idusuario' => $idUsuario]);
    // Busca aluno
    $su = $pdo->prepare("
        SELECT nome, email
          FROM new_sistema_cadastro
         WHERE codigocadastro = :id
         LIMIT 1
    ");
    $su->execute([':id' => $idUsuario]);
    $rwUser = $su->fetch(PDO::FETCH_ASSOC) ?: [];
    $nome   = $rwUser['nome']  ?? '';
    $email  = $rwUser['email'] ?? '';
    // Monta redirect sugerido
    $host    = $_SERVER['HTTP_HOST'] ?? 'professoreugenio.com';
    $scheme  = 'https'; // força https
    $redirect = $scheme . '://' . $host . '/pagina_vendasPagamento.php?plano=' . urlencode($planoStr);
    // ---------- RESPONDE AGORA (não esperar e-mail) ----------
    json_quit([
        'status'    => 'ok',
        'mensagem'  => 'Plano atualizado com sucesso.',
        'plano'     => $planoCode,
        'plano_str' => $planoStr,
        'redirect'  => $redirect
    ]);
    // A partir daqui, a conexão com o navegador já está fechada.
    // Vamos enviar o e-mail em background, sem travar o front.
    ignore_user_abort(true); // continua mesmo se usuário sair
    // Opcional: limite de execução só para este bloco
    @set_time_limit(30);
    // Disparo de e-mail protegido
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Variáveis esperadas pelos módulos
        $emailpara = $email;
        $nomepara  = $nome;
        $assunto = 'SELEÇÃO DE PLANO DO CURSO DE ' . $nmCurso;
        $subject = '=?UTF-8?B?' . base64_encode($assunto) . '?=';
        $linkContinuar    = $redirect;
        $linkAlterarPlano = $scheme . '://' . $host . '/pagina_vendasSelecaoPlano.php';
        $planoSelecionado = $nmPlano;
        try {
            ob_start();
            // Se seus headers aceitarem, evite debug:
            // $MAIL_SMTP_DEBUG = 0;
            include APP_ROOT . '/modulos_mail/modulo_mail_headers.php';
            include APP_ROOT . '/modulos_mail/modulo_mail_body_InscricaoPlano.php';
            include APP_ROOT . '/modulos_mail/modulo_mail_send.php';
            $out = ob_get_clean();
            // (Opcional) logar $out se necessário
            // if (!empty($out)) { @file_put_contents(APP_ROOT.'/logs/mail_'.date('Ymd_His').'.log', $out.PHP_EOL, FILE_APPEND); }
        } catch (Throwable $e) {
            if (ob_get_length()) {
                @ob_end_clean();
            }
            // (Opcional) logar erro
            // @file_put_contents(APP_ROOT.'/logs/mail_err_'.date('Ymd_His').'.log', $e->getMessage().PHP_EOL, FILE_APPEND);
        }
    } else {
        // (Opcional) logar ausência/invalidade do e-mail
        // @file_put_contents(APP_ROOT.'/logs/mail_skip_'.date('Ymd_His').'.log', "Email inválido para ID {$idUsuario}".PHP_EOL, FILE_APPEND);
    }
    exit;
} catch (Throwable $e) {
    if (!headers_sent()) {
        http_response_code(500);
    }
    json_quit([
        'status'   => 'erro',
        'mensagem' => 'Falha ao processar sua solicitação.',
        'detalhe'  => $e->getMessage()
    ]);
    exit;
}
