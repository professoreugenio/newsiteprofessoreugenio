<?php define('BASEPATH', true);
include('../../conexao/class.conexao.php'); ?>
<?php include('../../autenticacao.php'); ?>
<?php
$tabela = "new_sistema_usuario";


$addtime = 60 * 60 * 4;
$duracao = time() + $addtime;
?>

<?php
if (!empty($_COOKIE['adminstart'])) {
  $dectoken = encrypt($_COOKIE['adminstart'], $action = 'd');
  $expta = explode('&', $dectoken);
  $id = $expta[0];
  $codigoUser = $id;
} else {
  if (!empty($_COOKIE['startusuario'])) {
    $dectoken = encrypt($_COOKIE['startusuario'], $action = 'd');
    $expta = explode('&', $dectoken);
    $id = $expta[0];
    $codigoUser = $id;
  }
}

 $id;
?>


<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Configurações de diretórios

  require '../perfilv2.0/selectusuario.php';

  $uploadDir = $diretorio . '/' . $pasta . '/';

  $allowedFormats = ['image/jpeg', 'image/png'];

  // Verificar se o diretório existe, se não, criar
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
  }

  // Verificar se há um arquivo de upload
  if (isset($_FILES['imguser']) && $_FILES['imguser']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['imguser']['tmp_name'];
    $fileName = $_FILES['imguser']['name'];
    $fileType = $_FILES['imguser']['type'];

    // Verificar o tipo de arquivo
    if (in_array($fileType, $allowedFormats)) {
      $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
      $newFileName = $pasta . '_' . uniqid() . '.' . $fileExt;
      $uploadFilePath = $uploadDir . $newFileName;

      // Mover o arquivo para o diretório de destino
      if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
         "Upload realizado com sucesso!<br>";

        // Função para redimensionar a imagem
        function resizeImage($source, $destination, $newWidth)
        {
          list($originalWidth, $originalHeight) = getimagesize($source);
          $newHeight = ($originalHeight / $originalWidth) * $newWidth;

          $imageType = exif_imagetype($source);

          // Criar a imagem de acordo com o tipo
          switch ($imageType) {
            case IMAGETYPE_JPEG:
              $sourceImage = imagecreatefromjpeg($source);
              break;
            case IMAGETYPE_PNG:
              $sourceImage = imagecreatefrompng($source);
              break;
            default:
              return false;
          }

          // Criar uma nova imagem com as novas dimensões
          $newImage = imagecreatetruecolor($newWidth, $newHeight);
          imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

          // Salvar a nova imagem
          switch ($imageType) {
            case IMAGETYPE_JPEG:
              imagejpeg($newImage, $destination);
              break;
            case IMAGETYPE_PNG:
              imagepng($newImage, $destination);
              break;
          }

          // Liberar a memória
          imagedestroy($sourceImage);
          imagedestroy($newImage);

          return true;
        }

        // Caminhos para salvar as imagens redimensionadas
        $fileName200 = $uploadDir . '200_' . $newFileName;
        $fileName50 = $uploadDir . '50_' . $newFileName;

        $file50 = '50_' . $newFileName;
        $file200 = '200_' . $newFileName;

        // Redimensionar e salvar as imagens
        if (resizeImage($uploadFilePath, $fileName200, 400)) {
           "Imagem redimensionada para 200 pixels de largura.<br>";
        } else {
           "Erro ao redimensionar para 200 pixels.<br>";
        }

        if (resizeImage($uploadFilePath, $fileName50, 100)) {
           "Imagem redimensionada para 50 pixels de largura.<br>";
        } else {
           "Erro ao redimensionar para 50 pixels.<br>";
        }

        if (!empty($_COOKIE['adminstart'])) {
          $queryUpdate = $con->prepare("UPDATE new_sistema_usuario SET imagem=:imagem,imagem50=:imagem50,imagem200=:imagem200,horaultima=:horah,dataultima=:datah WHERE codigousuario  = :id");
          $queryUpdate->bindParam(":imagem", $newFileName);
          $queryUpdate->bindParam(":imagem50", $file50);
          $queryUpdate->bindParam(":imagem200", $file200);
          $queryUpdate->bindParam(":horah", $hora);
          $queryUpdate->bindParam(":datah", $data);
          $queryUpdate->bindParam(":id", $id);
          $queryUpdate->execute();
        } else {
          if (!empty($_COOKIE['startusuario'])) {
            $queryUpdate = $con->prepare("UPDATE new_sistema_cadastro SET imagemsc=:imagem,imagem50=:imagem50,imagem200=:imagem200,horaedita=:horah,dataedita=:datah WHERE codigocadastro = :id");
            $queryUpdate->bindParam(":imagem", $newFileName);
            $queryUpdate->bindParam(":imagem50", $file50);
            $queryUpdate->bindParam(":imagem200", $file200);
            $queryUpdate->bindParam(":horah", $hora);
            $queryUpdate->bindParam(":datah", $data);
            $queryUpdate->bindParam(":id", $id);
            $queryUpdate->execute();
          }
        }
      } else {
         "Erro ao mover o arquivo para o diretório de destino.<br>";
      }
    } else {
       "Formato de arquivo não suportado. Apenas JPG e PNG são permitidos.<br>";
    }
  } else {
     "Erro no upload do arquivo.<br>";
  }
}
?>
