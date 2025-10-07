<?php
define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';
?>
<?php
if (!isset($_POST['idaluno']) || !isset($_POST['idaula'])) exit;

$idAluno = $_POST['idaluno'];
$idaula = $_POST['idaula'];
$codigocurso = $_POST['idcurso'];

foreach ($_POST as $key => $resposta) {
    if (str_starts_with($key, 'resposta_')) {
        $idQuestao = str_replace('resposta_', '', $key);

        $insert = $con->prepare("INSERT INTO a_curso_questionario_resposta 
            (idalunoqr, idcursoqr, idquestionarioqr, idaulaqr, respostaqr, cont, dataqr, horaqr, visivel) 
            VALUES (:idaluno, :idcurso, :idquestao, :idaula, :resposta, 1, NOW(), NOW(), 1)");
        $insert->execute([
            ":idaluno" => $idAluno,
            ":idcurso" => $codigocurso,
            ":idquestao" => $idQuestao,
            ":idaula" => $idaula,
            ":resposta" => $resposta
        ]);
    }
}

echo "OK";
