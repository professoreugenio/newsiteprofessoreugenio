<!-- Barra Lateral Direita -->
<?php
$titulo = $titulo ?? '';
$codigomodulo = $codigomodulo ?? '';
$codigoaula = $codigoaula ?? '';
$ordemAaula = $ordemAaula ?? '';
$ordem = $ordem ?? $ordemAaula;
?>

<?php
if ($codigoUser == 1):
    require 'config_default1.0/require_SidebarLateral1.0.php';
else:
    require 'config_default1.0/require_SidebarLateral1.0.php';
// require 'config_default1.0/require_SidebarLateral.php';
endif;
?>

<?php if ($codigoUser == 1):  ?>
<?php endif; ?>

<div class="modal fade" id="modalAnexos" tabindex="-1" aria-labelledby="modalAnexosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg rounded-4 border-0" style="background-color: #1f2127;">
            <div class="modal-header" style="background-color: #343a40; color: white; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                <h5 class="modal-title d-flex align-items-center gap-2" id="modalAnexosLabel">
                    <i class="bi bi-folder2-open"></i> Anexos Disponíveis
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group list-group-flush">
                    <?php if (!empty($fetchContAnexo) && is_array($fetchContAnexo)): ?>
                        <?php foreach ($fetchContAnexo as $key => $value):
                            $titulopa  = $value['titulopa'];
                            $extensao  = strtolower($value['extpa']);
                            $pasta     = $value['pastapa'];
                            $anexo     = $value['anexopa'];

                            $url = ($value['urlpa'] == "#")
                                ? "../anexos/publicacoes/{$pasta}/{$anexo}"
                                : $value['urlpa'];

                            $isImage = in_array($extensao, ['jpg', 'jpeg', 'png', 'gif']);

                            if ($isImage) {
                                $icone = '<a href="' . $url . '" data-lightbox="anexos" data-title="' . htmlspecialchars($titulopa) . '">
                                    <img src="' . $url . '" alt="anexo" class="rounded shadow-sm me-2" style="width: 45px; height: 45px; object-fit: cover;">
                                </a>';
                            } else {
                                $icone = match ($extensao) {
                                    'pdf'           => '<i class="bi bi-file-earmark-pdf text-danger me-2 fs-5"></i>',
                                    'doc', 'docx'    => '<i class="bi bi-file-earmark-word text-primary me-2 fs-5"></i>',
                                    'xls', 'xlsx'    => '<i class="bi bi-file-earmark-excel text-success me-2 fs-5"></i>',
                                    'ppt', 'pptx'    => '<i class="bi bi-file-earmark-ppt-fill text-warning me-2 fs-5"></i>',
                                    'zip', 'rar'     => '<i class="bi bi-file-earmark-zip-fill text-secondary me-2 fs-5"></i>',
                                    default          => '<i class="bi bi-file-earmark text-secondary me-2 fs-5"></i>'
                                };
                            }
                        ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2 bg-dark text-light">
                                <div class="d-flex align-items-center">
                                    <?= $icone ?>
                                    <span class="fw-semibold"><?= htmlspecialchars($titulopa, ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <a href="<?= $url ?>" class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1" target="_blank" download title="Baixar <?= htmlspecialchars($titulopa) ?>">
                                    <i class="bi bi-download"></i> Baixar
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="modal-footer bg-secondary-subtle">
                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>



<!-- MODAL -->

<div class="modal fade" id="modalPublicaAula" tabindex="-1" aria-labelledby="modalPublicaAulaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formLiberaAula" class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalPublicaAulaLabel">Liberar Aula</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">

                <div class="mb-3">
                    <label for="dataLib" class="form-label">Data da Liberação</label>
                    <div class="input-group">
                        <input type="date" class="form-control" id="dataLib" value="<?= $data ?>" name="datasam" required>
                        <button type="button" class="btn btn-outline-secondary" id="btnAtualizarLista" title="Atualizar lista">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>

                <!-- Lista de liberações -->
                <div id="listaLiberacoes" class="mt-3 border rounded bg-light text-dark p-2 small">
                    <div class="text-muted">Carregando registros...</div>
                </div>



                <!-- Campos ocultos -->
                <input type="hidden" name="idde" id="idde" value="<?= $codigoUser; ?>">
                <input type="hidden" name="tiposam" value="5"> <!-- Valor fixo -->
                <input type="hidden" name="idmodulosam" id="idmodulosam" value="<?= $codigomodulo ?>">
                <input type="hidden" name="idturmasam" id="idturmasam" value="<?= $idTurma ?>">
                <input type="hidden" name="ordem" id="ordem" value="<?= $ordem ?>">
                <input type="hidden" name="idartigo_sma" id="idartigo_sma" value="<?= $codigoaula ?>">
                <input type="hidden" name="titulo" id="titulo" value="<?= htmlspecialchars($titulo) ?>">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-info w-100" id="btnEnviarLiberacao">
                    <i class="bi bi-send-check me-2"></i>Confirmar Liberação
                </button>
            </div>
        </form>
    </div>
</div>


<script>
    // 🔁 Função para carregar liberações existentes
    function carregarListaLiberacoes(data, idturma, idartigo) {
        if (!data || !idturma || !idartigo) {
            console.warn("Parâmetros ausentes para carregar liberações.");
            return;
        }

        const container = document.getElementById("listaLiberacoes");
        container.innerHTML = `<div class="text-muted">Carregando registros...</div>`;

        fetch("config_aulas1.0/ajax_listarLiberacoes.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `data=${encodeURIComponent(data)}&idturmasam=${encodeURIComponent(idturma)}&ordem=${encodeURIComponent(ordem)}&idartigo_sma=${encodeURIComponent(idartigo)}`
            })
            .then(res => res.json())
            .then(lista => {
                if (lista.length === 0) {
                    container.innerHTML = `<div class="text-muted">Nenhuma liberação registrada nesta data.</div>`;
                    return;
                }

                container.innerHTML = lista.map(item => `
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <span>${item.dataformatada} - ${item.msgsam || 'Sem mensagem'}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="excluirLiberacao(${item.codigomsg})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `).join('');
            })
            .catch(() => {
                container.innerHTML = `<div class="text-danger">Erro ao carregar registros.</div>`;
            });
    }

    // ❌ Função para excluir uma liberação
    function excluirLiberacao(id, e) {
        if (e) e.preventDefault(); // Impede submit/reload

        if (!confirm("Deseja realmente excluir esta liberação?")) return;

        fetch("config_aulas1.0/ajax_excluirLiberacao.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${encodeURIComponent(id)}`
            })
            .then(res => res.json())
            .then(json => {
                if (json.sucesso) {
                    const data = document.getElementById("dataLib").value;
                    const idturma = document.getElementById("idturmasam").value;
                    const idartigo = document.getElementById("idartigo_sma").value;
                    carregarListaLiberacoes(data, idturma, idartigo);
                } else {
                    alert("Erro ao excluir: " + (json.mensagem || "Desconhecido"));
                }
            })
            .catch(() => {
                alert("Erro na requisição ao excluir.");
            });
    }


    // 🚀 Script principal ao carregar a página
    document.addEventListener("DOMContentLoaded", function() {
        const modal = document.getElementById("modalPublicaAula");
        const form = document.getElementById("formLiberaAula");

        // Abertura do modal com preenchimento
        document.querySelectorAll("#btpublicalicao").forEach(btn => {
            btn.addEventListener("click", function() {
                document.getElementById("idturmasam").value = this.dataset.idturma;
                document.getElementById("idmodulosam").value = this.dataset.idmodulo;
                document.getElementById("idartigo_sma").value = this.dataset.idartigo;

                const hoje = document.getElementById("dataLib").value;


                carregarListaLiberacoes(hoje, this.dataset.idturma, this.dataset.idartigo);
            });
        });

        // Botão manual de atualizar lista
        document.getElementById("btnAtualizarLista").addEventListener("click", function() {
            const data = document.getElementById("dataLib").value;
            const idturma = document.getElementById("idturmasam").value;
            const idartigo = document.getElementById("idartigo_sma").value;

            if (data && idturma && idartigo) {
                carregarListaLiberacoes(data, idturma, idartigo);
            }
        });

        // Clique no botão de envio (não usa submit direto)
        const btnEnviar = document.querySelector("#formLiberaAula button[type='submit']");

        // Clique no botão de envio (por ID)
        document.getElementById("btnEnviarLiberacao").onclick = function(e) {
            e.preventDefault();

            const form = document.getElementById("formLiberaAula");
            const formData = new FormData(form);
            const btn = this;

            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Enviando...`;

            fetch("config_aulas1.0/ajax_insertLiberacaoAula.php", {
                    method: "POST",
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.sucesso) {
                        btn.innerHTML = `<i class="bi bi-check-circle me-2"></i>Liberação registrada`;

                        const data = document.getElementById("dataLib").value;
                        const idturma = document.getElementById("idturmasam").value;
                        const idartigo = document.getElementById("idartigo_sma").value;

                        carregarListaLiberacoes(data, idturma, idartigo);

                        setTimeout(() => {
                            btn.disabled = false;
                            btn.innerHTML = `<i class="bi bi-send-check me-2"></i>Confirmar Liberação`;

                        }, 1000);
                    } else {
                        alert("Erro ao registrar: " + res.mensagem);
                        btn.disabled = false;
                        btn.innerHTML = `<i class="bi bi-send-check me-2"></i>Confirmar Liberação`;
                    }
                })
                .catch(() => {
                    alert("Erro na requisição.");
                    btn.disabled = false;
                    btn.innerHTML = `<i class="bi bi-send-check me-2"></i>Confirmar Liberação`;
                });
        };

    });
</script>