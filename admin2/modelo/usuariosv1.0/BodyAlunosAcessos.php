<?php

// Se houver variáveis na querystring ou post, guarda na sessão
foreach ($_REQUEST as $key => $value) {
    $_SESSION[$key] = $value;
}




// Início da sessão e segurança
$idUsuarioCript = $_GET['idUsuario'] ?? '';
$idUsuario = $idUsuarioCript ? encrypt($idUsuarioCript, 'd') : null;

if (!$idUsuario) {
    echo "<p class='text-danger text-center mt-5'>ID de usuário inválido.</p>";
    exit;
}

$data30dias = date('Y-m-d', strtotime($data . ' -30 days'));

$dataFiltro = $_GET['data'] ?? null;
$con = config::connect();

// Condicional de consulta
if ($dataFiltro) {
    $stmt = $con->prepare("
        SELECT a.*, u.urlsru, c.nome 
        FROM a_site_registraacessos AS a
        LEFT JOIN a_site_registraurl AS u ON u.codigoregistrourl = a.idurlra
        LEFT JOIN new_sistema_cadastro AS c ON c.codigocadastro = a.idusuariora
        WHERE a.datara = :data AND a.idusuariora = :idusuario
        ORDER BY a.horara ASC
    ");
    $stmt->bindParam(":data", $dataFiltro);
    $stmt->bindParam(":idusuario", $idUsuario);
} else {
    $stmt = $con->prepare("
        SELECT a.*, u.urlsru, c.nome 
        FROM a_site_registraacessos AS a
        LEFT JOIN a_site_registraurl AS u ON u.codigoregistrourl = a.idurlra
        LEFT JOIN new_sistema_cadastro AS c ON c.codigocadastro = a.idusuariora
        WHERE a.idusuariora = :idusuario
        ORDER BY a.datara DESC, a.horara DESC
        LIMIT 30
    ");
    $stmt->bindParam(":idusuario", $idUsuario);
}

$stmt->execute();
$acessos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<?php

// Pega variáveis da sessão (com fallback caso não existam)
$idUsuario = $_SESSION['idUsuario'] ?? '';
$idUrl = $_SESSION['id'] ?? '';
$tm = $_SESSION['tm'] ?? '';
$ts  = $_SESSION['ts'] ?? '';

$linkFinal = "cursos_TurmasAlunos.php?id={$idUrl}&tm={$tm}";
?>

<div class="container">
    <a href="<?= htmlspecialchars($linkFinal) ?>" class="btn btn-success">
        Acessar Turma
    </a>
    <h2 class="mb-4" style="color: #00BB9C;">Acessos do Usuário – <?= $dataFiltro != null ? date('d/m/Y', strtotime($dataFiltro)) : '30 dias' ?></h2>


    <!-- Filtro por data -->
    <form class="row g-3 mb-4" method="get">
        <input type="hidden" name="idUsuario" value="<?= htmlspecialchars($idUsuarioCript) ?>">
        <div class="col-auto">
            <input type="date" name="data" class="form-control" value="<?= htmlspecialchars($dataFiltro) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-gradient">Filtrar</button>
        </div>
    </form>

    <?php require 'usuariosv1.0/require_MsgsWhatsApp.php'; ?>


    <!-- Tabela de acessos -->
    <div class="table-responsive">
        <table class="table table-dark table-bordered table-sm align-middle">
            <thead class="table-secondary text-dark">
                <tr>
                    <th>Dispositivo</th>
                    <th>IP</th>
                    <th>URL</th>
                    <th>Início</th>
                    <th>Fim</th>
                    <th>Contagem</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($acessos): ?>
                    <?php foreach ($acessos as $row): ?>
                        <tr>
                            <td class="text-center">
                                <?php
                                if ($row['dispositivora'] == '1') {
                                    echo '<i class="bi bi-pc-display-horizontal text-info" title="Computador"></i>';
                                } elseif ($row['dispositivora'] == '2') {
                                    echo '<i class="bi bi-phone text-success" title="Celular"></i>';
                                } else {
                                    echo '<span class="text-muted">?</span>';
                                }
                                ?>
                                <?= databr($row['datara']) ?>
                            </td>

                            <td><?= htmlspecialchars($row['ipra']) ?></td>
                            <td><?= htmlspecialchars($row['urlsru'] ?? $row['urlra']) ?></td>
                            <td><?= htmlspecialchars($row['horara']) ?></td>
                            <td><?= htmlspecialchars($row['horafinalra']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($row['contarra']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Nenhum acesso encontrado para esta data.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>