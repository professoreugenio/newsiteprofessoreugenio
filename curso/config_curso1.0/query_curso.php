<?php

if (isset($_COOKIE['nav'])) {
    $cookieCriptografado = $_COOKIE['nav'];
    $dadosDecodificados = encrypt($cookieCriptografado, 'd');
    if ($dadosDecodificados && strpos($dadosDecodificados, '&') !== false) {
        $partes = explode('&', $dadosDecodificados);
        $codigousuario  = $partes[0] ?? null;
        $codigocurso  = $partes[1] ?? null;
        $codigoturma  = $partes[2] ?? null;
        $codigomodulo = $partes[3] ?? null;
        $codigoaula = $partes[4] ?? null;
        if (!$codigousuario || !$codigocurso) {
            die('Dados do curso incompletos*' . $dadosDecodificados . '.');
        }
    } else {
        die('Falha ao descriptografar o cookie.');
    }




    $queryCurso = $con->prepare("SELECT * FROM new_sistema_cursos WHERE codigocursos = :idcurso ");
    $queryCurso->bindParam(":idcurso", $codigocurso);
    $queryCurso->execute();
    $rwCurso = $queryCurso->fetch(PDO::FETCH_ASSOC);
    $nmCurso = $rwCurso['nome'];
    $comercial = $rwCurso['comercialsc'];
} else {
    echo ('<meta http-equiv="refresh" content="0; url=../curso/modulos.php">');
    exit();
}
