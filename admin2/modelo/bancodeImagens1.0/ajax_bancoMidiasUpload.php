<?php

/**
 * bancoimagens1.0/ajax_bancoMidiasUpload.php
 * Upload múltiplo de imagens para uma galeria do Banco de Imagens.
 *
 * Requisitos (POST):
 *  - idgaleria  (CRIPTO)  -> obrigatório (usado para buscar pastaBI com segurança)
 *  - idadmin    (int)     -> obrigatório (quem está enviando)
 *  - imagens[]  (files)   -> obrigatório (múltiplos)
 *
 * Fluxo:
 *  - Verifica método
 *  - Decrypt do idgaleria
 *  - Busca pastaBI real no banco (ignora 'pasta' do POST por segurança)
 *  - Valida cada arquivo (extensão, mime, getimagesize)
 *  - Cria pasta física /fotos/bancoimagens/{pastaBI}/ se não existir
 *  - Renomeia e move os arquivos
 *  - Insere cada mídia em a_site_banco_imagensMidias
 *  - Responde JSON com itens enviados
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');

// Verificação do método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Método inválido.']);
    exit;
}

// Entrada
$idEnc   = trim($_POST['idgaleria'] ?? '');
$idadmin = trim($_POST['idadmin'] ?? '');

// Validações básicas
if ($idEnc === '') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID da galeria não informado.']);
    exit;
}
try {
    $idgaleria = encrypt($idEnc, 'd');
} catch (\Throwable $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Falha ao decodificar ID.']);
    exit;
}
if (!is_numeric($idgaleria) || (int)$idgaleria <= 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID da galeria inválido.']);
    exit;
}
$idgaleria = (int)$idgaleria;

if ($idadmin === '' || !ctype_digit($idadmin)) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Administrador inválido.']);
    exit;
}
$idadmin = (int)$idadmin;

// Verifica arquivos
if (empty($_FILES['imagens']) || !isset($_FILES['imagens']['name']) || count((array)$_FILES['imagens']['name']) === 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Nenhum arquivo enviado.']);
    exit;
}

// Parâmetros de segurança
$allowedExt  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$maxSize     = 25 * 1024 * 1024; // 25MB por arquivo (ajuste se quiser)

// Datas
$dataIM = date('Y-m-d');
$horaIM = date('H:i:s');

try {
    $con = config::connect();
    try {
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\Throwable $e) {
    }

    // Busca a pastaBI REAL da galeria (ignora o que veio no POST)
    $sqlGal = "SELECT pastaBI FROM a_site_banco_imagens WHERE codigobancoimagens = :id LIMIT 1";
    $stGal  = $con->prepare($sqlGal);
    $stGal->bindParam(':id', $idgaleria, PDO::PARAM_INT);
    $stGal->execute();
    if ($stGal->rowCount() === 0) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Galeria não encontrada.']);
        exit;
    }
    $pastaBI = trim((string)($stGal->fetchColumn() ?? ''));
    if ($pastaBI === '') {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Pasta da galeria não definida.']);
        exit;
    }

    // Caminho físico da pasta
    $baseDir = APP_ROOT . '/fotos/bancoimagens';
    $galDir  = $baseDir . '/' . $pastaBI;

    // Cria pastas se não existirem (com segurança)
    if (!is_dir($baseDir)) {
        if (!mkdir($baseDir, 0755, true)) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Falha ao preparar diretório base.']);
            exit;
        }
    }
    if (!is_dir($galDir)) {
        if (!mkdir($galDir, 0755, true)) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Falha ao criar diretório da galeria.']);
            exit;
        }
    }

    // Preparar INSERT
    $sqlIns = "INSERT INTO a_site_banco_imagensMidias
                 (idadminIM, imagemIM, pastaIM, sizeIM, extensaoIM, dataIM, horaIM)
               VALUES
                 (:idadmin, :imagem, :pasta, :size, :ext, :data, :hora)";
    $stIns = $con->prepare($sqlIns);

    $enviados = [];
    $erros    = [];

    // Normalizar arrays de upload
    $names     = (array)$_FILES['imagens']['name'];
    $types     = (array)$_FILES['imagens']['type'];
    $tmp_names = (array)$_FILES['imagens']['tmp_name'];
    $errors    = (array)$_FILES['imagens']['error'];
    $sizes     = (array)$_FILES['imagens']['size'];

    // Iterar arquivos
    for ($i = 0; $i < count($names); $i++) {
        $origName = (string)$names[$i];
        $tmp      = (string)$tmp_names[$i];
        $err      = (int)$errors[$i];
        $size     = (int)$sizes[$i];
        $mime     = (string)$types[$i];

        if ($err !== UPLOAD_ERR_OK) {
            $erros[] = "Falha ao enviar '{$origName}' (código {$err}).";
            continue;
        }
        if (!is_uploaded_file($tmp)) {
            $erros[] = "Arquivo inválido '{$origName}'.";
            continue;
        }
        if ($size <= 0 || $size > $maxSize) {
            $erros[] = "Arquivo '{$origName}' com tamanho inválido (máx. " . ($maxSize / 1024 / 1024) . "MB).";
            continue;
        }

        // Extensão
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
            $erros[] = "Extensão não permitida em '{$origName}'.";
            continue;
        }

        // MIME + verificação real da imagem
        if (!in_array($mime, $allowedMime, true)) {
            // Alguns browsers podem não setar corretamente; ainda assim validaremos com getimagesize
            // $erros[] = "MIME type inválido em '{$origName}'.";
            // continue;
        }
        $imgInfo = @getimagesize($tmp);
        if ($imgInfo === false) {
            $erros[] = "Arquivo '{$origName}' não é uma imagem válida.";
            continue;
        }

        // Gera nome seguro (timestamp + rand)
        $baseName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($origName, PATHINFO_FILENAME));
        $safeName = $baseName !== '' ? $baseName : 'img';
        $novoNome = date('Ymd_His') . '_' . mt_rand(1000, 9999) . '_' . $safeName . '.' . $ext;

        // Caminho final
        $dest = $galDir . '/' . $novoNome;

        // Move o arquivo
        if (!move_uploaded_file($tmp, $dest)) {
            $erros[] = "Não foi possível salvar '{$origName}'.";
            continue;
        }

        // Ajusta permissão básica
        @chmod($dest, 0644);

        // Tamanho final (por segurança, recalcule)
        $finalSize = @filesize($dest);
        if ($finalSize === false) {
            $finalSize = $size;
        }

        // Inserir no banco
        $stIns->bindParam(':idadmin', $idadmin,    PDO::PARAM_INT);
        $stIns->bindParam(':imagem',  $novoNome,   PDO::PARAM_STR);
        $stIns->bindParam(':pasta',   $pastaBI,    PDO::PARAM_STR);
        $stIns->bindParam(':size',    $finalSize,  PDO::PARAM_INT);
        $stIns->bindParam(':ext',     $ext,        PDO::PARAM_STR);
        $stIns->bindParam(':data',    $dataIM,     PDO::PARAM_STR);
        $stIns->bindParam(':hora',    $horaIM,     PDO::PARAM_STR);
        $stIns->execute();

        // Monta URL pública (ajuste se sua CDN/caminho público for diferente)
        $urlPublica = '/fotos/bancoimagens/' . $pastaBI . '/' . $novoNome;

        $enviados[] = [
            'arquivo' => $novoNome,
            'url'     => $urlPublica,
            'size'    => $finalSize,
            'ext'     => $ext,
        ];
    }

    if (count($enviados) === 0) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Nenhuma imagem válida foi enviada.', 'erros' => $erros]);
        exit;
    }

    echo json_encode([
        'status'   => 'ok',
        'mensagem' => 'Upload concluído.',
        'pasta'    => $pastaBI,
        'qtd'      => count($enviados),
        'itens'    => $enviados,
        'avisos'   => $erros, // pode conter arquivos ignorados
    ]);
    exit;
} catch (\PDOException $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro no banco de dados: ' . $e->getMessage()]);
    exit;
} catch (\Throwable $t) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro inesperado no upload.']);
    exit;
}
