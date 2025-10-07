<?php
$aulaLiberada = "0";

// Verifica se o cookie existe
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
            die('Dados do curso incompletos.*****');
        }
    } else {
        die('Falha ao descriptografar o cookie.');
    }
} else {
    echo ('<meta http-equiv="refresh" content="0; url=../curso/modulos.php">');
    exit();
}
$queryModulo = $con->prepare("SELECT * FROM new_sistema_modulos_PJA WHERE codigomodulos = :codigomodulo");
$queryModulo->bindParam(":codigomodulo", $codigomodulo);
$queryModulo->execute();
$rwModulo = $queryModulo->fetch(PDO::FETCH_ASSOC);
if ($rwModulo) {
    $nmmodulo = $rwModulo['modulo'];
    $bgcolor = $rwModulo['bgcolor'];
} else {
    $nmmodulo = 'Módulo não encontrado';
    $bgcolor = '#ccc';
}
$titulo  = "Seja bem vindo ao seu curso de " . $nmmodulo;
$olho = "Última aula assistida <br><a>Link da aula</a> <br> <h4>Atividade</h4> Em análise  <h4>Progresso</h4> 0% do curso concluído";
$texto = "Clique no título da sua lição ao lado";
$atividade = "Conclua suas atividades para evolução no curso";


$query = $con->prepare("SELECT * FROM new_sistema_publicacoes_PJA,a_aluno_publicacoes_cursos WHERE codigopublicacoes = :codigoaula AND idpublicacaopc = codigopublicacoes ");
$query->bindParam(":codigoaula", $codigoaula);
$query->execute();
$rwAula = $query->fetch(PDO::FETCH_ASSOC);
if ($rwAula) {
    $titulo = $rwAula['titulo'] ?? '';
    $olho = $rwAula['olho'] ?? '';
    $ordem = $rwAula['ordempc'] ?? '';
    $pasta = $rwAula['pasta'] ?? '';


    $texto = htmlspecialchars_decode($rwAula['texto'], ENT_QUOTES);
}
$query = $con->prepare("SELECT * FROM a_aluno_andamento_aula, new_sistema_publicacoes_PJA
    WHERE idalunoaa = :codigousuario AND idmoduloaa = :idmodulo AND codigopublicacoes = idpublicaa
    ORDER BY dataaa DESC, horaaa DESC LIMIT 1");
$query->bindParam(":codigousuario", $codigousuario);
$query->bindParam(":idmodulo", $codigomodulo);
$query->execute();
$rwUltimaaula = $query->fetch(PDO::FETCH_ASSOC);
if ($rwUltimaaula) {
    $tituloultimaaula = $rwUltimaaula['titulo'] ?? '';
    $olhoAaula = $rwUltimaaula['olho'] ?? '';
    $ordemAaula = $rwUltimaaula['ordempc'] ?? '*';
    $encUltimaId = encrypt($rwUltimaaula['idpublicaa'], 'e') ?? '';
} else {
    $olhoAaula = "Nenhuma aula assistida ainda, clique na aula para começar!";
    $tituloultimaaula = 'Nenhuma aula assistida neste módulo.';
    $encUltimaId = "Nenhum registro";
}

if ($comercialDados == '1' || $codigoUser == '1'):
    $queryLicoes = $con->prepare("SELECT * FROM a_aluno_publicacoes_cursos, new_sistema_publicacoes_PJA
    WHERE idmodulopc = :idmodulo AND codigopublicacoes = idpublicacaopc
    AND a_aluno_publicacoes_cursos.visivelpc = '1'
    ORDER BY ordempc ASC");
else:
    $queryLicoes = $con->prepare("SELECT * FROM a_aluno_publicacoes_cursos, new_sistema_publicacoes_PJA
    WHERE idmodulopc = :idmodulo AND aulaliberadapc = :publico AND codigopublicacoes = idpublicacaopc
    AND a_aluno_publicacoes_cursos.visivelpc = '1'
    ORDER BY ordempc ASC");
    $publico = '1';
    $queryLicoes->bindParam(":publico", $publico);
endif;
$queryLicoes->bindParam(":idmodulo", $codigomodulo);
$queryLicoes->execute();



$fetchTodasLicoes = $queryLicoes->fetchAll(PDO::FETCH_ASSOC);
$quantLicoes = $fetchTodasLicoes ? count($fetchTodasLicoes) : 0;
if (!empty($codigoaula)) {
    $queryLicao = $con->prepare("SELECT * FROM a_aluno_publicacoes_cursos, new_sistema_publicacoes_PJA
        WHERE a_aluno_publicacoes_cursos.idmodulopc = :idmodulo AND a_aluno_publicacoes_cursos.idpublicacaopc = :idpublicacao
        AND codigopublicacoes = idpublicacaopc AND a_aluno_publicacoes_cursos.visivelpc = '1'
        ORDER BY ordem ASC");
    $queryLicao->bindParam(":idmodulo", $codigomodulo);
    $queryLicao->bindParam(":idpublicacao", $codigoaula);
    $queryLicao->execute();
    $fetchLicao = $queryLicao->fetchAll(PDO::FETCH_ASSOC);
    $quantL = $fetchLicao ? count($fetchLicao) : 0;
}
$queryAssistidas = $con->prepare("SELECT * FROM a_aluno_andamento_aula WHERE idalunoaa = :idaluno AND idmoduloaa = :idmodulo");
$queryAssistidas->bindParam(":idaluno", $codigousuario);
$queryAssistidas->bindParam(":idmodulo", $codigomodulo);
$queryAssistidas->execute();
$fetchAssistidas = $queryAssistidas->fetchAll(PDO::FETCH_ASSOC);
$quantAssisitdas = $fetchAssistidas ? count($fetchAssistidas) : 0;
$perc = 0;
if ($quantLicoes > 0) {
    $perc = ($quantAssisitdas / $quantLicoes) * 100;
}
$perc = number_format($perc, 0);
if ($perc < 25) {
    $corBarra = 'bg-danger';
} elseif ($perc < 70) {
    $corBarra = 'bg-warning text-dark';
} else {
    $corBarra = 'bg-success';
}
$barra = '';
$barra12 = '';
if ($quantAssisitdas > 0) {
    $barra = "<div class='col-md-4 mb-3'>
        <div class='card card-custom h-100'>
            <div class='card-body'>
                <h6 class='card-title'>📊 Progresso $perc%
                    <div class='progress' style='height: 25px;'>
                        <div class='progress-bar $corBarra text-dark fw-bold' role='progressbar'
                            style='width: $perc%;' aria-valuenow='$perc' aria-valuemin='0' aria-valuemax='100'>
                        </div>
                    </div>
                </h6>
            </div>
        </div>
    </div>";
    $barra12 = "<div id='card-barraprogresso' class='col-md-12 mb-3'>
        <div class='card card-custom h-100'>
            <div class='card-body'>
                <h6 class='card-title'>📊 Progresso $perc%
                    <div class='progress' style='height: 15px; margin-top:10px'>
                        <div class='progress-bar mt-2 $corBarra text-dark fw-bold' role='progressbar'
                            style='width: $perc%;' aria-valuenow='$perc' aria-valuemin='0' aria-valuemax='100'>
                        </div>
                    </div>
                </h6>
            </div>
        </div>
    </div>";
}
?>
<?php
$quantAtv = 0;
$fetchAtvq = [];
if (!empty($codigoaula)) {
    $queryAtividadeQ = $con->prepare("SELECT * FROM a_curso_questionario 
        WHERE idpublicacaocq = :idpublicacao AND visivelcq = :visivel");
    $visivel = "1";
    $queryAtividadeQ->bindParam(":idpublicacao", $codigoaula);
    $queryAtividadeQ->bindParam(":visivel", $visivel);
    $queryAtividadeQ->execute();
    $fetchAtvq = $queryAtividadeQ->fetchAll(PDO::FETCH_ASSOC);
    $quantAtv = count($fetchAtvq);
    $queryQuestInicial = $con->prepare("SELECT * FROM a_curso_questionario 
        WHERE idpublicacaocq = :idpublicacao AND visivelcq = :visivel ORDER BY ordemcq ASC LIMIT 1");
    $queryQuestInicial->bindParam(":idpublicacao", $codigoaula);
    $queryQuestInicial->bindParam(":visivel", $visivel);
    $queryQuestInicial->execute();
    $rwQuestInicial = $queryQuestInicial->fetch(PDO::FETCH_ASSOC);
    $QuestInicial = $rwQuestInicial ? $rwQuestInicial['codigoquestionario'] : null;
    $QuestInicial = encrypt($QuestInicial, $action = 'e');
    /**VERIFICA TOTAL DE QUESTÕES RESPONDIDAS */

    $queryQenv = $con->prepare("SELECT * FROM a_curso_questionario_resposta WHERE idaulaqr = :idaula AND idalunoqr = :idaluno ");
    $queryQenv->bindParam(":idaula", $codigoaula);
    $queryQenv->bindParam(":idaluno", $codigousuario);
    $queryQenv->execute();
    $resultEnv = $queryQenv->fetchALL();
    $quantQenviadas = count($resultEnv);
}
?>
<?php
$queryQuest = $con->prepare("SELECT * FROM a_curso_questionario_resposta WHERE idalunoqr = :idaluno AND idaulaqr= :idaula ");
$queryQuest->bindParam(":idaluno", $codigoUsuario);
$queryQuest->bindParam(":idaula", $codigoaula);
$queryQuest->execute();
$rwQuest = $queryQuest->fetch(PDO::FETCH_ASSOC);
$qQuest = 0;
if ($rwQuest) {
    $qQuest = count($rwQuest);
}
?>
<?php
// Consulta atual
$queryAtual = $con->prepare("SELECT * FROM a_aluno_publicacoes_cursos 
    WHERE idpublicacaopc = :id AND idcursopc = :idturma AND idmodulopc = :idmodulo AND visivelpc = '1'");
$queryAtual->bindParam(":id", $codigoaula);
$queryAtual->bindParam(":idturma", $codigocurso);
$queryAtual->bindParam(":idmodulo", $codigomodulo);
$queryAtual->execute();
$rwAtual = $queryAtual->fetch(PDO::FETCH_ASSOC);
$codigoProxima = null;
$codigoAnterior = null;
// Verifica se encontrou o atual
if ($rwAtual) {
    $ordemAtual = $rwAtual['ordempc'];
    $aulaLiberada = ($rwAtual['aulaliberadapc'] == '1') ? '1' : '0';
    // Consulta anterior
    $queryAnterior = $con->prepare("SELECT idpublicacaopc FROM a_aluno_publicacoes_cursos 
        WHERE ordempc < :ordem AND idcursopc = :idturma AND idmodulopc = :idmodulo AND visivelpc = '1' 
        ORDER BY ordempc DESC LIMIT 1");
    $queryAnterior->bindParam(":ordem", $ordemAtual);
    $queryAnterior->bindParam(":idturma", $codigocurso);
    $queryAnterior->bindParam(":idmodulo", $codigomodulo);
    $queryAnterior->execute();
    $anterior = $queryAnterior->fetch(PDO::FETCH_ASSOC);
    $codigoAnterior = $anterior['idpublicacaopc'] ?? null;
    $encAnt = encrypt($codigoAnterior, $action = 'e');
    // Consulta próxima
    $queryProxima = $con->prepare("SELECT idpublicacaopc FROM a_aluno_publicacoes_cursos 
        WHERE ordempc > :ordem AND idcursopc = :idturma AND idmodulopc = :idmodulo AND visivelpc = '1' 
        ORDER BY ordempc ASC LIMIT 1");
    $queryProxima->bindParam(":ordem", $ordemAtual);
    $queryProxima->bindParam(":idturma", $codigocurso);
    $queryProxima->bindParam(":idmodulo", $codigomodulo);
    $queryProxima->execute();
    $proxima = $queryProxima->fetch(PDO::FETCH_ASSOC);
    $codigoProxima = $proxima['idpublicacaopc'] ?? null;
    $encProx = encrypt($codigoProxima, $action = 'e');
} else {
    // echo "Aula atual não encontrada.";
}
?>