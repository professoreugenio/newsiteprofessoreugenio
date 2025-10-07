<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['chave']) || empty($_FILES)) {
        throw new Exception('Dados incompletos.');
    }

    $chave = $_POST['chave'];
    $campo = $_POST['campo'];

    $permitidos = ['imgqrcodecurso', 'imgqrcodeanual', 'imgqrcodevitalicio'];
    if (!in_array($campo, $permitidos)) {
        throw new Exception('Campo inválido.');
    }

    $file = $_FILES['arquivo'];
    if ($file['error'] !== 0) {
        throw new Exception('Erro no envio da imagem.');
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $novoNome = uniqid($campo . '_') . '.' . $ext;

    $dir = APP_ROOT . "/fotos/qrcodes/";
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    if (!move_uploaded_file($file['tmp_name'], $dir . $novoNome)) {
        throw new Exception('Falha ao salvar imagem.');
    }

    $stmt = config::connect()->prepare("UPDATE new_sistema_cursos_turmas SET $campo = :nome WHERE chave = :chave");
    $stmt->bindParam(':nome', $novoNome);
    $stmt->bindParam(':chave', $chave);
    $stmt->execute();

    echo json_encode(['status' => 'ok', 'nome' => $novoNome]);
} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}
