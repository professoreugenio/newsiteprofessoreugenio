<?php

declare(strict_types=1);

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=UTF-8');
ob_start();
ini_set('display_errors', '0');

$respond = function (array $payload, int $http = 200) {
    http_response_code($http);
    $noise = ob_get_clean();
    if ($noise !== '') {
        $payload['debug'] = trim($noise);
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
};

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $respond(['status' => 'erro', 'mensagem' => 'Método não permitido.'], 405);
    }

    // Conexão
    if (!isset($con) || !$con) {
        if (class_exists('config') && method_exists('config', 'connect')) {
            $con = config::connect();
        }
    }
    if (!$con || !($con instanceof PDO)) {
        $respond(['status' => 'erro', 'mensagem' => 'Conexão indisponível.']);
    }
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // POST helper
    $p = function (string $k, $def = '') {
        return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $def;
    };

    // Chave da turma (identificador)
    $chave = $p('chave', '');
    if ($chave === '') {
        $respond(['status' => 'erro', 'mensagem' => 'Turma não informada (chave ausente).']);
    }

    // Conversor monetário: "000.000,00" -> "000000.00"
    $toMoney = function (?string $v) {
        $v = trim((string)$v);
        if ($v === '') return null;
        // Remove tudo que não for dígito, ponto ou vírgula
        $v = preg_replace('/[^\d\.,]/', '', $v) ?? '';
        // Remove separador de milhar (.)
        $v = str_replace('.', '', $v);
        // Troca vírgula decimal por ponto
        $v = str_replace(',', '.', $v);
        return $v === '' ? null : $v;
    };

    // Captura campos conforme seu form
    $dados = [
        // monetários
        'valorbrutoct'              => $toMoney($p('valorvenda')),
        'valorcartaoct'             => $toMoney($p('valorcartao')),
        'valorcartaoanualct'             => $toMoney($p('valorcartaoanual')),
        'valoravistact'          => $toMoney($p('valoravista')),
        'pixvaloravistact'          => $toMoney($p('chavepixvaloravista')),
        'pixvaloranualavistact'              => $toMoney($p('chavepixvaloranualavista')),
        'valoranualct'              => $toMoney($p('valoranual')),
        'valorhoraaula'           => $toMoney($p('valorhoraaula')),

        // textos/numéricos
        'chavepixvalorvenda'      => $p('chavepixvalorvenda'),
        'horasaulast'             => $p('horasaulast'),
        'chavepix'                => $p('chavepix'),
        'chavepixvitalicia'       => $p('chavepixvitalicia'),
        'linkpagseguro'           => $p('linkpagseguro'),
        'linkpagsegurovitalicia'  => $p('linkpagsegurovitalicia'),
        'linkmercadopago'         => $p('linkmercadopago'),
        'linkmercadopagovitalicio' => $p('linkmercadopagovitalicio'),
    ];

    // Upload de QRCodes (opcional)
    $dir = APP_ROOT . '/fotos/qrcodes/';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $updatedImages = [];

    $handleUpload = function (string $inputName, string $columnName) use (&$dados, &$updatedImages, $dir, $chave) {
        if (!isset($_FILES[$inputName]) || !is_array($_FILES[$inputName])) return;
        if ((int)($_FILES[$inputName]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return;

        $tmp  = $_FILES[$inputName]['tmp_name'];
        $name = (string)($_FILES[$inputName]['name'] ?? '');
        if (!is_uploaded_file($tmp)) return;

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        // Extensões comuns para QR
        $allowed = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
        if (!in_array($ext, $allowed, true)) {
            // se extensão não for aceita, ignore (ou trate como erro, se desejar)
            return;
        }

        $novo = $columnName . '_' . $chave . '_' . uniqid('', true) . '.' . $ext;
        if (move_uploaded_file($tmp, $dir . $novo)) {
            $dados[$columnName] = $novo;
            $updatedImages[$columnName] = $novo;
        }
    };

    $handleUpload('imgqrcodecurso',     'imgqrcodecurso');
    $handleUpload('imgqrcodeanual',     'imgqrcodeanual');
    $handleUpload('imgqrcodevitalicio', 'imgqrcodevitalicio');

    // Monta UPDATE dinâmico
    $setParts = [];
    foreach ($dados as $campo => $valor) {
        $setParts[] = "$campo = :$campo";
    }
    if (!$setParts) {
        $respond(['status' => 'ok', 'mensagem' => 'Nada a atualizar.']);
    }

    $sql = "UPDATE new_sistema_cursos_turmas SET " . implode(', ', $setParts) . " WHERE chave = :chave LIMIT 1";
    $stmt = $con->prepare($sql);

    // Bind valores
    foreach ($dados as $campo => $valor) {
        // monetários: se null, salva NULL
        if (in_array($campo, ['valorvenda', 'valorcartao', 'valoravista', 'valoranual', 'valorhoraaula', 'valorcartaoanual'], true)) {
            if ($valor === null || $valor === '') {
                $stmt->bindValue(":$campo", null, PDO::PARAM_NULL);
            } else {
                // bind como string; se suas colunas são DECIMAL, o PDO tratará bem
                $stmt->bindValue(":$campo", $valor, PDO::PARAM_STR);
            }
        } else {
            // textos/numéricos livres
            if ($valor === '') {
                $stmt->bindValue(":$campo", null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(":$campo", $valor, PDO::PARAM_STR);
            }
        }
    }
    $stmt->bindValue(':chave', $chave, PDO::PARAM_STR);

    $stmt->execute();
    $rows = $stmt->rowCount();

    $respond([
        'status'         => 'ok',
        'mensagem'       => $rows > 0 ? 'Dados comerciais atualizados!' : 'Nenhuma alteração aplicada.',
        'updated_images' => $updatedImages
    ]);
} catch (Throwable $e) {
    $respond([
        'status'   => 'erro',
        'mensagem' => 'Falha ao atualizar os dados comerciais.',
        'erro'     => $e->getMessage()
    ], 500);
}
