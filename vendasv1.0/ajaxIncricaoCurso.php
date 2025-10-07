<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 1));

require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ----- Regras de método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit;
}

$con = config::connect();
try {
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\Throwable $e) {
}

$data = date('Y-m-d');
$hora = date('H:i:s');


// ----- Coleta e validação básica
$horario       = trim($_POST['horario'] ?? '');
$nome       = trim($_POST['nome'] ?? '');
$email      = trim($_POST['email'] ?? '');
$chaveAf      = trim($_POST['chaveAf'] ?? '');
$telefone   = trim($_POST['telefone'] ?? '');
$chaveCript = trim($_POST['Codigochave'] ?? '');
$idCursoEnc = $_POST['idCurso'] ?? '';

if ($nome === '' || $email === '' || $telefone === '' || $chaveCript === '') {
    http_response_code(422); // Unprocessable Entity
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    exit;
}

// ----- Descriptografar chave
$chave = encrypt($chaveCript, 'd');
if (!$chave) {
    http_response_code(400); // Bad Request (chave inválida)
    exit;
}

try {
    // ----- Valida chave e obtém a turma
    $stmtChave = $con->prepare("
        SELECT chavesc, chaveturmasc
        FROM new_sistema_chave
        WHERE chavesc = :chave
        LIMIT 1
    ");
    $stmtChave->bindParam(':chave', $chave);
    $stmtChave->execute();
    $rwChave = $stmtChave->fetch(PDO::FETCH_ASSOC);

    if (!$rwChave) {
        http_response_code(400); // Chave inválida/expirada
        exit;
    }

    $chaveTurma = $rwChave['chaveturmasc'];

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

    // ----- Se já inscrito nesta turma, inicia sessão e finaliza com sucesso
    $queryCad = $con->prepare("
        SELECT nc.codigocadastro 
        FROM new_sistema_cadastro AS nc
        JOIN new_sistema_inscricao_PJA AS ni 
            ON ni.codigousuario = nc.codigocadastro
        WHERE nc.email = :email 
          AND ni.chaveturma = :chaveturma
        LIMIT 1
    ");
    $queryCad->bindParam(':email', $email);
    $queryCad->bindParam(':chaveturma', $chaveTurma);
    $queryCad->execute();
    $existe = $queryCad->fetch(PDO::FETCH_ASSOC);

    if ($existe) {
        $idUsuario     = (int)$existe['codigocadastro'];
        $encIdUsuario  = encrypt($idUsuario, 'e');

        $_SESSION['idUsuario']    = $encIdUsuario;
        $_SESSION['emailUsuario'] = $email;
        $_SESSION['nomeUsuario']  = $nome;
        $_SESSION['chaveTurma']   = $chaveTurma;

        http_response_code(204); // No Content (sucesso)
        exit;
    }

    // ----- Verifica se já existe cadastro por e-mail
    $stmtVerifica = $con->prepare("
        SELECT codigocadastro 
        FROM new_sistema_cadastro 
        WHERE email = :email 
        LIMIT 1
    ");
    $stmtVerifica->bindParam(':email', $email);
    $stmtVerifica->execute();
    $usuarioExistente = $stmtVerifica->fetch(PDO::FETCH_ASSOC);

    if ($usuarioExistente) {
        $idUsuario = (int)$usuarioExistente['codigocadastro'];
    } else {
        // Cria novo aluno
        $pasta = mesabreviado(date('Y-m-d')) . "_" . date("Ymd") . time();
        $expm  = explode("@", $email);
        $senha = encrypt($email . "&" . ($expm[0] ?? ''), 'e');

        $stmt = $con->prepare("
            INSERT INTO new_sistema_cadastro (afiliacaoSC, nome, pastasc, email, senha, celular, data_sc)
            VALUES (:afiliado,:nome, :pasta, :email, :senha, :telefone, NOW())
        ");
        $stmt->execute([
            ':afiliado'     => $chaveAf,
            ':nome'     => $nome,
            ':pasta'    => $pasta,
            ':email'    => $email,
            ':senha'    => $senha,
            ':telefone' => $telefone
        ]);

        $idUsuario = (int)$con->lastInsertId();

        // Envia e-mail de inscrição
        $emailpara = $email;
        $nomepara  = $nome;
        $assunto   = "MINHA INSCRIÇÃO NO CURSO " . $nmCurso;
        $subject   = '=?UTF-8?B?' . base64_encode($assunto) . '?=';

        include APP_ROOT . '/modulos_mail/modulo_mail_headers.php';
        include APP_ROOT . '/modulos_mail/modulo_mail_body_InscricaoAluno.php';
        include APP_ROOT . '/modulos_mail/modulo_mail_send.php';
    }

    // ----- Vincula aluno à turma
    $encIdUsuario = encrypt($idUsuario, 'e');
    $dataprazo    = dataprazo($data, 2);

    // idCurso pode vir criptografado do POST; se não vier/for inválido, usa o da turma
    $idCursoDec = $idCursoEnc ? encrypt($idCursoEnc, 'd') : '';
    $idCurso    = $idCursoDec !== '' ? (int)$idCursoDec : $idCursoTurma;

    $stmtInscricao = $con->prepare("
        INSERT INTO new_sistema_inscricao_PJA 
            (codigousuario, chaveturma, codcurso_ip, dataprazosi, datarenovacao, horarenovacao, data_ins, hora_ins)
        VALUES 
            (:iduser, :chaveturma, :idcurso, :dataprazo, :datarenovacao, :horarenovacao, NOW(), :hora)
    ");
    $ok = $stmtInscricao->execute([
        ':iduser'        => $idUsuario,
        ':chaveturma'    => $chaveTurma,
        ':idcurso'       => $idCurso,
        ':dataprazo'     => $dataprazo,
        ':datarenovacao' => $data,
        ':horarenovacao' => $hora,
        ':hora'          => $hora
    ]);

    if (!$ok) {
        http_response_code(500); // Falha ao vincular
        exit;
    }

    // ----- Seta sessão e finaliza com sucesso
    $_SESSION['idUsuario']    = $encIdUsuario;
    $_SESSION['emailUsuario'] = $email;
    $_SESSION['nomeUsuario']  = $nome;
    $_SESSION['chaveTurma']   = $chaveTurma;

    http_response_code(204); // No Content
    exit;
} catch (PDOException $e) {
    http_response_code(500); // Erro de BD
    exit;
}
