<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
if (!isset($_GET['id'])) exit;
$idCurso = intval($_GET['id']);
$con = config::connect();
$stmt = $con->prepare("SELECT codigoturma, nometurma, chave, datast FROM new_sistema_cursos_turmas WHERE codcursost = :id ORDER BY datast DESC");
$stmt->bindParam(":id", $idCurso);
$stmt->execute();
if ($stmt->rowCount() === 0) {
    echo "<small class='text-muted'>Nenhuma turma encontrada.</small>";
    exit;
}
echo "<ul class='list-group aos-init aos-animate' data-aos='fade-up' data-aos-duration='600'>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $chave = $row['chave'];
    $query = $con->prepare("SELECT * FROM new_sistema_inscricao_PJA WHERE chaveturma = :chave ");
    $query->bindParam(":chave", $chave);
    $query->execute();
    $fetch = $query->fetchALL();
    $countAlunos = count($fetch);


    $linkTurma = 'cursos_TurmasAlunos.php?id=' . encrypt($idCurso, 'e') . '&tm=' . encrypt($row['codigoturma'], 'e');
    echo '<li class="list-group-item border-0 px-2 py-1">';
    echo '<a href="' . $linkTurma . '" class="d-block rounded px-2 py-1 text-decoration-none text-dark turma-hover">';
    echo '<i class="bi bi-people me-2 text-primary"></i>' . htmlspecialchars($row['nometurma']);
    echo '[ ' . $countAlunos . ']';

    echo '</a>';
    echo '</li>';
}
echo "</ul>";
