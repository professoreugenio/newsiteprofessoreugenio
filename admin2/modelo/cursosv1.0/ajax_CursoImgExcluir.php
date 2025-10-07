<?php define('BASEPATH', true);
include '../../../conexao/class.conexao.php';
include '../../../autenticacao.php';
header('Content-Type: text/html; charset=utf-8');

$idCurso = $_POST['idCurso'] ?? null;
$tipo = $_POST['tipo'] ?? null;
$idCurso = isset($idCurso) ? $idCurso : 0;
$idCurso = encrypt($idCurso, $action = 'd');
if (!$idCurso || !is_numeric($idCurso)) {
    echo '<div class="alert alert-danger">ID inválido.</div>';
    exit;
} else {
    // Buscar imagem
    $query = $con->prepare("SELECT * FROM new_sistema_midias_fotos_PJA WHERE codpublicacao = :id AND tipo = :tipo");
    $query->bindParam(':id', $idCurso, PDO::PARAM_INT);
    $query->bindParam(':tipo', $tipo, PDO::PARAM_INT);
    $query->execute();
    $img = $query->fetch(PDO::FETCH_ASSOC);
    if ($img) {
        $pastaDestino = "../../../fotos/midias/" . $img['pasta'] . "/";
        $arquivo = $pastaDestino . $img['foto'];

        if (file_exists($arquivo)) {
            unlink($arquivo);
            $queryDelete = $con->prepare("DELETE FROM new_sistema_midias_fotos_PJA WHERE codpublicacao = :id AND tipo = :tipo");
            $queryDelete->bindParam(':id', $idCurso, PDO::PARAM_INT);
            $queryDelete->bindParam(':tipo', $tipo, PDO::PARAM_INT);
            $queryDelete->execute();
            if ($queryDelete->rowCount() >= 1) {
                echo '<div class="alert alert-success">Excluído com sucesso! 👍</div>';
                exit;
            } else {
                echo '<div class="alert alert-warning">Erro na aplicação.</div>';
                exit;
            }
        }
    }
};
