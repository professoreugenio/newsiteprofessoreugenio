<?php define('BASEPATH', true);
include '../../../conexao/class.conexao.php';
include '../../../autenticacao.php';
header('Content-Type: text/html; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCurso = isset($_POST['idCurso']) ? $_POST['idCurso'] : 0;
    $idCurso = encrypt($idCurso, $action = 'd');
    $pasta = $_POST['pasta'];
    $tipo = $_POST['tipo'];
    if ($idCurso <= 0) {
        echo '<div class="alert alert-danger">ID do curso inválido.</div>';
        exit;
    }
    if (!isset($_FILES['imagemCurso']) || $_FILES['imagemCurso']['error'] !== UPLOAD_ERR_OK) {
        echo '<div class="alert alert-danger">Erro ao receber o arquivo.</div>';
        exit;
    }
    $pastaDestino = "../../../fotos/midias/" . $pasta . "/";
    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0777, true);
    }
    $imagem = $_FILES['imagemCurso'];
    $extensao = strtolower(pathinfo($imagem['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($extensao, $permitidas)) {
        echo '<div class="alert alert-warning">Formato de imagem inválido. Use JPG, PNG ou WEBP.</div>';
        exit;
    }
    $time = time();
    // Nome do arquivo padronizado
    $nomeArquivo = $tipo . "_tipo_{$time}{$_POST['idCurso']}_{$pasta}." . $extensao;
    $caminhoFinal = $pastaDestino . $nomeArquivo;
    if (move_uploaded_file($imagem['tmp_name'], $caminhoFinal)) {
        $caminhoRelativo = $caminhoFinal . $nomeArquivo;
        $query = $con->prepare("SELECT * FROM new_sistema_midias_fotos_PJA WHERE codpublicacao = :idcurso AND tipo = :tipo AND pasta = :pasta ");
        $query->bindParam(":idcurso", $idCurso, PDO::PARAM_INT);
        $query->bindParam(":tipo", $tipo, PDO::PARAM_INT);
        $query->bindParam(":pasta", $pasta, PDO::PARAM_INT);
        $query->execute();
        $fetchImg = $query->fetch(PDO::FETCH_ASSOC);
        if ($fetchImg) {
            $arquivoAntigo = $pastaDestino . $fetchImg['foto'];
            if (file_exists($arquivoAntigo)) {
                unlink($arquivoAntigo); // Exclui fisicamente o arquivo
            } else {
                $foto_size = $imagem['size'];
                $query = $con->prepare("INSERT INTO new_sistema_midias_fotos_PJA (
      codpublicacao,
     
      foto,
      tipo,
      ext,
      size,
      pasta,
      datamf,
      horamf)
      VALUES (
        :codpublicacao,
       
        :foto,
        :tipo,
        :ext,
        :size,
        :pasta,
        :datapf,
        :horapf) ");
                $query->bindParam(":codpublicacao", $idCurso);

                $query->bindParam(":foto", $nomeArquivo);
                $query->bindParam(":tipo", $tipo);
                $query->bindParam(":ext", $extensao);
                $query->bindParam(":size", $foto_size);
                $query->bindParam(":pasta", $pasta);
                $query->bindParam(":datamf", $data);
                $query->bindParam(":horamf", $hora);
                $query->execute();
            }


            $queryUpdate = $con->prepare("UPDATE new_sistema_midias_fotos_PJA 
            SET 
            foto = :imagem, 
            datamf =:datamf, 
            horamf= :horamf 
            WHERE 
            codpublicacao = :idcurso 
            AND tipo = :tipo 
            AND pasta = :pasta
            ");
            $queryUpdate->bindParam(":imagem", $nomeArquivo);
            $queryUpdate->bindParam(":datamf", $data);
            $queryUpdate->bindParam(":horamf", $hora);
            $queryUpdate->bindParam(":idcurso", $idCurso, PDO::PARAM_INT);
            $queryUpdate->bindParam(":tipo", $tipo, PDO::PARAM_INT);
            $queryUpdate->bindParam(":pasta", $pasta, PDO::PARAM_INT);
            $queryUpdate->execute();
        }
        echo '<div class="alert alert-success">Imagem enviada com sucesso!</div>';
        exit();
    } else {
        echo '<div class="alert alert-danger">Erro ao salvar a imagem no servidor.</div>';
    }
} else {
    echo '<div class="alert alert-warning">Requisição inválida.</div>';
}
