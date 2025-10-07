<?php

// Helper de escape (se não existir ainda)
if (!function_exists('h')) {
    function h($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

// Busca do vídeo do YouTube vinculado à aula (fallback seguro)
$fetchVideo = [];
$quantVideo = 0;
try {
    if (isset($codigoaula) && $codigoaula) {
        $queryVideo = $con->prepare("SELECT * FROM new_sistema_youtube_PJA WHERE codpublicacao_sy = :idpublic LIMIT 1");
        $queryVideo->bindParam(":idpublic", $codigoaula);
        $queryVideo->execute();
        $fetchVideo = $queryVideo->fetchAll(PDO::FETCH_ASSOC);
        $quantVideo = count($fetchVideo);
    }
} catch (Throwable $e) { /* silencioso */
}


/* =========================================================
   CONTAGENS DE ASSISTIDAS x NÃO ASSISTIDAS
   ========================================================= */
$totalAssistidas   = 0;
$totalNaoAssistidas = 0;

if (!empty($fetchTodasLicoes)) {
    $stmtCheck = $con->prepare("
        SELECT 1 
        FROM a_aluno_andamento_aula 
        WHERE idpublicaa = :codigoaula 
          AND idalunoaa  = :codigousuario 
          AND idcursoaa  = :idcurso 
        LIMIT 1
    ");

    foreach ($fetchTodasLicoes as $value) {
        $stmtCheck->bindParam(":codigoaula", $value['codigopublicacoes']);
        $stmtCheck->bindParam(":codigousuario", $codigousuario);
        $stmtCheck->bindParam(":idcurso", $codigocurso);
        $stmtCheck->execute();
        if ($stmtCheck->fetch(PDO::FETCH_NUM)) $totalAssistidas++;
        else $totalNaoAssistidas++;
    }
}

/* =========================================================
   MAPA: codigopublicacoes -> dados úteis da lição
   ========================================================= */
$mapLicoes = []; // [codigopublicacoes] => ['ordem'=>..., 'titulo'=>..., 'idpubOriginal'=>..., 'liberada'=>...]
foreach ($fetchTodasLicoes as $a) {
    $mapLicoes[(string)$a['codigopublicacoes']] = [
        'ordem'         => $a['ordempc'] ?? null,
        'titulo'        => $a['titulo'] ?? '',
        'idpubOriginal' => $a['idpublicacaopc'] ?? null,
        'liberada'      => $a['aulaliberadapc'] ?? null,
    ];
}


$totalLicoesModulo = is_array($fetchTodasLicoes) ? count($fetchTodasLicoes) : 0;

// Dados dinâmicos com fallback
$nmModuloSafe       = isset($nmmodulo) ? h($nmmodulo) : 'Módulo';
$percSafe           = isset($perc) ? (int)$perc : 0;
$totalAssistidas    = isset($totalAssistidas) ? (int)$totalAssistidas : (isset($concluidas) ? (int)$concluidas : 0);
$totalLicoes        = isset($totalLicoes) ? (int)$totalLicoes : (isset($total) ? (int)$total : 0);
$ordemAtualSafe     = isset($ordemAtual) ? h($ordemAtual) : '—';
$tituloSafe         = isset($titulo) ? h($titulo) : 'Título da Lição';
$ytKey              = ($quantVideo >= 1 && !empty($fetchVideo[0]['chavetube_sy'])) ? h($fetchVideo[0]['chavetube_sy']) : '';
if ($percSafe > 100) {
    $percSafe = 100;
    // Lógica para lidar com progresso abaixo de 100%
}
