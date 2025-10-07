<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');



try {
    // ... seu código aqui ...

    echo json_encode(['status' => 'ok', 'mensagem' => 'Dados comerciais atualizados!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}

ob_end_clean(); // ← limpa qualquer saída antes do JSON

try {
    if (!isset($_POST['chave'])) {
        throw new Exception('Turma não informada.');
    }

    $chave = $_POST['chave'];

    $campos = [
        'valorvenda',
        'chavepixvalorvenda',
        'valoranual',
        'horasaulast',
        'chavepix',
        'valorvendavitalicia',
        'chavepixvitalicia',
        'linkpagseguro',
        'linkpagsegurovitalicia',
        'linkmercadopago',
        'linkmercadopagovitalicio',
        'valorhoraaula'
    ];

    $campos_monetarios = ['valorvenda', 'valoranual', 'valorvendavitalicia', 'valorhoraaula'];

    $dados = [];
    foreach ($campos as $campo) {
        $valor = trim($_POST[$campo] ?? '');
        if (in_array($campo, $campos_monetarios)) {
            $valor = str_replace(',', '.', $valor); // vírgula para ponto
        }
        $dados[$campo] = $valor;
    }

    // Upload de imagens (se houver)
    $dir = APP_ROOT . "/fotos/qrcodes/";
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $arquivos = [
        'imgqrcodecurso' => 'imgqrcodecurso',
        'imgqrcodeanual' => 'imgqrcodeanual',
        'imgqrcodevitalicio' => 'imgqrcodevitalicio'
    ];

    foreach ($arquivos as $input => $campoBanco) {
        if (isset($_FILES[$input]) && $_FILES[$input]['error'] === 0) {
            $ext = pathinfo($_FILES[$input]['name'], PATHINFO_EXTENSION);
            $novoNome = uniqid($input . '_') . '.' . $ext;
            move_uploaded_file($_FILES[$input]['tmp_name'], $dir . $novoNome);
            $dados[$campoBanco] = $novoNome;
        }
    }

    // Monta SQL dinâmico
    $sql = "UPDATE new_sistema_cursos_turmas SET ";
    $set = [];
    foreach ($dados as $campo => $valor) {
        $set[] = "$campo = :$campo";
    }
    $sql .= implode(', ', $set) . " WHERE chave = :chave";

    $stmt = $con->prepare($sql);
    foreach ($dados as $campo => $valor) {
        $stmt->bindValue(":$campo", $valor);
    }
    $stmt->bindValue(":chave", $chave);
    $stmt->execute();

    echo json_encode(['status' => 'ok', 'mensagem' => 'Dados comerciais atualizados!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}
