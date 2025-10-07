<?php
// Recuperação do usuário a partir do cookie
$iduser = $nomeuser = $emailuser = $dataanv = null;
if (!empty($_COOKIE['adminstart']) || !empty($_COOKIE['startusuario'])) {
    $cookie = !empty($_COOKIE['adminstart']) ? $_COOKIE['adminstart'] : $_COOKIE['startusuario'];
    $decstart = encrypt($cookie, 'd');
    $exp = explode("&", $decstart);
    $iduser = $exp[0];
    $nomeuser = $exp[2];
    $emailuser = $exp[3];
    $dataanv = "2005-07-01";
}
// Consulta das turmas
if (!empty($_COOKIE['adminstart'])) {
    $query = $con->prepare("
        SELECT DISTINCT t.*, 
            (SELECT MAX(r.datara) FROM a_site_registraacessos r WHERE r.idturmara = t.codigoturma) as ultimo_acesso
        FROM new_sistema_inscricao_PJA i
        INNER JOIN new_sistema_cursos_turmas t ON i.chaveturma = t.chave
        ORDER BY ultimo_acesso DESC
        LIMIT 0,15
    ");
} else {
    $query = $con->prepare("
        SELECT t.*, 
            (SELECT MAX(r.datara) FROM a_site_registraacessos r WHERE r.idturmara = t.codigoturma) as ultimo_acesso
        FROM new_sistema_inscricao_PJA i
        INNER JOIN new_sistema_cursos_turmas t ON i.chaveturma = t.chave
        WHERE i.codigousuario = :idusuario
        ORDER BY ultimo_acesso DESC
    ");
    $query->bindParam(":idusuario", $iduser);
}
$query->execute();
$turmas = $query->fetchAll();
$quant = count($turmas);
foreach ($turmas as $value) {
    $idCurso     = isset($value['codcursost'])     ? $value['codcursost']     : null;
    $codTurma    = isset($value['codigoturma'])    ? $value['codigoturma']    : null;
    $chaveTurma  = isset($value['chave'])          ? $value['chave']          : null;
    $comercial   = isset($value['comercialt'])     ? $value['comercialt']     : null;
    $dtprazo     = isset($value['dataprazosi'])    ? $value['dataprazosi']    : null;
    $ativo       = isset($value['andamento'])      ? $value['andamento']      : null;
    $assinante   = isset($value['renovacaosi'])    ? $value['renovacaosi']    : null;
    $duracao     = time() + $addtime;
    // Geração do token de acesso
    $tokenturma = implode("&", [$iduser, $nomeuser, $emailuser, $dataanv, $codTurma, $chaveTurma, $duracao, $dtprazo]);
    $tokem = encrypt($tokenturma, 'e');
    // Último acesso formatado
    $queryUltimoAcesso = $con->prepare("SELECT * FROM a_site_registraacessos WHERE idusuariora = :idusuario AND idturmara = :idturma ORDER BY datara DESC LIMIT 1 ");
    $queryUltimoAcesso->bindParam(":idusuario", $iduser);
    $queryUltimoAcesso->bindParam(":idturma", $codTurma);
    // Executa a consulta
    $queryUltimoAcesso->execute();
    $rwUltAcesso = $queryUltimoAcesso->fetch(PDO::FETCH_ASSOC);
    $ultimadata   = isset($rwUltAcesso['datara'])    ? databr($rwUltAcesso['datara'])    : 'Sem registro';
    $ultihorai   = isset($rwUltAcesso['horara'])    ? horabr($rwUltAcesso['horara'])    : 'Sem registro';
    $ultihoraf   = isset($rwUltAcesso['horafinalra'])    ? horabr($rwUltAcesso['horafinalra'])    : 'Sem registro';
    // Busca de imagem da turma
    $tipo = 3;
    $stmtFoto = $con->prepare("
        SELECT f.*, c.*
        FROM new_sistema_categorias_PJA c
        INNER JOIN new_sistema_midias_fotos_PJA f ON c.pasta = f.pasta
        WHERE f.codpublicacao = :id AND f.tipo = :tipo
    ");
    $stmtFoto->bindParam(":id", $idCurso);
    $stmtFoto->bindParam(":tipo", $tipo);
    $stmtFoto->execute();
    $resultFoto = $stmtFoto->fetch(PDO::FETCH_ASSOC);
    $arquivo = $raizSite . "/img/nocapa.jpg";
    if ($resultFoto) {
        $pasta = $resultFoto['pasta'];
        $foto = $resultFoto['foto'];
        $diretorio = $raizSite . "/fotos/midias/" . $pasta;
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0777, true);
        }
        $arquivo = $diretorio . "/" . $foto;
    }
?>
    <!-- Card da turma -->
    <div id="chaveRegistraturma" class="card-turma" data-id="<?= $tokem; ?>" style="background-image: url('<?= $arquivo; ?>');" data-aos="zoom-in">
        <div class="topo">
            <?= htmlspecialchars($value['nometurma']) ?>
        </div>
        <div class="rodape">
            <div class="data">Último acesso: <?= $ultimadata; ?> às <?= $ultihoraf; ?> </div>
            <div class="abrir">Abrir</div>
        </div>
    </div>
<?php } ?>