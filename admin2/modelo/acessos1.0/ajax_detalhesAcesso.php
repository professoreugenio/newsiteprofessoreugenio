<?php
// acessos1.0/ajax_detalhesAcesso.php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: text/html; charset=UTF-8');

$idusuario = isset($_POST['idusuario']) ? (int)$_POST['idusuario'] : 0;
$data      = isset($_POST['data']) ? trim($_POST['data']) : date('Y-m-d');

if ($idusuario <= 0) {
    echo '<div class="alert alert-warning">Usuário inválido.</div>';
    exit;
}

try {
    $pdo = config::connect();

    // Cabeçalho com nome do aluno
    $h = $pdo->prepare("SELECT nome FROM new_sistema_cadastro WHERE codigocadastro = :id LIMIT 1");
    $h->bindParam(':id', $idusuario, PDO::PARAM_INT);
    $h->execute();
    $nome = $h->fetchColumn();

    echo '<div class="mb-2">';
    echo '<div class="fw-semibold">' . htmlspecialchars($nome ?: 'Aluno') . '</div>';
    echo '<div class="small text-muted">Data: ' . date('d/m/Y', strtotime($data)) . '</div>';
    echo '</div>';

    // Detalhes de acessos do dia
    $sql = "
    SELECT 
      r.ipra,
      COALESCE(u.urlra, r.urlra) AS url,
      r.horara, 
      r.horafinalra
    FROM a_site_registraacessosvendas r
    LEFT JOIN a_site_registraurl u
      ON u.codigoregistrourl = r.idregistrourl
    WHERE r.idusuariora = :id AND r.datara = :data
    ORDER BY r.horara ASC, r.horafinalra ASC
    LIMIT 2000
  ";
    $st = $pdo->prepare($sql);
    $st->bindParam(':id', $idusuario, PDO::PARAM_INT);
    $st->bindParam(':data', $data);
    $st->execute();

    if ($st->rowCount() === 0) {
        echo '<div class="alert alert-info">Sem registros para este dia.</div>';
        exit;
    }

    echo '<div class="table-responsive">';
    echo '<table class="table table-sm align-middle">';
    echo '<thead><tr>';
    echo '<th style="width:140px;">IP</th>';
    echo '<th>URL/Página</th>';
    echo '<th style="width:120px;">Hora início</th>';
    echo '<th style="width:120px;">Hora fim</th>';
    echo '</tr></thead><tbody>';

    while ($rw = $st->fetch(PDO::FETCH_ASSOC)) {
        $ip   = $rw['ipra'] ?: '-';
        $url  = $rw['url'] ?: '-';
        $hi   = $rw['horara'] ? substr($rw['horara'], 0, 5) : '-';
        $hf   = $rw['horafinalra'] ? substr($rw['horafinalra'], 0, 5) : '-';

        echo '<tr>';
        echo '<td><span class="badge bg-dark-subtle text-body">' . htmlspecialchars($ip) . '</span></td>';
        echo '<td class="text-truncate"><a href="' . htmlspecialchars($url) . '" target="_blank" class="text-decoration-none">' . htmlspecialchars($url) . '</a></td>';
        echo '<td>' . htmlspecialchars($hi) . '</td>';
        echo '<td>' . htmlspecialchars($hf) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table></div>';
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Erro: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
