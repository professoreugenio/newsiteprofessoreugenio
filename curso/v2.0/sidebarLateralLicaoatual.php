<!-- Box com lições -->
<div id="licoesBox" class="box-licoes shadow-lg">
    <span class="badge bg-success" onclick="window.location.href='../curso/modulos.php';">Acessar módulos</span>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div style="cursor: pointer;" onclick="window.location.href='modulo_status.php';" id="nmModuloAula" class="">
            <i class="bi bi-chevron-left me-2"></i><span>Voltar</span> <?= htmlspecialchars($nmmodulo) ?>
        </div>
        <button id="fecharBox" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="box-Menulicoes">
        <?php
        if (!empty($fetchLicao) && is_array($fetchLicao)) {
            foreach ($fetchLicao as $key => $value) {
                $codigoaulas = $value['codigopublicacoes'];
                $query = $con->prepare("SELECT * FROM a_aluno_andamento_aula 
                WHERE idpublicaa = :codigoaula AND idalunoaa = :codigousuario  ");
                $query->bindParam(":codigoaula", $codigoaulas);
                $query->bindParam(":codigousuario", $codigousuario);
                $query->execute();
                $rwaulavista = $query->fetch(PDO::FETCH_ASSOC);
                $selecionada = $rwaulavista ? 'lida' : '';
        ?>
                <?php $num = $key + 1; ?>
                <?php $enc = encrypt($value['idpublicacaopc'], $action = 'e'); ?>

                <?php
                $query = $con->prepare("
    SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(totalhoras))) AS somatotal 
    FROM a_curso_videoaulas 
    WHERE idpublicacaocva = :idpublicacao
");
                $query = $con->prepare("
    SELECT SUM(TIME_TO_SEC(totalhoras)) AS totalSegundos
    FROM a_curso_videoaulas 
    WHERE idpublicacaocva = :idpublicacao
");
                $query->bindParam(":idpublicacao", $codigoaulas);
                $query->execute();
                $result = $query->fetch(PDO::FETCH_ASSOC);

                $segundos = (int)($result['totalSegundos'] ?? 0);

                // Converte segundos manualmente
                $hora = floor($segundos / 3600);
                $minuto = floor(($segundos % 3600) / 60);
                $segundo = $segundos % 60;

                // Formata com zero à esquerda
                $tempoTotal = ($hora > 0)
                    ? sprintf('<strong>%d:%02d:%02d</strong>', $hora, $minuto, $segundo)
                    : sprintf('<strong>%02d:%02d</strong>', $minuto, $segundo);
                ?>
                <?php
                ?>
                <div class="licao <?php echo $selecionada; ?>">
                    <div class="d-flex justify-content-between align-items-center w-100" id="<?php echo $enc; ?>" onclick="window.location.href='actionCurso.php?lc=<?php echo $enc; ?>';">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <span class="titulo-licao"><?php echo $value['ordempc']; ?>
                                <?php echo $value['titulo']; ?> 🕛<?php echo $tempoTotal;  ?>
                            </span>
                        </div>
                        <!-- <button class="btn btn-sm btn-outline-primary btn-marcar">
                        <i class="bi bi-chevron-right"></i>
                    </button> -->
                    </div>
                </div>
                <div id="exibelistaTopicos"></div>
        <?php }
        }
        ?>
    </div>
    <div class="box-botoes-licao  gap-2 px-3 py-2">
        <!-- Botões de navegação -->
        <!-- Botões de navegação centralizados lado a lado -->
        <!-- Container para centralizar os dois botões na mesma linha -->
        <div class="d-flex justify-content-center mt-2">
            <div id="botoesNavegacao" class="d-flex gap-3">
                <?php if ($codigoAnterior): ?>
                    <a class="btn btn-warning px-4" href="actionCurso.php?lc=<?php echo $encAnt; ?>">
                        <i class="bi bi-arrow-left-circle"></i> ANTERIOR
                    </a>
                <?php endif; ?>
                <?php if ($codigoProxima): ?>
                    <a title="Conclua a atividade desta lição"
                        class="btn btn-success px-4"
                        href="actionCurso.php?lc=<?php echo $encProx; ?>">
                        PRÓXIMA <i class="bi bi-arrow-right-circle"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <!-- Botão ATIVIDADE embaixo, centralizado e com largura total -->
        <?php if ($quantAtv >= 1): ?>
            <div class="mt-4 d-flex justify-content-center">
                <a class="btn btn-warning btn-lg w-100 d-flex justify-content-center align-items-center gap-2"
                    href="actionCurso.php?Atv=<?php echo $QuestInicial; ?>"
                    style="max-width: 600px;">
                    <i class="bi bi-journal-text"></i> ATIVIDADE
                </a>
            </div>
        <?php endif; ?>
        <!-- Loader JS -->
        <script>
            document.querySelectorAll('#botoesNavegacao a').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const originalText = this.innerHTML;
                    this.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Carregando...`;
                    this.classList.add("disabled");
                    window.location.href = this.href;
                });
            });
        </script>
    </div>
</div>