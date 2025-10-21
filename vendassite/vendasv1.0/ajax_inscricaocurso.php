<?php

declare(strict_types=1);
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));

@date_default_timezone_set('America/Fortaleza');
header('Content-Type: application/json; charset=utf-8');

require_once APP_ROOT . '/conexao/class.conexao.php';   // PDO: config::connect()
require_once APP_ROOT . '/autenticacao.php';            // se precisar
// Função encrypt($payload, $action) já existente no seu projeto (e/d)

/* ========================= Helpers ========================= */

function json_input(): array
{
    $raw = file_get_contents('php://input');
    if ($raw) {
        $data = json_decode($raw, true);
        if (is_array($data)) return $data;
    }
    // fallback para POST tradicional (form-data/x-www-form-urlencoded)
    return $_POST ?? [];
}

function jfail(string $msg, array $extra = []): never
{
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => $msg] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

function jsuccess(array $payload = []): never
{
    http_response_code(200);
    echo json_encode(['ok' => true] + $payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// Normaliza horário aceitando 'manha','tarde','noite' ou HH:MM(:SS)
function normalize_time(?string $v): ?string
{
    if (!$v) return null;
    $v = trim(mb_strtolower($v));
    // Mapeamento padrão — ajuste conforme sua regra real:
    if (in_array($v, ['manhã', 'manha'], true)) return '08:00:00';
    if ($v === 'tarde') return '14:00:00';
    if ($v === 'noite') return '19:00:00';
    // Aceita HH:MM ou HH:MM:SS
    if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $v) === 1) {
        return strlen($v) === 5 ? ($v . ':00') : $v;
    }
    return null;
}



/* ========================= Entrada ========================= */

$in = json_input();

// Campos do front:
$nome     = trim((string)($in['nome']     ?? ''));
$email    = trim((string)($in['email']    ?? ''));
$telefone = trim((string)($in['telefone'] ?? ''));
$objetivo = trim((string)($in['objetivo'] ?? ''));
$utm      = trim((string)($in['utm']      ?? ''));
$horario  = normalize_time($in['horario'] ?? null);

// Campos hidden (criptografados) vindos do form:
$encIdCurso   = (string)($in['idCurso']      ?? ($_POST['idCurso']      ?? ''));
$encIdTurma   = (string)($in['idTurma']      ?? ($_POST['idTurma']      ?? ''));
// antes
// $encChave = (string)($in['Codigochave']  ?? ($_POST['Codigochave']  ?? ''));

// depois (aceita variações e tenta fallback raw se decrypt falhar)
$encChave = (string)($in['Codigochave'] ?? $_POST['Codigochave'] ?? $_POST['CodigoChave'] ?? '');
$chaveSC  = '';

if ($encChave !== '') {
    try {
        $dec = encrypt($encChave, 'd');
        // se decrypt retornou vazio ou igual (algumas libs retornam igual quando falha), tenta usar o próprio valor
        $chaveSC = $dec ?: $encChave;
    } catch (Throwable $e) {
        // fallback: pode ser que já venha em texto plano
        $chaveSC = $encChave;
    }
}

if ($chaveSC === '') {
    jfail('Chave inválida ou ausente.');
}

$afiliadoCode = (string)($in['CodigoAfiliado'] ?? ($_POST['CodigoAfiliado'] ?? ($_GET['af'] ?? '')));

// Validações básicas
if ($nome === '' || $email === '' || $telefone === '') {
    jfail('Nome, e-mail e telefone são obrigatórios.');
}

// Descriptografar ID do curso, ID da turma e a CHAVE
try {
    $idCurso  = (int)encrypt($encIdCurso, 'd');  // esperado inteiro
} catch (Throwable $e) {
    $idCurso = 0;
}
try {
    $idTurma  = (int)encrypt($encIdTurma, 'd');  // opcional; usamos a chave como verdade absoluta da turma
} catch (Throwable $e) {
    $idTurma = 0;
}
try {
    $chaveSC  = (string)encrypt($encChave, 'd'); // valor de new_sistema_chave.chavesc
} catch (Throwable $e) {
    $chaveSC = '';
}

if ($chaveSC === '') {
    jfail('Chave inválida ou ausente.');
}

/* ========================= Conexão ========================= */

try {
    /** @var PDO $con */
    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Throwable $e) {
    jfail('Não foi possível conectar ao banco de dados.');
}

/* ========================= Validações de chave/turma/curso ========================= */

// 1) Valida CHAVE (new_sistema_chave)
$stmtChave = $con->prepare("
    SELECT chavesc, chaveturmasc
    FROM new_sistema_chave
    WHERE chavesc = :chave
    LIMIT 1
");
$stmtChave->bindValue(':chave', $chaveSC, PDO::PARAM_STR);
$stmtChave->execute();
$rowChave = $stmtChave->fetch(PDO::FETCH_ASSOC);
if (!$rowChave) {
    jfail('Chave de inscrição inválida.');
}
$chaveTurma = (string)$rowChave['chaveturmasc']; // string de ligação à turma

// 2) Valida TURMA (new_sistema_cursos_turmas) pela chave
$stmtTurma = $con->prepare("
    SELECT codcursost, nometurma
    FROM new_sistema_cursos_turmas
    WHERE chave = :chaveTurma
    LIMIT 1
");
$stmtTurma->bindValue(':chaveTurma', $chaveTurma, PDO::PARAM_STR);
$stmtTurma->execute();
$rowTurma = $stmtTurma->fetch(PDO::FETCH_ASSOC);
if (!$rowTurma) {
    jfail('Turma não encontrada para a chave informada.');
}
$cursoIdFromTurma = (int)$rowTurma['codcursost'];
$nomeTurma        = (string)$rowTurma['nometurma'];

// 3) Valida CURSO (new_sistema_curso) — atenção: tabela no singular conforme sua instrução
if ($idCurso <= 0) {
    // se não veio no hidden, usa o derivado da turma
    $idCurso = $cursoIdFromTurma;
}
$stmtCurso = $con->prepare("
    SELECT nome
    FROM new_sistema_curso
    WHERE codigocurso = :idcurso
    LIMIT 1
");
$stmtCurso->bindValue(':idcurso', $idCurso, PDO::PARAM_INT);
$stmtCurso->execute();
$rowCurso = $stmtCurso->fetch(PDO::FETCH_ASSOC);
if (!$rowCurso) {
    jfail('Curso inválido.');
}
$nmCurso = (string)$rowCurso['nome'];

/* ========================= Verificações de existência ========================= */

// Já possui cadastro e já inscrito nesta turma?
$queryCad = $con->prepare("
    SELECT nc.codigocadastro 
    FROM new_sistema_cadastro AS nc
    JOIN new_sistema_inscricao_PJA AS ni 
        ON ni.codigousuario = nc.codigocadastro
    WHERE nc.email = :email 
      AND ni.chaveturma = :chaveturma
    LIMIT 1
");
$queryCad->bindValue(':email', $email, PDO::PARAM_STR);
$queryCad->bindValue(':chaveturma', $chaveTurma, PDO::PARAM_STR);
$queryCad->execute();
$rowExist = $queryCad->fetch(PDO::FETCH_ASSOC);

if ($rowExist && (int)$rowExist['codigocadastro'] > 0) {
    // Já inscrito — ainda assim podemos registrar horário escolhido
    $idUser = (int)$rowExist['codigocadastro'];

    if ($horario) {
        $stmtHorario = $con->prepare("
            INSERT INTO new_sistema_inscricao_horario
                (horarioih, idusuarioih, idturmaih, dataih, horaih)
            VALUES
                (:horario, :user, :turma, CURRENT_DATE(), CURRENT_TIME())
        ");
        $stmtHorario->bindValue(':horario', $horario, PDO::PARAM_STR);
        $stmtHorario->bindValue(':user', $idUser, PDO::PARAM_INT);
        // OBS: idturmaih — use a PK real da turma. Se não tiver aqui, usamos codcursost como fallback.
        $stmtHorario->bindValue(':turma', $cursoIdFromTurma, PDO::PARAM_INT);
        $stmtHorario->execute();
    }

    // E-mail opcional de “já cadastrado” — pode pular.
    jsuccess([
        'lead'     => $idUser,
        'message'  => 'Usuário já possuía inscrição nesta turma.',
        'redirect' => 'vendas_plano.php'
    ]);
}

/* ========================= Início da transação ========================= */

try {
    $con->beginTransaction();

    // Verifica se existe cadastro (mesmo e-mail), mas sem inscrição nesta turma
    $stmtFindUser = $con->prepare("
        SELECT codigocadastro 
        FROM new_sistema_cadastro
        WHERE email = :email
        LIMIT 1
    ");
    $stmtFindUser->bindValue(':email', $email, PDO::PARAM_STR);
    $stmtFindUser->execute();
    $rowUser = $stmtFindUser->fetch(PDO::FETCH_ASSOC);

    if ($rowUser && (int)$rowUser['codigocadastro'] > 0) {
        $idUser = (int)$rowUser['codigocadastro'];
    } else {
        // Cria cadastro
        $pasta = 'alunos'; // ajuste se há regra
        $hash  = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT); // senha aleatória (não será enviada)
        $stmt = $con->prepare("
            INSERT INTO new_sistema_cadastro (afiliacaoSC, nome, pastasc, email, senha, celular, data_sc)
            VALUES (:afiliado, :nome, :pasta, :email, :senha, :telefone, NOW())
        ");
        $stmt->bindValue(':afiliado', $afiliadoCode, PDO::PARAM_STR);
        $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindValue(':pasta', $pasta, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':senha', $hash, PDO::PARAM_STR);
        $stmt->bindValue(':telefone', $telefone, PDO::PARAM_STR);
        $stmt->execute();
        $idUser = (int)$con->lastInsertId();
    }

    // Verifica se já há inscrição deste usuário nesta turma (concorrência)
    $stmtCheckIns = $con->prepare("
        SELECT 1 
        FROM new_sistema_inscricao_PJA
        WHERE codigousuario = :u AND chaveturma = :t
        LIMIT 1
    ");
    $stmtCheckIns->bindValue(':u', $idUser, PDO::PARAM_INT);
    $stmtCheckIns->bindValue(':t', $chaveTurma, PDO::PARAM_STR);
    $stmtCheckIns->execute();
    $already = (bool)$stmtCheckIns->fetchColumn();

    if (!$already) {
        // Datas de controle — ajuste conforme sua regra de prazo/renovação
        $dataprazo      = (new DateTime('+3 days'))->format('Y-m-d');  // reserva por 3 dias
        $datarenovacao  = (new DateTime('+1 year'))->format('Y-m-d');  // exemplo
        $horarenovacao  = (new DateTime('now'))->format('H:i:s');
        $horaNow        = (new DateTime('now'))->format('H:i:s');

        $stmtInscricao = $con->prepare("
            INSERT INTO new_sistema_inscricao_PJA 
                (codigousuario, chaveturma, codcurso_ip, dataprazosi, datarenovacao, horarenovacao, data_ins, hora_ins)
            VALUES 
                (:iduser, :chaveturma, :idcurso, :dataprazo, :datarenovacao, :horarenovacao, NOW(), :hora)
        ");
        $stmtInscricao->bindValue(':iduser', $idUser, PDO::PARAM_INT);
        $stmtInscricao->bindValue(':chaveturma', $chaveTurma, PDO::PARAM_STR);
        $stmtInscricao->bindValue(':idcurso', $idCurso, PDO::PARAM_INT);
        $stmtInscricao->bindValue(':dataprazo', $dataprazo, PDO::PARAM_STR);
        $stmtInscricao->bindValue(':datarenovacao', $datarenovacao, PDO::PARAM_STR);
        $stmtInscricao->bindValue(':horarenovacao', $horarenovacao, PDO::PARAM_STR);
        $stmtInscricao->bindValue(':hora', $horaNow, PDO::PARAM_STR);
        $stmtInscricao->execute();
    }

    // Horário escolhido (opcional)
    if ($horario) {
        $stmtHorario = $con->prepare("
            INSERT INTO new_sistema_inscricao_horario
                (horarioih, idusuarioih, idturmaih, dataih, horaih)
            VALUES
                (:horario, :user, :turma, CURRENT_DATE(), CURRENT_TIME())
        ");
        $stmtHorario->bindValue(':horario', $horario, PDO::PARAM_STR);
        $stmtHorario->bindValue(':user', $idUser, PDO::PARAM_INT);
        // OBS: idturmaih — use a PK real da turma. Sem ela aqui, uso codcursost como fallback.
        $stmtHorario->bindValue(':turma', $cursoIdFromTurma, PDO::PARAM_INT);
        $stmtHorario->execute();
    }

    $con->commit();
} catch (Throwable $e) {
    if ($con->inTransaction()) $con->rollBack();
    jfail('Não foi possível concluir a inscrição. Tente novamente.');
}

/* ========================= E-mail de inscrição ========================= */

// Variáveis esperadas pelo template
$celular   = $telefone;   // alias
$prazoTexto = 'Sua pré-reserva está ativa por até 3 dias. Conclua a próxima etapa para garantir a vaga.';
$linkAcesso = ''; // se já houver área do aluno
$linkVisualizarWeb = '';
$whatsapp  = '';
$textoHeader = 'Nesta primeira fase realizamos a coleta de dados e a reserva da sua inscrição. Confira os detalhes:';

// Monta assunto e cabecalhos
$emailpara = $email;
$nomepara  = $nome;
$assunto   = $nomepara . ' se inscreveu NO CURSO ' . $nmCurso;
$subject   = '=?UTF-8?B?' . base64_encode($assunto) . '?=';

// Envia
try {
    include APP_ROOT . '/modulos_mail/modulo_mail_headers.php';
    include APP_ROOT . '/modulos_mail/modulo_mail_body_InscricaoAluno.php';
    include APP_ROOT . '/modulos_mail/modulo_mail_send.php';
} catch (Throwable $e) {
    // não bloqueia o fluxo — logue se necessário
    // error_log('Falha ao enviar e-mail de inscrição: ' . $e->getMessage());
}

/* ========================= Resposta ao front ========================= */

jsuccess([
    'lead'     => $idUser,
    'redirect' => 'vendas_plano.php'
]);
