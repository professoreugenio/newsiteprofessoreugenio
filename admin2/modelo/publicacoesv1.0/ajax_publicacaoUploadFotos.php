<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: text/plain');

try {
    if (!isset($_POST['idpublicacao'])) {
        throw new Exception('ID da publicação não foi enviado.');
    }

    $idArtigo = encrypt($_POST['idpublicacao'], 'd');
    if (!is_numeric($idArtigo)) {
        throw new Exception('ID inválido.');
    }

    $con = config::connect();

    // Consulta os dados da publicação (para obter pasta e módulo)
    $queryArt = $con->prepare("SELECT pasta, codmodulo_sp FROM new_sistema_publicacoes_PJA WHERE codigopublicacoes = :id");
    $queryArt->bindParam(":id", $idArtigo, PDO::PARAM_INT);
    $queryArt->execute();
    $dadosPub = $queryArt->fetch(PDO::FETCH_ASSOC);

    if (!$dadosPub) {
        throw new Exception('Publicação não encontrada.');
    }

    $pasta = $dadosPub['pasta'];
    $idModulo = $dadosPub['codmodulo_sp'];

    // Define diretórios
    $dirBase = "../../../fotos/publicacoes";
    $uploadDir = $dirBase . "/" . $pasta;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $ts = time();
    $numImagens = 0;

    foreach ($_FILES['images']['name'] as $key => $name) {
        $tmpName = $_FILES['images']['tmp_name'][$key];
        $fotoSize = $_FILES['images']['size'][$key];
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            continue;
        }

        $newName = 'img_' . $key . '_' . $idArtigo . '_' . $pasta . '_' . $ts . '_' . uniqid() . '.' . $extension;
        $caminhoCompleto = $uploadDir . '/' . $newName;

        if (move_uploaded_file($tmpName, $caminhoCompleto)) {
            $query = $con->prepare("INSERT INTO new_sistema_publicacoes_fotos_PJA 
                (codpublicacao, idmodulo_pf, foto, numimg, ext, size, pasta, data, hora)
                VALUES (:codpublicacao, :idmodulo, :foto, :numimg, :ext, :size, :pasta, :data, :hora)");

            $query->bindParam(':codpublicacao', $idArtigo);
            $query->bindParam(':idmodulo', $idModulo);
            $query->bindParam(':foto', $newName);
            $query->bindParam(':numimg', $key, PDO::PARAM_INT);
            $query->bindParam(':ext', $extension);
            $query->bindParam(':size', $fotoSize, PDO::PARAM_INT);
            $query->bindParam(':pasta', $pasta);
            $query->bindParam(':data', $data);
            $query->bindParam(':hora', $hora);
            $query->execute();

            $numImagens++;
        }
    }

    if ($numImagens > 0) {
        echo "1";
    } else {
        echo "Nenhuma imagem válida foi enviada.";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
