<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

try {
    $pdo = config::connect();

    $idUsuario = isset($_POST['idUsuario']) ? (int)$_POST['idUsuario'] : 0;
    $pastasc   = trim($_POST['pastasc'] ?? '');
    if ($idUsuario <= 0) {
        echo json_encode(['status' => 'erro', 'msg' => 'ID inválido.']);
        exit;
    }
    if (!$pastasc) {
        echo json_encode(['status' => 'erro', 'msg' => 'Pasta (pastasc) não informada.']);
        exit;
    }
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'erro', 'msg' => 'Arquivo não recebido.']);
        exit;
    }

    $file = $_FILES['foto'];
    if ($file['size'] > 5 * 1024 * 1024) { // 5 MB
        echo json_encode(['status' => 'erro', 'msg' => 'Arquivo excede 5 MB.']);
        exit;
    }

    // Validação MIME real
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $extMap = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];
    if (!isset($extMap[$mime])) {
        echo json_encode(['status' => 'erro', 'msg' => 'Formato inválido. Use JPG, PNG ou WebP.']);
        exit;
    }
    $ext = $extMap[$mime];

    // Monta caminhos
    $baseDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/fotos/usuarios';
    $dirUser = $baseDir . '/' . $pastasc;
    if (!is_dir($dirUser)) {
        if (!mkdir($dirUser, 0775, true)) {
            echo json_encode(['status' => 'erro', 'msg' => 'Falha ao criar diretório do usuário.']);
            exit;
        }
    }

    // Nome único
    $nomeArquivo = 'img_' . $idUsuario . '_' . date('Ymd_His') . '.' . $ext;
    $destino = $dirUser . '/' . $nomeArquivo;

    if (!move_uploaded_file($file['tmp_name'], $destino)) {
        echo json_encode(['status' => 'erro', 'msg' => 'Falha ao salvar arquivo no servidor.']);
        exit;
    }

    // Atualiza no banco
    $stmt = $pdo->prepare("UPDATE new_sistema_cadastro SET imagem200 = :img WHERE codigocadastro = :id LIMIT 1");
    $stmt->execute([':img' => $nomeArquivo, ':id' => $idUsuario]);

    // URL pública (com cache-buster)
    $urlPublica = "https://professoreugenio.com/fotos/usuarios/{$pastasc}/{$nomeArquivo}?v=" . time();

    echo json_encode([
        'status' => 'ok',
        'msg' => 'Foto enviada e atualizada.',
        'nomeArquivo' => $nomeArquivo,
        'urlPublica'  => $urlPublica
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'msg' => 'Erro: ' . $e->getMessage()]);
}
