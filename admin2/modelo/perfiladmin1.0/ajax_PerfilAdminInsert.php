<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

$decadm = encrypt($_COOKIE['adminuserstart'], 'd');
$expadm = explode("&", $decadm);
$niveladm = $expadm[1] ?? '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

// Funções utilitárias
if (!function_exists('temPermissao')) {
    function temPermissao($nivelUsuario, $permitidos = [])
    {
        return in_array($nivelUsuario, $permitidos);
    }
}
function soLetrasENumeros($s)
{
    return preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$s);
}

/** Redimensiona a imagem para lado maior = 200px (mantendo proporção) */
function redimensiona200(string $origem, string $destino): bool
{
    try {
        $info = getimagesize($origem);
        if (!$info) return false;
        [$w, $h] = $info;
        $mime = $info['mime'];
        if ($w === 0 || $h === 0) return false;

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $src = imagecreatefromjpeg($origem);
                break;
            case 'image/png':
                $src = imagecreatefrompng($origem);
                break;
            default:
                return false;
        }

        $ladoMaior = max($w, $h);
        $escala = 200 / $ladoMaior;
        $nw = max(1, (int)round($w * $escala));
        $nh = max(1, (int)round($h * $escala));

        $dst = imagecreatetruecolor($nw, $nh);
        if ($mime === 'image/png') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $ok = ($mime === 'image/png') ? imagepng($dst, $destino) : imagejpeg($dst, $destino, 90);
        imagedestroy($src);
        imagedestroy($dst);
        return (bool)$ok;
    } catch (Throwable $e) {
        return false;
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Método inválido.']);
        exit;
    }

    // Checagem de permissão (apenas Admin pode criar)
    $niveladm = isset($niveladm) ? (int)$niveladm : 0; // vem do autenticacao.php

    if (!temPermissao($niveladm, [1])) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Permissão negada.']);
        exit;
    }

    // CSRF simples
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Falha de segurança (CSRF).']);
        exit;
    }

    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Campos
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $celular = trim($_POST['celular'] ?? '');
    $dataaniversario = trim($_POST['dataaniversario'] ?? '');
    $nivel = (int)($_POST['nivel'] ?? 1);
    $liberado = (int)($_POST['liberado'] ?? 1);
    $senha = trim($_POST['senha'] ?? '');
    $senha2 = trim($_POST['senha2'] ?? '');
    $removerFoto = (int)($_POST['remover_foto'] ?? 0);


    // Validações
    if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Preencha nome e e-mail válidos.']);
        exit;
    }
    if (!in_array($nivel, [1, 2, 3, 4], true)) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Nível inválido.']);
        exit;
    }
    if (strlen($senha) < 6 || $senha !== $senha2) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Senha inválida ou não confere.']);
        exit;
    }

    $senha = encrypt($senha . "&" . $email, $action = 'e');
    // E-mail único
    $chk = $con->prepare("SELECT 1 FROM new_sistema_usuario WHERE email = :e LIMIT 1");
    $chk->bindValue(':e', $email);
    $chk->execute();
    if ($chk->fetchColumn()) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'E-mail já cadastrado.']);
        exit;
    }

    // Preparar dados

    $senhaenc = encrypt($email . "&" . $senha, $action = 'e');
    $chaveenc = strtoupper(encrypt($email . "&" . $senha . "&professoreugenio", $action = 'e'));
    $pastasu = 'u' . date('ymdHis') . '_' . bin2hex(random_bytes(3));
    $imagem = 'usuario.jpg';
    $imagem200 = 'usuario.jpg';
    $size = null;
    $onlinesu = 0;
    $timestampsu = date('Y-m-d H:i:s');

    // Inserção base
    $ins = $con->prepare("
        INSERT INTO new_sistema_usuario
        (nome, email, celular, dataaniversario, senha, chave, pastasu, imagem, imagem200, size, nivel, liberado, onlinesu, timestampsu)
        VALUES
        (:nome, :email, :celular, :dataaniversario, :senha, :chave, :pastasu, :imagem, :imagem200, :size, :nivel, :liberado, :onlinesu, :timestampsu)
    ");
    $ins->bindValue(':nome', $nome);
    $ins->bindValue(':email', $email);
    $ins->bindValue(':celular', $celular);
    $ins->bindValue(':dataaniversario', $dataaniversario ?: null, $dataaniversario ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $ins->bindValue(':senha', $senhaenc);
    $ins->bindValue(':chave', $chaveenc);
    $ins->bindValue(':pastasu', $pastasu);
    $ins->bindValue(':imagem', $imagem);
    $ins->bindValue(':imagem200', $imagem200);
    $ins->bindValue(':size', $size, PDO::PARAM_NULL);
    $ins->bindValue(':nivel', $nivel, PDO::PARAM_INT);
    $ins->bindValue(':liberado', $liberado, PDO::PARAM_INT);
    $ins->bindValue(':onlinesu', $onlinesu, PDO::PARAM_INT);
    $ins->bindValue(':timestampsu', $timestampsu);
    $ins->execute();

    $novoId = (int)$con->lastInsertId();

    // Processar foto se enviada
    $baseFotos = APP_ROOT . '/fotos/usuarios';
    $dirUser = $baseFotos . '/' . soLetrasENumeros($pastasu);
    if (!is_dir($dirUser)) {
        @mkdir($dirUser, 0755, true);
    }

    if ($removerFoto === 0 && isset($_FILES['imagemPerfil']) && $_FILES['imagemPerfil']['error'] === UPLOAD_ERR_OK) {
        $f = $_FILES['imagemPerfil'];
        $mime = mime_content_type($f['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/jpg', 'image/png'], true)) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Formato de imagem inválido. Use JPG ou PNG.']);
            exit;
        }
        if ($f['size'] > 5 * 1024 * 1024) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Imagem acima de 5MB.']);
            exit;
        }

        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $nomeBase = 'user_' . $novoId . '_' . time();
        $nomeArq = $nomeBase . '.' . $ext;
        $nomeArq200 = $nomeBase . '_200.' . $ext;

        $dest = $dirUser . '/' . $nomeArq;
        if (!move_uploaded_file($f['tmp_name'], $dest)) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Falha ao salvar a imagem enviada.']);
            exit;
        }

        // Redimensionar 200px
        $dest200 = $dirUser . '/' . $nomeArq200;
        if (!redimensiona200($dest, $dest200)) {
            $nomeArq200 = $nomeArq;
        }

        $imagem = $nomeArq;
        $imagem200 = $nomeArq200;
        $size = (int)$f['size'];

        // Atualiza imagens
        $upd = $con->prepare("UPDATE new_sistema_usuario SET imagem = :i, imagem200 = :i200, size = :sz WHERE codigousuario = :id LIMIT 1");
        $upd->bindValue(':i', $imagem);
        $upd->bindValue(':i200', $imagem200);
        $upd->bindValue(':sz', $size, PDO::PARAM_INT);
        $upd->bindValue(':id', $novoId, PDO::PARAM_INT);
        $upd->execute();
    }

    // (Opcional) Gerar URL de edição com ID criptografado
    // $idCrypt = encrypt((string)$novoId, $action = 'e');
    // $urlEdicao = "perfiladmin.php?id={$idCrypt}"; // ajuste conforme sua rota

    echo json_encode([
        'status' => 'ok',
        'mensagem' => 'Usuário cadastrado com sucesso.',
        'id' => $novoId
        // ,'url' => $urlEdicao
    ]);
} catch (Throwable $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao cadastrar: ' . $e->getMessage()]);
}
