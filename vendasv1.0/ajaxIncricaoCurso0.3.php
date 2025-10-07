<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 1));

require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Verificação do método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Método inválido']);
    exit;
}

$con = config::connect();
// Opcional: garanta exceções para captar falhas pontuais
try {
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\Throwable $e) {
}

$data = date('Y-m-d');
$hora = date('H:i:s');

// Capturar e limpar os dados
$nome       = trim($_POST['nome'] ?? '');
$email      = trim($_POST['email'] ?? '');
$telefone   = trim($_POST['telefone'] ?? '');
$chaveCript = trim($_POST['Codigochave'] ?? '');
$idCursoEnc = trim($_POST['idCurso'] ?? '');

if (!$nome || !$email || !$telefone || !$chaveCript || !$idCursoEnc) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Preencha todos os campos obrigatórios.']);
    exit;
}

// Descriptografar chave e id do curso
$chave   = encrypt($chaveCript, 'd');
$idCurso = encrypt($idCursoEnc, 'd');

// Verificar validade da chave
$stmtChave = $con->prepare("SELECT chavesc, chaveturmasc FROM new_sistema_chave WHERE chavesc = :chavesc");
$stmtChave->bindParam(":chavesc", $chave);
$stmtChave->execute();
$rwChave = $stmtChave->fetch(PDO::FETCH_ASSOC);

if (!$rwChave) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Chave do curso inválida ou expirada.']);
    exit;
}

$chaveTurma = $rwChave['chaveturmasc'];

try {
    // Verificar se já existe aluno com esse e-mail nesta turma
    $queryCad = $con->prepare("
        SELECT nc.codigocadastro 
        FROM new_sistema_cadastro AS nc
        JOIN new_sistema_inscricao_PJA AS ni ON ni.codigousuario = nc.codigocadastro
        WHERE nc.email = :email AND ni.chaveturma = :chaveturma
        LIMIT 1
    ");
    $queryCad->bindParam(":email", $email);
    $queryCad->bindParam(":chaveturma", $chaveTurma);
    $queryCad->execute();
    $existe = $queryCad->fetch(PDO::FETCH_ASSOC);

    if ($existe) {
        $idUsuario = $existe['codigocadastro'];
        $encIdUsuario = encrypt($idUsuario, 'e');

        $_SESSION['idUsuario']    = $encIdUsuario;
        $_SESSION['emailUsuario'] = $email;
        $_SESSION['nomeUsuario']  = $nome;
        $_SESSION['chaveTurma']   = $chaveTurma;

        echo json_encode([
            'status'   => 'ja_inscrito',
            'mensagem' => 'Usuário já inscrito nesta turma.',
            'redirect' => 'pagina_vendasPlano.php'
        ]);
        exit;
    }

    // Verifica se já existe um usuário com esse e-mail (independente da turma)
    $stmtVerifica = $con->prepare("SELECT codigocadastro FROM new_sistema_cadastro WHERE email = :email LIMIT 1");
    $stmtVerifica->bindParam(":email", $email);
    $stmtVerifica->execute();
    $usuarioExistente = $stmtVerifica->fetch(PDO::FETCH_ASSOC);

    if ($usuarioExistente) {
        $idUsuario = $usuarioExistente['codigocadastro'];
    } else {
        // Criar nome da pasta do aluno
        $pasta = mesabreviado(date('Y-m-d')) . "_" . date("Ymd") . time();

        // Inserir novo aluno
        $stmt = $con->prepare("
            INSERT INTO new_sistema_cadastro (nome, pastasc, email, celular, data_sc)
            VALUES (:nome, :pasta, :email, :telefone, NOW())
        ");
        $stmt->execute([
            ':nome'     => $nome,
            ':pasta'    => $pasta,
            ':email'    => $email,
            ':telefone' => $telefone
        ]);

        $idUsuario = $con->lastInsertId();
    }

    $encIdUsuario = encrypt($idUsuario, 'e');

    // Prazo e datas (ajuste suas helpers conforme necessário)
    $dataprazo = dataprazo($data, 2); // +2 dias, por exemplo

    // Vincular aluno à turma
    $stmtInscricao = $con->prepare("
        INSERT INTO new_sistema_inscricao_PJA 
            (codigousuario, chaveturma, codcurso_ip, dataprazosi, datarenovacao, horarenovacao, data_ins, hora_ins)
        VALUES 
            (:iduser, :chaveturma, :idcurso, :dataprazo, :datarenovacao, :horarenovacao, NOW(), :hora)
    ");
    $stmtInscricao->execute([
        ':iduser'        => $idUsuario,
        ':chaveturma'    => $chaveTurma,
        ':idcurso'       => $idCurso,
        ':dataprazo'     => $dataprazo,
        ':datarenovacao' => $data,
        ':horarenovacao' => $hora,
        ':hora'          => $hora
    ]);

    // =============================
    // EMAIL DE CONFIRMAÇÃO (INÍCIO)
    // =============================

    // 1) (Opcional) gerar senha temporária e salvar no cadastro
    //    - Se sua tabela NÃO tiver o campo "senha", comente este bloco.
    $senhaCadastro = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)); // 8 chars
    try {
        $stmtPwd = $con->prepare("UPDATE new_sistema_cadastro SET senha = :senha WHERE codigocadastro = :id LIMIT 1");
        $stmtPwd->execute([
            ':senha' => password_hash($senhaCadastro, PASSWORD_DEFAULT),
            ':id'    => $idUsuario
        ]);
    } catch (\Throwable $e) {
        // Se falhar (ex.: campo inexistente), mantemos o fluxo e ajustamos a mensagem do e-mail
        $senhaCadastro = 'Crie sua senha no primeiro acesso';
    }

    // 2) Buscar dados do curso e da turma para preencher o template do e-mail
    //    ATENÇÃO: ajuste nomes de colunas/tabelas conforme seu schema real.
    $sqlInfo = $con->prepare("
        SELECT 
            c.nome_curso       AS nomeCurso,
            c.cargahoraria     AS cargahoraria,
            c.qtdaulas         AS aulas,
            t.nometurma        AS NomeTurma,
            t.datainicio       AS datainicio,
            t.datafim          AS datafim,
            t.link_whatsapp    AS whatsapp
        FROM new_sistema_cursos_turmas t
        JOIN new_sistema_cursos c ON c.idcurso = t.idcurso
        WHERE t.chaveturmasc = :chave
        LIMIT 1
    ");
    $sqlInfo->execute([':chave' => $chaveTurma]);
    $info = $sqlInfo->fetch(PDO::FETCH_ASSOC) ?: [];

    // Fallbacks seguros
    $nomeCurso   = $info['nomeCurso']   ?? 'Curso On-line';
    $cargahoraria = $info['cargahoraria'] ?? '—';
    $aulas       = $info['aulas']       ?? '—';
    $NomeTurma   = $info['NomeTurma']   ?? 'Turma';
    $datainicio  = !empty($info['datainicio']) ? date('d/m/Y', strtotime($info['datainicio'])) : 'A confirmar';
    $datafim     = !empty($info['datafim'])    ? date('d/m/Y', strtotime($info['datafim']))    : 'A confirmar';
    $whatsapp    = $info['whatsapp']    ?? 'https://wa.me/5585XXXXXXXX'; // ajuste se quiser

    // 3) Preparar assunto e variáveis usadas pelos módulos
    $emailpara = $email;
    $nomepara  = $nome;

    $assunto = "Inscrição confirmada: " . $nomeCurso;
    $subject = '=?UTF-8?B?' . base64_encode($assunto) . '?=';

    // (Opcional) headers adicionais – PHPMailer não precisa destes, mas manterei seu include
    include '../../modulos_mail/modulo_mail_headers.php';

    // 4) Montar corpo do e-mail usando seu template existente
    //    O template usa as variáveis: $nome, $nomeCurso, $NomeTurma, $cargahoraria, $aulas,
    //    $datainicio, $datafim, $whatsapp, $emailpara, $senhaCadastro e deve preencher $Body.
    include '../../modulos_mail/modulo_mail_body_InscricaoAluno.php';

    // 5) Enviar com PHPMailer (se falhar, não bloqueia a inscrição)
    $emailEnviado = false;
    try {
        include '../../modulos_mail/modulo_mail_send.php';
        // Se chegar aqui sem exceção do PHPMailer, consideramos enviado
        $emailEnviado = true;
    } catch (\Throwable $e) {
        $emailEnviado = false;
        // Opcional: logar erro em tabela própria
        // $log = $con->prepare("INSERT INTO logs_email (tipo, payload, erro, datahora) VALUES ('inscricao', :payload, :erro, NOW())");
        // $log->execute([':payload' => json_encode($_POST), ':erro' => $e->getMessage()]);
    }

    // ===========================
    // EMAIL DE CONFIRMAÇÃO (FIM)
    // ===========================

    // Sessão/logon
    $_SESSION['idUsuario']    = $encIdUsuario;
    $_SESSION['emailUsuario'] = $email;
    $_SESSION['nomeUsuario']  = $nome;
    $_SESSION['chaveTurma']   = $chaveTurma;

    echo json_encode([
        'status'      => 'ok',
        'id'          => $encIdUsuario,
        'email'       => $email,
        'email_enviado' => $emailEnviado ? 'sim' : 'nao'
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro no banco de dados.']);
}
