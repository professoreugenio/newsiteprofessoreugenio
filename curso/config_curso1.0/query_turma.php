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
        $idTurma = $codigoturma;
        if (!$codigousuario || !$codigocurso) {
            die('Dados do curso incompletos.*1-2');
        }
    } else {
        die('Falha ao descriptografar o cookie.');
    }
} else {
    die('Cookie não encontrado.');
}

 ?>