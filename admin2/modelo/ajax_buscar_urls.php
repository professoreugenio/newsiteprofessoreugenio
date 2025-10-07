<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
?>
<?php
echo $user = $_GET['user'];
echo " - ";
echo $turma = $_GET['turma'];
echo " - ";
echo $data = $_GET['data'];

// Busca todos os acessos desse usuário, nessa turma e data:
$stmt = config::connect()->prepare("
    SELECT a.horara, r.urlsru
    FROM a_site_registraacessos a
    LEFT JOIN a_site_registraurl r ON a.idurlra = r.codigoregistrourl
    WHERE a.idusuariora = :user
      AND a.idturmara = :turma
      AND a.datara = :data
    ORDER BY a.horara
");
$stmt->bindParam(':user', $user);
$stmt->bindParam(':turma', $turma);
$stmt->bindParam(':data', $data);
$stmt->execute();
$urls = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$urls) {
    echo '<div class="alert alert-warning">Nenhuma URL acessada registrada.</div>';
} else {
    echo '<ul class="list-group">';
    foreach ($urls as $url) {
        echo '<li class="list-group-item"><span class="fw-bold">' . htmlspecialchars($url['horara']) . '</span> — <a href="' . htmlspecialchars($url['urlsru']) . '" target="_blank">' . htmlspecialchars($url['urlsru']) . '</a></li>';
    }
    echo '</ul>';
}
