<?php

function fotoAlunoUrl($pasta, $imagem)
{
    $urlFoto = "https://professoreugenio.com/fotos/usuarios/{$pasta}/{$imagem}";
    if (!$imagem) return "https://professoreugenio.com/fotos/usuarios/usuario.jpg";
    $headers = @get_headers($urlFoto);
    if ($headers && strpos($headers[0], '200') !== false) {
        return $urlFoto;
    } else {
        return "https://professoreugenio.com/fotos/usuarios/usuario.jpg";
    }
}

function linkWhats($cel, $msg)
{
    $numero = preg_replace('/\D/', '', $cel);
    if ($numero && substr($numero, 0, 2) !== '55') $numero = '55' . $numero;
    return $numero ? 'https://wa.me/' . $numero . '?text=' . urlencode($msg) : false;
}
 ?>