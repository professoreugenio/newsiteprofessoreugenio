<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=frequencia_alunos.xls");
header("Pragma: no-cache");
header("Expires: 0");

$chaveturma = $_POST['chaveturma'] ?? '';
$idturma = $_POST['idturma'] ?? '';

$con = config::connect();

// Pegando alunos
$stmtAlunos = $con->prepare("SELECT codigocadastro, nome FROM new_sistema_inscricao_PJA, new_sistema_cadastro WHERE chaveturma = :chaveturma AND codigocadastro = codigousuario ORDER BY nome");
$stmtAlunos->bindParam(":chaveturma", $chaveturma);
$stmtAlunos->execute();
$alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

// Pegando datas das aulas
$stmtDatas = $con->prepare("SELECT dataaulactd FROM new_sistema_cursos_turma_data WHERE codigoturmactd = :idturma ORDER BY dataaulactd");
$stmtDatas->bindParam(":idturma", $idturma);
$stmtDatas->execute();
$datasAulas = $stmtDatas->fetchAll(PDO::FETCH_COLUMN);

echo "<table border='1'>";
echo "<tr><th>Aluno</th>";
foreach ($datasAulas as $data) {
    echo "<th>" . date('d/m', strtotime($data)) . "</th>";
}
echo "</tr>";

foreach ($alunos as $aluno) {
    echo "<tr><td>" . htmlspecialchars($aluno['nome']) . "</td>";

    // Presenças
    $stmtPresenca = $con->prepare("SELECT datara FROM a_site_registraacessos WHERE idusuariora = :idaluno AND idturmara = :idturma");
    $stmtPresenca->bindParam(":idaluno", $aluno['codigocadastro']);
    $stmtPresenca->bindParam(":idturma", $idturma);
    $stmtPresenca->execute();
    $presencas = array_map(function ($d) {
        return date('Y-m-d', strtotime($d['datara']));
    }, $stmtPresenca->fetchAll(PDO::FETCH_ASSOC));

    $hoje = date('Y-m-d');

    foreach ($datasAulas as $data) {
        $dataFormatada = date('Y-m-d', strtotime($data));

        if ($dataFormatada <= $hoje) {
            $presente = in_array($dataFormatada, $presencas);
            echo "<td>" . ($presente ? '*' : 'F') . "</td>";
        } else {
            echo "<td></td>"; // data futura, célula vazia
        }
    }

    echo "</tr>";
}


echo "</table>";
