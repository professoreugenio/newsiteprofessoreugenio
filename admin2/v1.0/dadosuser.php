<?php
// Verifica se o cookie existe
if (isset($_COOKIE['adminuserstart'])) {
    // Tenta descriptografar
    $decadm = encrypt($_COOKIE['adminuserstart'], 'd');



    // Explode a string decodificada
    $expadm = explode("&", $decadm);

    // Verifica se todos os dados esperados estão presentes
    if (count($expadm) >= 3) {
        $codadm   = $expadm[0]??''; // Ex: ID do admin
        $niveladm = $expadm[1]??''; // Ex: nível de permissão
        $nomeadm  = $expadm[2]??''; // Ex: nome do admin
        $idEnc = encrypt($codadm, 'e');
        $con = config::connect();
        $query = $con->prepare("SELECT * FROM new_sistema_usuario WHERE codigousuario  = :iduser ");
        $query->bindParam(":iduser", $codadm);
        $query->execute();
        $rw = $query->fetch(PDO::FETCH_ASSOC);
        $foto = $rw['imagem200'];
        $pasta = $rw['pastasu'];
        $img = $raizSite . "/fotos/usuarios/" . $pasta . "/" . $foto;
        if ($foto == "usuario.jpg") {
            $img = $raizSite . "/fotos/usuarios/" . $foto;
        }
    } else {
        // Falha ao decodificar — valores ausentes
        exit("Erro: dados de administrador incompletos.");
    }
} else {
    // Cookie ausente
    exit("Erro: acesso não autorizado.");
}



// Quem está logado (via autenticacao.php você costuma ter $niveladm)
$nivelLogado = (int)($niveladm ?? ($_SESSION['niveladm'] ?? $_SESSION['nivel'] ?? 0));
$podeEditarNivel = ($nivelLogado === 1);
