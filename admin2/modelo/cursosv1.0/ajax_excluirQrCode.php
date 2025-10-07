<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

try {
    $chave = $_POST['chave'] ?? '';
    $campo = $_POST['campo'] ?? '';

    $permitidos = ['imgqrcodecurso', 'imgqrcodeanual', 'imgqrcodevitalicio'];
    if (!in_array($campo, $permitidos)) {
        throw new Exception('Campo inválido.');
    }

    $sql = "SELECT $campo FROM new_sistema_cursos_turmas WHERE chave = :chave";
    $stmt = config::connect()->prepare($sql);
    $stmt->bindParam(':chave', $chave);
    $stmt->execute();
    $imagem = $stmt->fetchColumn();

    if ($imagem) {
        $path = APP_ROOT . "/fotos/qrcodes/$imagem";
        if (file_exists($path)) unlink($path);
    }

    $stmt = config::connect()->prepare("UPDATE new_sistema_cursos_turmas SET $campo = '' WHERE chave = :chave");
    $stmt->bindParam(':chave', $chave);
    $stmt->execute();

    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}
