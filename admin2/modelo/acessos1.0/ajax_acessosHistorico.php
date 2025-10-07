<?php
// acessosv1.0/ajax_acessosHistorico.php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: text/html; charset=utf-8');

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$chavera   = $_POST['chavera']   ?? '';
$idusuario = $_POST['idusuario'] ?? '0';

// if (!$chavera || !$idusuario) {
//     http_response_code(400);
//     echo "<div class='alert alert-warning mb-0'>Parâmetros insuficientes.</div>";
//     exit;
// }

try {
    $pdo = config::connect();
    $sql = "
        SELECT urlrah, datarah, horarah
        FROM a_site_registraacessoshistorico
        WHERE chaverah = :chavera
          AND (idusuariorah = :idusuario OR :idusuario = 0) 
        ORDER BY datarah DESC, horarah DESC
        LIMIT 1000
    ";
    $st = $pdo->prepare($sql);
    $st->bindParam(':chavera', $chavera);
    $st->bindParam(':idusuario', $idusuario, PDO::PARAM_INT);
    $st->execute();

    if ($st->rowCount() === 0) {
        echo "<div class='alert alert-info mb-0'>Sem páginas registradas para esta chave.</div>";
        exit;
    }

    echo "<div class='list-group'>";
    while ($rw = $st->fetch(PDO::FETCH_ASSOC)) {
        $url  = $rw['urlrah'] ?? '';
        $data = $rw['datarah'] ? date('d/m/Y', strtotime($rw['datarah'])) : '—';
        $hora = $rw['horarah'] ?? '—';

        echo "<a href='" . h($url) . "' target='_blank' class='list-group-item list-group-item-action'>
                <div class='d-flex w-100 justify-content-between'>
                    <h6 class='mb-1 text-truncate' title='" . h($url) . "'>
                        <i class='bi bi-link-45deg me-1'></i>" . h($url) . "
                    </h6>
                    <small class='text-nowrap'><i class='bi bi-calendar3 me-1'></i>" . h($data) . " <i class='bi bi-dot'></i> <i class='bi bi-clock me-1'></i>" . h($hora) . "</small>
                </div>
              </a>";
    }
    echo "</div>";
} catch (Exception $e) {
    http_response_code(500);
    echo "<div class='alert alert-danger mb-0'>Erro: " . h($e->getMessage()) . "</div>";
}
