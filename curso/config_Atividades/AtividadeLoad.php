<?php define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';
 $navDec = encrypt($_COOKIE['nav'], 'd');
if (is_string($navDec) && !empty($navDec)) {
    $expnav = explode("&", $navDec);
} else {
    error_log("Falha ao descriptografar o cookie 'nav'.");
}
$iduser = $expnav[0];
$idatv = $expnav[5];
$codigoaula = $expnav[4];
$codigomodulo = $expnav[3];
?>

<?php

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
?>

<?php

$queryAula = $con->prepare("SELECT * FROM  new_sistema_publicacoes_PJA
    WHERE codigopublicacoes = :idpublicaa");
$queryAula->bindParam(":idpublicaa", $codigoaula);
$queryAula->execute();
$rwAulaAtual = $queryAula->fetch(PDO::FETCH_ASSOC);
if ($rwAulaAtual) {
    $tituloAula = $rwAulaAtual['titulo'];
} else {
    $tituloAula = 'Nenhuma publicação disponível.';
}

?>
<h4 id="nmmodulo"></h4><?php echo $nmmodulo;  ?></h4>
<h5 id="tituloaula"><?php echo $tituloAula;  ?> </h5>
<div id="formContainer" class="form-card">
    <?php
    /**
     * Verifica Total de questões
     */
    $codigoaula;
    $queryQ = $con->prepare("SELECT COUNT(*) as total FROM a_curso_questionario WHERE idpublicacaocq = :idpublic AND visivelcq='1'");
    $queryQ->bindParam(":idpublic", $codigoaula);
    $queryQ->execute();
    $result = $queryQ->fetch(PDO::FETCH_ASSOC);
    $quantQuestoes = $result['total'];

    /**
     * Verifica Total de questões inseridas
     */

    $queryQenv = $con->prepare("
    SELECT COUNT(DISTINCT r.idquestionarioqr) AS total
    FROM a_curso_questionario_resposta r
    INNER JOIN a_curso_questionario q ON r.idquestionarioqr = q.codigoquestionario  
    WHERE q.idpublicacaocq = :idpublic
      AND q.visivelcq = '1'
      AND r.idalunoqr = :iduser
");

    $queryQenv->bindParam(":idpublic", $codigoaula);
    $queryQenv->bindParam(":iduser", $iduser); // Certifique-se de que $iduser está definido
    $queryQenv->execute();
    $resultEnv = $queryQenv->fetch(PDO::FETCH_ASSOC);
    $quantQenv = $resultEnv['total'];



    /**
     * 
     */
    $query = $con->prepare("SELECT * FROM  a_curso_questionario WHERE idpublicacaocq = :idpublic AND codigoquestionario = :idquest AND visivelcq ='1'");
    $query->bindParam(":idpublic", $codigoaula);
    $query->bindParam(":idquest", $idatv);
    $query->execute();
    $rwAtividade = $query->fetch(PDO::FETCH_ASSOC);
    if (!empty($rwAtividade)) {
        $titulo = $rwAtividade['titulocq'];
        $ordem = $rwAtividade['ordemcq'];
        $tipo = $rwAtividade['tipocq'];
    } else {
        $titulo = NULL;
        $ordem = NULL;
        $tipo = NULL;
    }
    ?>
    <?php if($iduser==1): ?>
    Ordem: <?php echo $ordem; ?>

    Id: <?php echo $idatv; ?>
    Aula : <?php echo $codigoaula; ?>
    Tipo : <?php echo $tipo; ?>
    total enviado : <?php echo $quantQenv; ?>
    <?php endif; ?>
    <form id="formResposta">
        <input type="hidden" name="ordem" id="ordem" value="<?php echo $ordem; ?>">
        <h3>Um total de <?php echo $quantQuestoes;  ?> questões</h3>

        <?php if ($quantQenv < $quantQuestoes): ?>
            <?php echo $tipo;  ?>
            <?php if ($tipo == 1): ?>
                <h5 class="mb-4"><i class="bi bi-question-circle-fill text-primary"></i> <?php echo $ordem; ?> ) <?php echo $titulo; ?></h5>
                <p class="mb-2">Responda abaixo:.</p>
                <div class="form-floating mb-3">
                    <textarea class="form-control" id="respostaAluno" placeholder="Digite sua resposta aqui" style="height: 120px" required></textarea>
                    <label for="respostaAluno">Sua resposta</label>
                </div>
            <?php endif; ?>
            <?php if ($tipo == 2): ?>
                <div class="card shadow-sm border-0 rounded p-4 mb-4">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-question-circle-fill me-2"></i> <?php echo $titulo; ?>
                    </h5>
                    <p class="mb-4 fs-5 text-secondary">Responda abaixo:</p>
                    <div class="mb-3">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="quest" id="opcaoA" value="A">
                            <label class="form-check-label" for="opcaoA">
                                A. <?php echo $rwAtividade['opcaoa']; ?>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="quest" id="opcaoB" value="B">
                            <label class="form-check-label" for="opcaoB">
                                B. <?php echo $rwAtividade['opcaob']; ?>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="quest" id="opcaoC" value="C">
                            <label class="form-check-label" for="opcaoC">
                                C. <?php echo $rwAtividade['opcaoc']; ?>
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="quest" id="opcaoD" value="D">
                            <label class="form-check-label" for="opcaoD">
                                D. <?php echo $rwAtividade['opcaod']; ?>
                            </label>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($tipo == 3): ?>
                <h5 class="mb-4">
                    <i class="bi bi-question-circle-fill text-primary"></i>
                    <?php echo $titulo; ?>
                </h5>
                <p class="mb-4">Responda abaixo:</p>

                <?php
                $query = $con->prepare("SELECT * FROM a_curso_questionario_afirmacoes WHERE idpergunta = :idatividade ");
                $query->bindParam(":idatividade", $idatv);
                $query->execute();
                $fetch = $query->fetchAll();

                foreach ($fetch as $key => $value) {
                    $index = $key + 1;
                    $selectId = "resposta_" . $index;
                ?>

                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <!-- Afirmação -->
                                <div class="flex-grow-1">
                                    <p class="card-text mb-0">
                                        <strong><?php echo $index; ?>.</strong>
                                        <?php echo htmlspecialchars($value['afirmacao']); ?>
                                    </p>
                                </div>

                                <!-- Select -->
                                <div style="min-width: 150px;">
                                    <label for="<?php echo $selectId; ?>" class="form-label mb-1">Sua resposta:</label>
                                    <select class="form-select" name="respostas[<?php echo $value['idafirmacoes']; ?>]" id="<?php echo $selectId; ?>">
                                        <option selected disabled>Escolha uma opção</option>
                                        <option value="V">Verdadeiro</option>
                                        <option value="F">Falso</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php } ?>

            <?php endif; ?>


            <div class="d-flex justify-content-center gap-3">
                <?php
                if ($tipo == 1):
                    // Lógica para tipo 1
                    echo ('<button type="button" id="btnEnviar" onclick="enviarResposta()" class="btn btn-success">
                <i class="bi bi-send"></i> Enviar resposta
            </button>');
                elseif ($tipo == 2):
                    // Lógica para tipo 2
                    echo ('<button type="button" id="btnEnviar" onclick="enviarResposta()" class="btn btn-success">
                <i class="bi bi-send"></i> Enviar resposta
            </button>');
                elseif ($tipo == 3):
                    // Lógica para tipo 3
                    echo ('<button type="button" id="btnEnviar" onclick="enviarRespostaVF()" class="btn btn-success">
                <i class="bi bi-send"></i> Enviar resposta V F
            </button>');
                else:
                // Lógica para outros casos
                endif;
                ?>



                <button type="button" id="btnProxima" class="btn btn-primary" style="display: none;">
                    hp <i class="bi bi-arrow-right-circle"></i> Próxima pergunta
                </button>
            </div>

        <?php else: ?>
            <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div>
                    <strong>Todas as questões foram respondidas!</strong>
                </div>
            </div>

            <div class="text-center">
                <button type="button" id="btnEnviar" onclick="finalizarAtividades()" class="btn btn-success btn-lg">
                    <i class="bi bi-check-circle me-2"></i> Finalizar atividade ?
                </button>
            </div>

        <?php endif; ?>


    </form>
</div>