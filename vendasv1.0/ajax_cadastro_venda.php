<?php
define('BASEPATH', true);
include('../../conexao/class.conexao.php');
include('../../autenticacao.php');
$con = config::connect();
// Inicialização segura e checagem de variáveis POST
$chavePost = $_POST['chaveCadastro'] ?? '';
$nome = $_POST['nomeCadastro'] ?? '';
$assinatura = $_POST['assinatura'] ?? '';
$email = $_POST['emailCadastro'] ?? '';
$senha = $_POST['senhaCadastro'] ?? '';
$tipoAssinatura = $_POST['tipoAssinatura'] ?? '';
$ts = time();
$ano = date("Y");
if (empty($chavePost) || empty($email) || empty($senha)) {
    echo 'Dados obrigatórios faltando';
    exit;
}
// Descriptografar chave
$chave = encrypt($chavePost, 'd');
$exp = explode("&", $chave);
$chave = $exp[1] ?? null;
if (!$chave) {
    echo 'Chave inválida';
    exit;
}
$pasta = mesabreviado($data) . "_" . date("Ymd") . $ts;
validadeCodigo($chave);
// Verifica se a chave existe
$query = $con->prepare("SELECT chavesc, chaveturmasc FROM new_sistema_chave WHERE chavesc = :chavesc");
$query->bindParam(":chavesc", $chave);
$query->execute();
$rwChave = $query->fetch(PDO::FETCH_ASSOC);
if (!$rwChave) {
    echo '1'; // Chave inválida
    exit;
}
$chaveTurma = $rwChave['chaveturmasc'];
// Validação de e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo '2'; // Email inválido
    exit;
}
// Valida e busca informações da turma
$queryTurma = $con->prepare("SELECT * FROM new_sistema_cursos_turmas WHERE chave = :id");
$queryTurma->bindParam(":id", $chaveTurma);
$queryTurma->execute();
$rwTurma = $queryTurma->fetch(PDO::FETCH_ASSOC);
$pix  = $rwTurma['chavepix'];
$pixv = $rwTurma['chavepixvitalicia'];
if (!$rwTurma || $rwTurma['visivelst'] == 0) {
    echo '3'; // Turma não liberada
    exit;
}
extract($rwTurma); // Cria variáveis a partir das chaves do array da turma
$idadm = "1";
// Verifica se já está inscrito na turma
$queryCad = $con->prepare("
    SELECT * 
    FROM new_sistema_cadastro AS nc
    JOIN new_sistema_inscricao_PJA AS ni ON ni.codigousuario = nc.codigocadastro
    WHERE nc.email = :email AND ni.chaveturma = :chaveturma
");
$queryCad->bindParam(":email", $email);
$queryCad->bindParam(":chaveturma", $chaveTurma);
$queryCad->execute();
$rwCadastro = $queryCad->fetch(PDO::FETCH_ASSOC);
if ($rwCadastro) {
    echo '4';
    // Já cadastrado na turma
    exit;
}
// Criptografias
$senhaenc = encrypt($email . "&" . $senha, 'e');
$chaveGerada = strtoupper(md5($email . "&" . $senha));
// Verifica se já existe o aluno cadastrado
$querySelect = $con->prepare("SELECT * FROM new_sistema_cadastro WHERE email = :email");
$querySelect->bindParam(":email", $email);
$querySelect->execute();
$rwAluno = $querySelect->fetch(PDO::FETCH_ASSOC);
if (!$rwAluno) {
    $queryInsert = $con->prepare("
        INSERT INTO new_sistema_cadastro (
            codadmin, codigo, turma_sc, turma, nome, email, senha, chave, pastasc, data_sc, hora_sc
        ) VALUES (
            :codadmin, :codigo, :turma_sc, :turma, :nome, :email, :senha, :chave, :pasta, :data_sc, :hora_sc
        )
    ");
    $queryInsert->execute([
        ":codadmin" => $idadm,
        ":codigo" => $chaveTurma,
        ":turma_sc" => $codigoturma,
        ":turma" => $codigoturma,
        ":nome" => $nome,
        ":email" => $email,
        ":senha" => $senhaenc,
        ":chave" => $chaveGerada,
        ":pasta" => $pasta,
        ":data_sc" => $data,
        ":hora_sc" => $hora
    ]);
    $codigoaluno = $con->lastInsertId();
} else {
    $codigoaluno = $rwAluno['codigocadastro'];
}
// Inscrição
$plano = ($tipoAssinatura == "Assinatura Vitalicia") ? "1" : "0";
$dataprazo = dataprazo($data, 2);
$renovacao = "1";
$hr = "0";
$hora = date("H:i:s", time() - ($hr));
$data = date("Y-m-d");
$queryInscricao = $con->prepare("
    INSERT INTO new_sistema_inscricao_PJA (
        codigousuario, 
        chaveturma, 
        codcurso_ip, 
        codadmin, 
        data,
        hora,
        ano_ip,
        visivel_ci, 
        renovacaosi, 
        plano,
        dataprazosi, 
        datarenovacao,
        data_ci,
        hora_ci,
        horarenovacao
    ) VALUES (
        :codigousuario, 
        :chaveturma, 
        :idcurso, 
        :codadmin, 
        :data,
        :hora,
        :anoip,
        :visivel_ci,
        :renovacao, 
        :plano,
        :dataprazo,
        :datarenova,
        :dataci,
        :horaci,
        :horarenova
    )
");
/*
 */
$queryInscricao->execute([
    ":codigousuario" => $codigoaluno,
    ":chaveturma" => $chaveTurma,
    ":idcurso" => $codcursost,
    ":codadmin" => $idadm,
    ":data" => $data,
    ":hora" => $hora,
    ":anoip" => $ano,
    ":visivel_ci" => "1",
    ":renovacao" => $renovacao,
    ":plano" => $plano,
    ":dataprazo" => $dataprazo,
    ":datarenova" => $data,
    ":dataci" => $data,
    ":horaci" => $hora,
    ":horarenova" => $hora
]);
if ($queryInscricao) {
    $key = encrypt($plano . "&" . $nome . "&" . $email, 'e');
    setcookie('dadoscadastro', $key, time() + 21600, '/'); // 6 horas
    // Envio de e-mails
    $datainicio = diadasemana($data, 5);
    $curso = $nometurma;
    $codigodesbloqueio = encrypt($codigoaluno . "&" . $chaveTurma . "&" . "&" . $codcursost . "&" . $plano, 'e');
    if ($plano == "1") {
        $pix = $chavepixvitalicia;
    }
    $emailpara = $email;
    $nomepara = $nome;
    var_dump($emailpara);
    $assunto = "$curso Plano $tipoAssinatura";
    $subject = '=?UTF-8?B?' . base64_encode($assunto) . '?=';
    include '../../modulos_mail/modulo_mail_headers.php';
    include '../../modulos_mail/modelomembro.php';
    $assunto2 = "EMENTA $curso";
    $subject2 = '=?UTF-8?B?' . base64_encode($assunto2) . '?=';
    include '../../modulos_mail/modeloementamembro.php';
    include '../../modulos_mail/modulo_mail_send.php';
}
