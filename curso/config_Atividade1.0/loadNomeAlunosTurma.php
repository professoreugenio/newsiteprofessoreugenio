<div class="container mt-5">
    <h6 class="text-center text-primary mb-4">Revisão de Atividades Enviadas</h6>

    <!-- Lista de alunos -->
    <div id="listaAlunos" class="d-flex flex-wrap justify-content-center gap-3 mb-5"></div>

    <!-- Atividades do aluno selecionado -->
    <div id="atividadeAlunoSelecionado"></div>
</div>

<script>
    function listarAlunos() {
        const formData = new FormData();
        formData.append('idcurso', <?= $codigocurso ?>);
        formData.append('idturma', '<?= $chaveTurma ?>');
        formData.append('idpublicacao', <?= $codigoaula ?>);

        fetch("config_Atividade1.0/ajax_listarAlunosComAtividade.php", {
                method: "POST",
                body: formData
            })
            .then(r => r.text())
            .then(html => document.getElementById("listaAlunos").innerHTML = html);
    }

    listarAlunos(); // Chamar ao carregar
</script>

<script>
    function carregarAtividadesAluno(idaluno) {
        const formData = new FormData();
        formData.append('idcurso', <?= $codigocurso ?>);
        formData.append('idpublicacao', <?= $codigoaula ?>);
        formData.append('idmodulo', <?= $codigomodulo ?>);
        formData.append('idaluno', idaluno);

        fetch("config_Atividade1.0/ajax_loadAtividade.php", {
                method: "POST",
                body: formData
            })
            .then(r => r.text())
            .then(html => document.getElementById("atividadeAlunoSelecionado").innerHTML = html);
    }
</script>