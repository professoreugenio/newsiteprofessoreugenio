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

    $id         = (int)($_POST['id'] ?? 0);
    $idcampanha = (int)($_POST['idcampanhaAM'] ?? 0);
    $idcliente  = (int)($_POST['idclienteAM'] ?? 0);
    $idcat      = trim($_POST['idcategroiaAM'] ?? '');
    $link       = trim($_POST['linkAM'] ?? '');
    $youtube    = trim($_POST['youtubeAM'] ?? '');
    $chaveyt    = trim($_POST['chaveyoutubeAM'] ?? '');
    $imgAtual   = trim($_POST['imagemAtual'] ?? '');
    $removerImg = isset($_POST['removerImagem']) ? 1 : 0;

    if ($id <= 0 || $idcampanha <= 0 || $idcliente <= 0) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Dados essenciais inválidos.']);
        exit;
    }

    if ($link !== '' && !filter_var($link, FILTER_VALIDATE_URL)) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Informe um link válido.']);
        exit;
    }

    if ($youtube !== '' && $chaveyt === '') {
        if (preg_match('/(youtu\.be\/|v=|\/embed\/)([A-Za-z0-9_\-]{5,})/i', $youtube, $m)) {
            $chaveyt = end($m);
        }
    }

    // Upload da nova imagem, se houver
    $novoCaminhoPublico = null;
    if (!empty($_FILES['imagemAM']) && is_uploaded_file($_FILES['imagemAM']['tmp_name'])) {
        $file = $_FILES['imagemAM'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Falha no upload da imagem.']);
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $ext = '';
        if (in_array($mime, ['image/jpeg', 'image/jpg'])) $ext = '.jpg';
        elseif ($mime === 'image/png') $ext = '.png';
        elseif ($mime === 'image/webp') $ext = '.webp';
        else {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Formato de imagem não suportado.']);
            exit;
        }

        $dir = APP_ROOT . '/fotos/anuncios/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $nome = 'mid_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . $ext;
        $dest = $dir . $nome;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Não foi possível salvar a imagem.']);
            exit;
        }

        $novoCaminhoPublico = '/fotos/anuncios/' . $nome;
    }

    // Remover imagem atual do disco se solicitado ou se substituiu
    $apagarAtual = false;
    if ($removerImg === 1 && $imgAtual !== '') $apagarAtual = true;
    if ($novoCaminhoPublico !== null && $imgAtual !== '') $apagarAtual = true;

    if ($apagarAtual) {
        // Só remove se estiver dentro do path esperado
        if (str_starts_with($imgAtual, '/fotos/anuncios/')) {
            $full = APP_ROOT . $imgAtual;
            if (is_file($full)) {
                @unlink($full);
            }
        }
        $imgAtual = ''; // vai gravar null
    }

    // Monta UPDATE dinâmico para imagem
    $sql = "UPDATE a_site_anuncios_midias SET
                idclienteAM   = :cli,
                idcategroiaAM = :cat,
                idcampanhaAM  = :camp,
                linkAM        = :link,
                youtubeAM     = :yt,
                chaveyoutubeAM= :key";

    if ($novoCaminhoPublico !== null) {
        $sql .= ", imagemAM = :img";
    } elseif ($removerImg === 1) {
        $sql .= ", imagemAM = NULL";
    }

    $sql .= " WHERE codigomidiasanuncio = :id LIMIT 1";

    $st = $con->prepare($sql);
    $st->bindValue(':cli',  $idcliente, PDO::PARAM_INT);
    $st->bindValue(':cat',  $idcat !== '' ? $idcat : null, $idcat !== '' ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $st->bindValue(':camp', $idcampanha, PDO::PARAM_INT);
    $st->bindValue(':link', $link !== '' ? $link : null, $link !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $st->bindValue(':yt',   $youtube !== '' ? $youtube : null, $youtube !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $st->bindValue(':key',  $chaveyt !== '' ? $chaveyt : null, $chaveyt !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    if ($novoCaminhoPublico !== null) {
        $st->bindValue(':img', $novoCaminhoPublico, PDO::PARAM_STR);
    }
    $st->bindValue(':id',   $id, PDO::PARAM_INT);
    $st->execute();

    echo json_encode(['status' => 'ok', 'mensagem' => 'Mídia atualizada com sucesso!']);
} catch (Throwable $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao atualizar: ' . $e->getMessage()]);
}
