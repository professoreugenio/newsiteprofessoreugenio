<?php
// Atualiza cliente de anúncios
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

    $id        = (int)($_POST['id'] ?? 0);
    $nome      = trim($_POST['nomeclienteAC'] ?? '');
    $categoria = trim($_POST['categoriaAC'] ?? '');
    $celular   = trim($_POST['celularAC'] ?? '');
    $whatsapp  = trim($_POST['whatsappAC'] ?? '');
    $linksite  = trim($_POST['linksiteAC'] ?? '');

    if ($id <= 0) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'ID inválido.']);
        exit;
    }
    if ($nome === '' || $categoria === '') {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Preencha {$nome} nome e categoria.']);
        exit;
    }

    // Higieniza números
    $celular  = preg_replace('/\D+/', '', $celular);
    $whatsapp = preg_replace('/\D+/', '', $whatsapp);

    // Validação leve de URL
    if ($linksite !== '' && !filter_var($linksite, FILTER_VALIDATE_URL)) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Informe um link válido (URL).']);
        exit;
    }

    $sql = "UPDATE a_site_anuncios_clientes SET
                nomeclienteAC = :nome,
                idcategoriaAC   = :categoria,
                celularAC     = :celular,
                whatsappAC    = :whatsapp,
                linksiteAC    = :link
            WHERE codigoclienteanuncios = :id
            LIMIT 1";

    $stmt = $con->prepare($sql);
    $stmt->bindValue(':nome',      $nome);
    $stmt->bindValue(':categoria', $categoria, PDO::PARAM_INT);
    $stmt->bindValue(':celular',   $celular);
    $stmt->bindValue(':whatsapp',  $whatsapp);
    $stmt->bindValue(':link',      $linksite);
    $stmt->bindValue(':id',        $id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['status' => 'ok', 'mensagem' => 'Cliente atualizado com sucesso!']);
} catch (Throwable $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao atualizar: ' . $e->getMessage()]);
}
