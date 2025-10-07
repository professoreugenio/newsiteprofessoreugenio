<?php

/**
 * usuariosv1.0/ajax_uploadFotoUsuario.php
 * Upload de foto de usuário + geração de versões 200x200 (imagem200) e 50x50 (imagem50)
 * Retorno: JSON
 */

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

try {
    // ===== Cabeçalho padrão =====
    define('BASEPATH', true);
    define('APP_ROOT', dirname(__DIR__, 3));
    require_once APP_ROOT . '/conexao/class.conexao.php';
    require_once APP_ROOT . '/autenticacao.php';

    // ===== Configurações =====
    $MAX_MB           = 5;                                  // limite de 5MB
    $MAX_BYTES        = $MAX_MB * 1024 * 1024;
    $FIELD_NAME       = 'arquivo';                          // nome do campo <input type="file" name="arquivo">
    $SUBDIR_FOTOS     = '/fotos/usuarios';                  // base pública
    $allowExt         = ['jpg', 'jpeg', 'png', 'webp'];        // extensões aceitas
    $allowMime        = ['image/jpeg', 'image/png', 'image/webp'];

    // $raizSite opcional (se existir no seu projeto), para compor URL absoluta
    $raizSite = $raizSite ?? (isset($_SERVER['HTTP_HOST']) ? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] : '');

    // ===== Helpers =====
    function jsonOut(array $arr, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($arr, JSON_UNESCAPED_UNICODE);
        exit;
    }

    function ensureDir(string $path): bool
    {
        if (is_dir($path)) return true;
        return @mkdir($path, 0775, true);
    }

    function safeBase(string $s): string
    {
        $s = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', $s);
        return trim($s, '_');
    }

    function imageResourceFromFile(string $filePath, string $mime)
    {
        if ($mime === 'image/jpeg') return imagecreatefromjpeg($filePath);
        if ($mime === 'image/png')  return imagecreatefrompng($filePath);
        if ($mime === 'image/webp') return imagecreatefromwebp($filePath);
        return false;
    }

    function saveImageResource($im, string $destPath, string $mime): bool
    {
        // cria diretório se necessário
        $dir = dirname($destPath);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) return false;

        if ($mime === 'image/jpeg') {
            return imagejpeg($im, $destPath, 90);
        } elseif ($mime === 'image/png') {
            // nível 6 equilibrado (0-9), preservando transparência
            imagesavealpha($im, true);
            return imagepng($im, $destPath, 6);
        } elseif ($mime === 'image/webp') {
            return imagewebp($im, $destPath, 90);
        }
        return false;
    }

    function resizeSquare($srcIm, int $size, string $mime)
    {
        $w = imagesx($srcIm);
        $h = imagesy($srcIm);

        // crop para quadrado central
        $side = min($w, $h);
        $srcX = (int)(($w - $side) / 2);
        $srcY = (int)(($h - $side) / 2);

        $dst = imagecreatetruecolor($size, $size);

        // transparência (PNG/WEBP)
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $size, $size, $transparent);
        }

        imagecopyresampled($dst, $srcIm, 0, 0, $srcX, $srcY, $size, $size, $side, $side);
        return $dst;
    }

    // ===== Entrada =====
    $idUsuario = isset($_POST['idUsuario']) ? (int)$_POST['idUsuario'] : 0;
    $pastasc   = isset($_POST['pastasc']) ? trim((string)$_POST['pastasc']) : '';
    if ($pastasc === '') {
        jsonOut(['ok' => false, 'msg' => 'Parâmetro "pastasc" é obrigatório.']);
    }

    if (!isset($_FILES[$FIELD_NAME]) || !is_uploaded_file($_FILES[$FIELD_NAME]['tmp_name'])) {
        jsonOut(['ok' => false, 'msg' => 'Nenhum arquivo enviado.']);
    }

    $file = $_FILES[$FIELD_NAME];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        jsonOut(['ok' => false, 'msg' => 'Erro no upload: código ' . $file['error']]);
    }
    if ($file['size'] <= 0 || $file['size'] > $MAX_BYTES) {
        jsonOut(['ok' => false, 'msg' => "Arquivo inválido ou maior que {$MAX_MB}MB."]);
    }

    // Validação de imagem
    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        jsonOut(['ok' => false, 'msg' => 'Arquivo não é uma imagem válida.']);
    }
    $mime = $info['mime'] ?? '';
    if (!in_array($mime, $allowMime, true)) {
        jsonOut(['ok' => false, 'msg' => 'Formato de imagem não suportado. Use JPG, PNG ou WEBP.']);
    }

    // Extensão a partir do mime
    $extMap = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];
    $ext = $extMap[$mime] ?? 'jpg';

    // Garante diretório de destino
    $publicBaseDir = rtrim($_SERVER['DOCUMENT_ROOT'] ?? APP_ROOT, '/') . $SUBDIR_FOTOS . '/' . safeBase($pastasc);
    if (!ensureDir($publicBaseDir)) {
        jsonOut(['ok' => false, 'msg' => 'Falha ao criar diretório de destino.']);
    }

    // Gera nome-base
    $uid   = $idUsuario > 0 ? (string)$idUsuario : 'u';
    $stamp = date('Ymd_His');
    $base  = "user_{$uid}_{$stamp}";

    // Caminhos destino
    $dest200 = $publicBaseDir . '/' . $base . '_200.' . $ext;
    $dest50  = $publicBaseDir . '/' . $base . '_50.' . $ext;

    // Carrega imagem de origem
    $srcIm = imageResourceFromFile($file['tmp_name'], $mime);
    if ($srcIm === false) {
        jsonOut(['ok' => false, 'msg' => 'Falha ao processar a imagem.']);
    }

    // Redimensiona (quadrado)
    $im200 = resizeSquare($srcIm, 200, $mime);
    $im50  = resizeSquare($srcIm, 50,  $mime);

    // Salva arquivos
    if (!saveImageResource($im200, $dest200, $mime)) {
        jsonOut(['ok' => false, 'msg' => 'Falha ao salvar a imagem 200x200.']);
    }
    if (!saveImageResource($im50, $dest50, $mime)) {
        // não interrompe totalmente; tenta remover a 200 e reporta erro
        @unlink($dest200);
        jsonOut(['ok' => false, 'msg' => 'Falha ao salvar a imagem 50x50.']);
    }

    // Libera recursos
    imagedestroy($srcIm);
    imagedestroy($im200);
    imagedestroy($im50);

    // Monta URL pública (absoluta se $raizSite existir)
    $rel200 = $SUBDIR_FOTOS . '/' . safeBase($pastasc) . '/' . basename($dest200);
    $url200 = ($raizSite ? rtrim($raizSite, '/') : '') . $rel200;

    // Opcional: atualizar no banco (imagem200/imagem50) — descomentando este bloco
   
    if ($idUsuario > 0) {
        $con = config::connect();
        $sql = "UPDATE new_sistema_cadastro SET imagem200 = :img200, imagem50 = :img50, pastasc = :pasta WHERE codigocadastro = :id LIMIT 1";
        $stmt = $con->prepare($sql);
        $stmt->execute([
            ':img200' => basename($dest200),
            ':img50'  => basename($dest50),
            ':pasta'  => $pastasc,
            ':id'     => $idUsuario,
        ]);
    }
    

    jsonOut([
        'ok'           => true,
        'msg'          => 'Upload concluído.',
        'nomeArquivo'  => basename($dest200),  // para o campo imagem200
        'nomeArquivo50' => basename($dest50),   // se quiser armazenar imagem50 também
        'urlPublica'   => $url200
    ]);
} catch (Throwable $e) {
    jsonOut(['ok' => false, 'msg' => 'Erro interno no upload.', 'err' => $e->getMessage()], 500);
}
