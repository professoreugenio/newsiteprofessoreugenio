<?php
// AJAX: inserir novo cliente de anúncios
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

    $nome       = trim($_POST['nomeclienteAC'] ?? '');
    $categoria  = trim($_POST['categoriaAC'] ?? '');
    $celular    = trim($_POST['celularAC'] ?? '');
    $whatsapp   = trim($_POST['whatsappAC'] ?? '');
    $linksite   = trim($_POST['linksiteAC'] ?? '');

    if ($nome === '' || $categoria === '') {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Preencha nome e categoria.']);
        exit;
    }

    // Higieniza números (mantém apenas dígitos)
    $celular  = preg_replace('/\D+/', '', $celular);
    $whatsapp = preg_replace('/\D+/', '', $whatsapp);

    // Validação leve de URL (opcional)
    if ($linksite !== '' && !filter_var($linksite, FILTER_VALIDATE_URL)) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Informe um link válido (URL).']);
        exit;
    }

    $data = date('Y-m-d');
    $hora = date('H:i:s');

    $sql = "INSERT INTO a_site_anuncios_clientes 
                (nomeclienteAC, idcategoriaAC, celularAC, whatsappAC, linksiteAC, dataAC, horaAC)
            VALUES 
                (:nome, :categoria, :celular, :whatsapp, :link, :data, :hora)";

    $stmt = $con->prepare($sql);
    $stmt->bindValue(':nome',      $nome);
    $stmt->bindValue(':categoria', $categoria, PDO::PARAM_INT);
    $stmt->bindValue(':celular',   $celular);
    $stmt->bindValue(':whatsapp',  $whatsapp);
    $stmt->bindValue(':link',      $linksite);
    $stmt->bindValue(':data',      $data);
    $stmt->bindValue(':hora',      $hora);
    $stmt->execute();

    $id = $con->lastInsertId();

    echo json_encode([
        'status'   => 'ok',
        'mensagem' => 'Cliente cadastrado com sucesso!',
        'id'       => $id
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'status'   => 'erro',
        'mensagem' => 'Erro ao salvar: ' . $e->getMessage()
    ]);
}
