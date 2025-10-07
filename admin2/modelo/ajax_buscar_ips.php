<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
?>
<?php
$user = $_GET['user'];
$data = $_GET['data'];

$stmt = config::connect()->prepare("
    SELECT DISTINCT ipra 
    FROM a_site_registraacessos 
    WHERE idusuariora = :user AND datara = :data
    ORDER BY ipra
");
$stmt->bindParam(':user', $user);
$stmt->bindParam(':data', $data);
$stmt->execute();
$ips = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($ips as $ip) {
    echo '<li class="list-group-item">';
    echo '<button class="btn btn-link p-0 ip-detalhe" style="font-weight:bold" data-ip="' . htmlspecialchars($ip) . '">';
    echo htmlspecialchars($ip);
    echo '</button>';
    echo '</li>';
}
