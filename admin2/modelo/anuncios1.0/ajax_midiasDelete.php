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

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID inválido']);
    exit;
}

try {
    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Busca a mídia para obter o caminho da imagem (se houver)
    $sel = $con->prepare("
        SELECT codigomidiasanuncio, imagemAM
        FROM a_site_anuncios_midias
        WHERE codigomidiasanuncio = :id
        LIMIT 1
    ");
    $sel->bindValue(':id', $id, PDO::PARAM_INT);
    $sel->execute();

    if ($sel->rowCount() === 0) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Mídia não encontrada']);
        exit;
    }

    $midia = $sel->fetch(PDO::FETCH_ASSOC);
    $imagem = trim($midia['imagemAM'] ?? '');

    // Exclui o registro
    $del = $con->prepare("
        DELETE FROM a_site_anuncios_midias
        WHERE codigomidiasanuncio = :id
        LIMIT 1
    ");
    $del->bindValue(':id', $id, PDO::PARAM_INT);
    $del->execute();

    // Remove o arquivo de imagem do disco, se existir e estiver no path esperado
    if ($imagem !== '' && str_starts_with($imagem, '/fotos/anuncios/')) {
        $fullPath = APP_ROOT . $imagem;
        if (is_file($fullPath)) {
            @unlink($fullPath); // silencioso – se falhar, não bloqueia a exclusão
        }
    }

    echo json_encode(['status' => 'ok', 'mensagem' => 'Mídia excluída com sucesso!']);
} catch (Throwable $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao excluir: ' . $e->getMessage()]);
}
