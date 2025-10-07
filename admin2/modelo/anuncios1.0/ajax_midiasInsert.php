<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Método inválido']);
    exit;
}

try {
    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $idcampanha = (int)($_POST['idcampanhaAM'] ?? 0);
    $idcliente  = (int)($_POST['idclienteAM'] ?? 0);
    $idcat      = trim($_POST['idcategroiaAM'] ?? ''); // campo conforme tabela
    $link       = trim($_POST['linkAM'] ?? '');
    $youtube    = trim($_POST['youtubeAM'] ?? '');
    $chaveyt    = trim($_POST['chaveyoutubeAM'] ?? '');

    if ($idcampanha <= 0 || $idcliente <= 0) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Campanha/Cliente inválidos.']);
        exit;
    }

    // Validação leve do link
    if ($link !== '' && !filter_var($link, FILTER_VALIDATE_URL)) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Informe um link válido.']);
        exit;
    }

    // Se veio URL do YouTube e não veio chave, tenta extrair simples
    if ($youtube !== '' && $chaveyt === '') {
        // fallback simples (caso frontend não tenha preenchido)
        if (preg_match('/(youtu\.be\/|v=|\/embed\/)([A-Za-z0-9_\-]{5,})/i', $youtube, $m)) {
            $chaveyt = end($m);
        }
    }

    // Upload da imagem (opcional)
    $nomeImagemFinal = '';
    if (!empty($_FILES['imagemAM']) && is_uploaded_file($_FILES['imagemAM']['tmp_name'])) {
        $file = $_FILES['imagemAM'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Falha no upload da imagem.']);
            exit;
        }

        // Valida extensão/mime simples
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $ext = '';
        if (in_array($mime, ['image/jpeg', 'image/jpg'])) $ext = '.jpg';
        elseif ($mime === 'image/png') $ext = '.png';
        elseif ($mime === 'image/webp') $ext = '.webp';
        else {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Formato de imagem não suportado (use JPG, PNG ou WebP).']);
            exit;
        }

        // Caminho destino — ajuste se desejar
        $dir = APP_ROOT . '/fotos/anuncios/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $nomeImagemFinal = 'mid_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . $ext;
        $destino = $dir . $nomeImagemFinal;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Não foi possível salvar a imagem.']);
            exit;
        }

        // Caminho público (ajuste a base conforme seu projeto)
        $publicBase = '/fotos/anuncios/';
        $nomeImagemFinal = $publicBase . $nomeImagemFinal;
    }

    $data = date('Y-m-d');
    $hora = date('H:i:s');

    $sql = "INSERT INTO a_site_anuncios_midias
                (idclienteAM, idcategroiaAM, idcampanhaAM, linkAM, imagemAM, youtubeAM, chaveyoutubeAM, dataAM, horaAM)
            VALUES
                (:cli, :cat, :camp, :link, :img, :yt, :key, :data, :hora)";

    $st = $con->prepare($sql);
    $st->bindValue(':cli',  $idcliente, PDO::PARAM_INT);
    $st->bindValue(':cat',  $idcat !== '' ? $idcat : null, $idcat !== '' ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $st->bindValue(':camp', $idcampanha, PDO::PARAM_INT);
    $st->bindValue(':link', $link !== '' ? $link : null, $link !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $st->bindValue(':img',  $nomeImagemFinal !== '' ? $nomeImagemFinal : null, $nomeImagemFinal !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $st->bindValue(':yt',   $youtube !== '' ? $youtube : null, $youtube !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $st->bindValue(':key',  $chaveyt !== '' ? $chaveyt : null, $chaveyt !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $st->bindValue(':data', $data);
    $st->bindValue(':hora', $hora);
    $st->execute();

    echo json_encode(['status' => 'ok', 'mensagem' => 'Mídia inserida com sucesso!']);
} catch (Throwable $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao salvar: ' . $e->getMessage()]);
}
