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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Método inválido']);
    exit;
}

$con = config::connect();
try {
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\Throwable $e) {
}

function soLetrasENumeros($s)
{
    return preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$s);
}

try {
    $codigousuario = (int)($_POST['codigousuario'] ?? 0);
    if ($codigousuario <= 0) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário inválido.']);
        exit;
    }

    // (Opcional) Verificação CSRF
    if (isset($_SESSION['csrf_token']) && !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Falha de segurança (CSRF).']);
        exit;
    }

    // Carrega dados atuais (precisamos da pasta do usuário e fotos)
    $stmt = $con->prepare("SELECT pastasu, imagem, imagem200 FROM new_sistema_usuario WHERE codigousuario = :id LIMIT 1");
    $stmt->bindParam(':id', $codigousuario, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário não encontrado.']);
        exit;
    }
    $atual = $stmt->fetch(PDO::FETCH_ASSOC);
    $pastaUser = $atual['pastasu'] ?: 'default';

    // Campos editáveis
    $nome   = trim($_POST['nome'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $celular = trim($_POST['celular'] ?? '');
    $dataaniversario = trim($_POST['dataaniversario'] ?? '');
    $nivel  = (int)($_POST['nivel'] ?? 0);
    $liberado = (int)($_POST['liberado'] ?? 1);

    if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Preencha nome e e-mail válidos.']);
        exit;
    }
    if (!in_array($nivel, [1, 2, 3, 4], true)) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Nível inválido.']);
        exit;
    }

    // Senha (opcional)
    $senha = trim($_POST['senha'] ?? '');
    $hashSenha = null;
    if ($senha !== '') {
        if (strlen($senha) < 6) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'A senha deve ter no mínimo 6 caracteres.']);
            exit;
        }

        $senhaenc = encrypt($email . "&" . $senha, $action = 'e');
        $chaveenc = strtoupper(encrypt($email . "&" . $senha . "&professoreugenio", $action = 'e'));
    }

    // Foto (opcional) + remover_foto
    $removerFoto = (int)($_POST['remover_foto'] ?? 0);
    $novaImagem = null;     // nome arquivo original salvo
    $novaImagem200 = null;  // versão redimensionada 200px

    // Cria pasta do usuário, se necessário
    $baseFotos = APP_ROOT . '/fotos/usuarios';
    $dirUser = $baseFotos . '/' . soLetrasENumeros($pastaUser);
    if (!is_dir($dirUser)) {
        @mkdir($dirUser, 0755, true);
    }

    if ($removerFoto === 1) {
        // Reseta para padrão
        $novaImagem = 'usuario.jpg';
        $novaImagem200 = 'usuario.jpg';
    } elseif (isset($_FILES['imagemPerfil']) && $_FILES['imagemPerfil']['error'] === UPLOAD_ERR_OK) {
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

        // Gera nome seguro
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $nomeBase = 'user_' . $codigousuario . '_' . time();
        $nomeArq = $nomeBase . '.' . $ext;
        $nomeArq200 = $nomeBase . '_200.' . $ext;

        $dest = $dirUser . '/' . $nomeArq;
        if (!move_uploaded_file($f['tmp_name'], $dest)) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Falha ao salvar a imagem.']);
            exit;
        }

        // Redimensionar para ~200px (lado maior)
        $dest200 = $dirUser . '/' . $nomeArq200;
        if (!redimensiona200($dest, $dest200)) {
            // fallback: se falhar, usa a original em ambos
            $nomeArq200 = $nomeArq;
        }

        $novaImagem = $nomeArq;
        $novaImagem200 = $nomeArq200;
    }

    // Monta SQL dinâmico
    $campos = [
        'nome' => $nome,
        'email' => $email,
        'celular' => $celular,
        'dataaniversario' => ($dataaniversario ?: null),
        'nivel' => $nivel,
        'liberado' => $liberado,
        'chave' => $chaveenc,
        'senha' => $senhaenc,
        'data' => $data,
        'hora' => $hora
    ];

    if ($novaImagem !== null) {
        $campos['imagem'] = $novaImagem;
        $campos['imagem200'] = $novaImagem200;
    }

    $sets = [];
    foreach ($campos as $k => $v) {
        $sets[] = "$k = :$k";
    }
    $sql = "UPDATE new_sistema_usuario SET " . implode(', ', $sets) . " WHERE codigousuario = :id LIMIT 1";
    $upd = $con->prepare($sql);
    foreach ($campos as $k => $v) {
        $param = is_null($v) ? PDO::PARAM_NULL : PDO::PARAM_STR;
        $upd->bindValue(":$k", $v, $param);
    }
    $upd->bindValue(':id', $codigousuario, PDO::PARAM_INT);
    $upd->execute();

    echo json_encode(['status' => 'ok', 'mensagem' => 'Perfil atualizado com sucesso.']);
} catch (Throwable $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao atualizar: ' . $e->getMessage()]);
    exit;
}

/**
 * Redimensiona a imagem para que o MAIOR lado tenha 200px, mantendo proporção.
 * Retorna true em sucesso.
 */
function redimensiona200(string $origem, string $destino): bool
{
    try {
        $info = getimagesize($origem);
        if (!$info) return false;
        [$w, $h] = $info;
        $tipo = $info['mime'];

        switch ($tipo) {
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

        if ($w === 0 || $h === 0) return false;

        $ladoMaior = max($w, $h);
        $escala = 200 / $ladoMaior;
        $nw = max(1, (int)round($w * $escala));
        $nh = max(1, (int)round($h * $escala));

        $dst = imagecreatetruecolor($nw, $nh);

        // Transparência para PNG
        if ($tipo === 'image/png') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $ok = false;
        if ($tipo === 'image/png') {
            $ok = imagepng($dst, $destino);
        } else {
            $ok = imagejpeg($dst, $destino, 90);
        }

        imagedestroy($src);
        imagedestroy($dst);
        return (bool)$ok;
    } catch (Throwable $e) {
        return false;
    }
}
