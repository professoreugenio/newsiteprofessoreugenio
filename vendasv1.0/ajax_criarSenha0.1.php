<?php

/**
 * vendasv1.0/ajax_criarSenha0.1.php
 * Etapa 4 — Criação de Senha
 * - Atualiza/insere senha do usuário
 * - Responde JSON imediatamente
 * - Dispara e-mail de confirmação em background
 */

declare(strict_types=1);

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 1));

require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Envia JSON e encerra a resposta sem bloquear o back (email roda depois) */
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

    if (function_exists('fastcgi_finish_request')) {
        @fastcgi_finish_request();
    } else {
        @flush();
        @ob_flush();
    }
}

/** Dispara o e-mail de senha (executa após json_quit) */
function enviar_email_senha(array $ctx): void
{
    // Espera chaves: nome, emailpara, senhaCadastro, nmCurso, linkAcesso, subject, assunto
    $nome          = $ctx['nome']          ?? '';
    $emailpara     = $ctx['emailpara']     ?? '';
    $senhaCadastro = $ctx['senhaCadastro'] ?? '';
    $nmCurso       = $ctx['nmCurso']       ?? 'Curso Online';
    $linkAcesso    = $ctx['linkAcesso']    ?? '';
    $assunto       = $ctx['assunto']       ?? ('SENHA DE ACESSO — ' . $nmCurso);
    $subject       = $ctx['subject']       ?? ('=?UTF-8?B?' . base64_encode($assunto) . '?=');

    if (!filter_var($emailpara, FILTER_VALIDATE_EMAIL)) {
        return;
    }

    // Disponibiliza variáveis ao template no padrão '.$variavel.'
    $senha = $senhaCadastro; // compat: se o template usar '.$senha.' ou '.$senhaCadastro.'
    $logoUrl = 'https://professoreugenio.com/img/logo.png'; // opcional

    ignore_user_abort(true);
    @set_time_limit(30);

    try {
        ob_start();
        include APP_ROOT . '/modulos_mail/modulo_mail_headers.php';
        include APP_ROOT . '/modulos_mail/modulo_mail_body_InscricaoSenha.php';
        include APP_ROOT . '/modulos_mail/modulo_mail_send.php';
        @ob_end_clean(); // descarta qualquer saída dos módulos de e-mail
    } catch (Throwable $e) {
        if (ob_get_length()) {
            @ob_end_clean();
        }
        // (Opcional) log do erro de e-mail
        // @file_put_contents(APP_ROOT.'/logs/mail_senha_err_'.date('Ymd_His').'.log', $e->getMessage().PHP_EOL, FILE_APPEND);
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_quit(['status' => 'erro', 'mensagem' => 'Método inválido.'], 405);
        exit;
    }

    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Entrada
    $idUsuarioIn   = trim($_POST['idUsuario'] ?? '');
    $emailIn       = trim($_SESSION['emailUsuario'] ?? ''); // fallback
    $senha         = trim($_POST['senha'] ?? '');

    // Validação básica de senha
    if (strlen($senha) < 6) {
        json_quit(['status' => 'erro', 'mensagem' => 'A senha deve ter pelo menos 6 caracteres.'], 422);
        exit;
    }

    // Decodifica ID, quando vier criptografado
    $idUsuario = $idUsuarioIn ? encrypt($idUsuarioIn, 'd') : null;

    // Buscar usuário por ID (preferência) ou por E-mail (fallback)
    $usuario = null;
    if ($idUsuario && ctype_digit((string)$idUsuario)) {
        $stmt = $con->prepare("SELECT codigocadastro, nome, email FROM new_sistema_cadastro WHERE codigocadastro = :id LIMIT 1");
        $stmt->bindParam(':id', $idUsuario);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (!$usuario && $emailIn) {
        $stmt = $con->prepare("SELECT codigocadastro, nome, email FROM new_sistema_cadastro WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $emailIn);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Monta contexto comum ao e-mail
    $host  = $_SERVER['HTTP_HOST'] ?? 'professoreugenio.com';
    $https = 'https';
    // ajuste para sua rota de login/acesso:
    $linkAcesso = $https . '://' . $host . '/login.php';

    $nmCurso = $_SESSION['nmCurso']   ?? $_SESSION['nomeCurso'] ?? 'Curso Online';
    $nomeSess = $_SESSION['nomeUsuario'] ?? 'Aluno';

    if ($usuario) {
        $idUsuarioDb = (int)$usuario['codigocadastro'];
        $emailDb     = (string)$usuario['email'];
        $nomeDb      = trim((string)($usuario['nome'] ?? '')) ?: $nomeSess;

        // Padrão do projeto
        $senhaenc = encrypt($emailDb . "&" . $senha, 'e');
        $chave    = strtoupper(md5($emailDb . "&" . $senha));

        $upd = $con->prepare("
            UPDATE new_sistema_cadastro
               SET senha = :senha, chave = :chave
             WHERE codigocadastro = :id
             LIMIT 1
        ");
        $upd->execute([
            ':senha' => $senhaenc,
            ':chave' => $chave,
            ':id'    => $idUsuarioDb
        ]);

        // Atualiza sessão
        $_SESSION['idUsuario']    = encrypt($idUsuarioDb, 'e');
        $_SESSION['emailUsuario'] = $emailDb;
        $_SESSION['nomeUsuario']  = $nomeDb;

        // Responde imediatamente
        json_quit(['status' => 'ok', 'mensagem' => 'Senha definida com sucesso.']);

        // Dispara e-mail em background
        enviar_email_senha([
            'nome'          => $nomeDb,
            'emailpara'     => $emailDb,
            'senhaCadastro' => $senha,
            'nmCurso'       => $nmCurso,
            'linkAcesso'    => $linkAcesso,
            'assunto'       => 'SENHA CRIADA — Acesso ao ' . $nmCurso,
            'subject'       => '=?UTF-8?B?' . base64_encode('SENHA CRIADA — Acesso ao ' . $nmCurso) . '?='
        ]);
        exit;
    }

    /**
     * Se não existir cadastro, INSERE (fallback).
     * Obs.: na maioria dos fluxos isso não será usado, mas mantemos por segurança.
     */
    $email = $emailIn ?: ($_SESSION['emailUsuario'] ?? null);
    $nome  = $_SESSION['nomeUsuario'] ?? 'Aluno';
    if (!$email) {
        json_quit(['status' => 'erro', 'mensagem' => 'E-mail não informado para criação do cadastro.'], 400);
        exit;
    }

    // Campos complementares
    $idadm       = $_SESSION['idadm'] ?? 0;
    $chaveturma  = $_SESSION['chaveTurma'] ?? null;
    $idturma     = $chaveturma;
    $codigo      = $chaveturma;

    $datanascimento = $_POST['datanascimento'] ?? null;
    $celular        = $_POST['celular'] ?? ($_SESSION['celularUsuario'] ?? null);
    $estado         = $_POST['estado'] ?? null;

    $data = date('Y-m-d');
    $hora = date('H:i:s');

    if (!function_exists('mesabreviado')) {
        function mesabreviado($d)
        {
            $map = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            $m = (int)date('n', strtotime($d));
            return $map[$m - 1] ?? date('m', strtotime($d));
        }
    }
    $pasta = mesabreviado(date('Y-m-d')) . "_" . date("Ymd") . time();

    // Senha/chave no padrão do projeto
    $senhaenc = encrypt($email . "&" . $senha, 'e');
    $chave    = strtoupper(md5($email . "&" . $senha));

    $queryInsert = $con->prepare("
        INSERT INTO new_sistema_cadastro (
            codadmin, codigo, turma_sc, turma,
            nome, email, datanascimento_sc, celular, estado,
            senha, chave, pastasc, data_sc, hora_sc
        ) VALUES (
            :codadmin, :codigo, :turma_sc, :turma,
            :nome, :email, :datanascimento, :celular, :estado,
            :senha, :chave, :pasta, :data_sc, :hora_sc
        )
    ");
    $queryInsert->execute([
        ':codadmin'       => $idadm,
        ':codigo'         => $codigo,
        ':turma_sc'       => $idturma,
        ':turma'          => $idturma,
        ':nome'           => $nome,
        ':email'          => $email,
        ':datanascimento' => $datanascimento,
        ':celular'        => $celular,
        ':estado'         => $estado,
        ':senha'          => $senhaenc,
        ':chave'          => $chave,
        ':pasta'          => $pasta,
        ':data_sc'        => $data,
        ':hora_sc'        => $hora,
    ]);

    $novoId = (int)$con->lastInsertId();

    // Atualiza sessão
    $_SESSION['idUsuario']    = encrypt($novoId, 'e');
    $_SESSION['emailUsuario'] = $email;
    $_SESSION['nomeUsuario']  = $nome;

    // Responde imediatamente
    json_quit(['status' => 'ok', 'mensagem' => 'Cadastro criado e senha definida com sucesso.']);

    // Dispara e-mail em background
    enviar_email_senha([
        'nome'          => $nome,
        'emailpara'     => $email,
        'senhaCadastro' => $senha,
        'nmCurso'       => $nmCurso,
        'linkAcesso'    => $linkAcesso,
        'assunto'       => 'SENHA CRIADA — Acesso ao ' . $nmCurso,
        'subject'       => '=?UTF-8?B?' . base64_encode('SENHA CRIADA — Acesso ao ' . $nmCurso) . '?='
    ]);
    exit;
} catch (PDOException $e) {
    json_quit(['status' => 'erro', 'mensagem' => 'Erro no banco de dados.'], 500);
    exit;
} catch (Throwable $e) {
    json_quit(['status' => 'erro', 'mensagem' => 'Falha ao processar dados.'], 500);
    exit;
}
