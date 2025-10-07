<!-- Modal 1: Possui PC ou Notebook -->
<div class="modal fade" id="modalPC" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Você possui um computador ou notebook?</h5>
            </div>
            <div class="modal-body text-center text-dark">
                <p>Para melhor aproveitamento do curso, é importante sabermos se você tem acesso a um PC ou notebook para seus estudos?</p>
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button class="btn btn-success" onclick="respondePC(1)">Sim</button>
                    <button class="btn btn-danger" onclick="respondePC(0)">Não</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Data de Nascimento -->
<div class="modal fade" id="modalNascimento" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Qual a sua data de nascimento? <?= $idUser; ?> </h5>
                <input type="hidden" name="iduser" id="iduser" value="<?php echo $encIdUser;  ?>">
            </div>
            <div class="modal-body text-center text-dark">
                <p>Informe corretamente para melhorar a sua experiência no curso.</p>
                <input type="date" class="form-control" id="dataNascimentoInput">
                <div class="mt-4">
                    <button class="btn btn-primary" onclick="respondeNascimento()">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$verifica = $con->prepare("SELECT possuipc, datanascimento_sc, codigocadastro FROM new_sistema_cadastro WHERE codigocadastro = :id");
$verifica->bindParam(":id", $idUser);
$verifica->execute();
$dados = $verifica->fetch(PDO::FETCH_ASSOC);
$pcRespondido = !is_null($dados['possuipc']);
$dataNascimento = $dados['datanascimento_sc'];
$idusuario = $dados['codigocadastro'];
?>


<script>
    document.addEventListener("DOMContentLoaded", () => {
        const pcRespondido = <?= $pcRespondido ? 'true' : 'false' ?>;
        const nascimentoInformado = <?= !empty($dataNascimento) ? 'true' : 'false' ?>;

        if (!pcRespondido) {
            let modalPC = new bootstrap.Modal(document.getElementById('modalPC'));
            modalPC.show();
        } else if (!nascimentoInformado) {
            let modalNascimento = new bootstrap.Modal(document.getElementById('modalNascimento'));
            modalNascimento.show();
        }
    });
</script>

<script>
    function respondePC(valor) {
        const iduser = document.getElementById('iduser').value;

        fetch('config_curso1.0/ajax_updateUsuarioInfo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `campo=possuipc&valor=${valor}&iduser=${encodeURIComponent(iduser)}`
            })
            .then(r => r.json())
            .then(res => {
                if (res.sucesso) {
                    bootstrap.Modal.getInstance(document.getElementById('modalPC')).hide();
                    if (!<?= !empty($dataNascimento) ? 'true' : 'false' ?>) {
                        new bootstrap.Modal(document.getElementById('modalNascimento')).show();
                    }
                } else {
                    alert('Erro ao salvar resposta.');
                }
            });
    }

    function respondeNascimento() {
        const data = document.getElementById("dataNascimentoInput").value;
        const iduser = document.getElementById('iduser').value;

        if (!data) {
            alert("Por favor, informe a data de nascimento.");
            return;
        }

        fetch('config_curso1.0/ajax_updateUsuarioInfo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `campo=datanascimento_sc&valor=${encodeURIComponent(data)}&iduser=${encodeURIComponent(iduser)}`
            })
            .then(r => r.json())
            .then(res => {
                if (res.sucesso) {
                    bootstrap.Modal.getInstance(document.getElementById('modalNascimento')).hide();
                } else {
                    alert('Erro ao salvar data.');
                }
            });
    }
</script>