<div class="modal fade" id="modalCaderno" tabindex="-1" aria-labelledby="modalCadernoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg draggable-modal caderno-modal">
        <div class="modal-content">
            <div class="modal-header cursor-move" id="dragHandleCaderno">
                <h5 class="modal-title" id="modalCadernoLabel" style="color:#000000">
                    <i class="bi bi-journal-text me-2"></i> Caderno de anotações
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <!-- Campos ocultos (ajuste conforme seu contexto) -->
                <input type="hidden" id="iduserCaderno" value="<?= $codigoUser ?? '' ?>">
                <input type="hidden" id="idturmaCaderno" value="<?= $idTurma ?? '' ?>">
                <input type="hidden" id="idmoduloCaderno" value="<?= $codigomodulo ?? '' ?>">
                <input type="hidden" id="idartigoCaderno" value="<?= $codigoaula ?? '' ?>">

                <!-- Editor Summernote -->
                <textarea id="anotacaoEditor"></textarea>
            </div>
            <div class="modal-footer">
                <button id="btnSalvarAnotacao" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Salvar anotação
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Inicializa Summernote quando o modal abrir
    const modalCadernoEl = document.getElementById('modalCaderno');
    modalCadernoEl.addEventListener('shown.bs.modal', function() {
        // Evita reinicialização
        if (!$('#anotacaoEditor').next('.note-editor').length) {
            $('#anotacaoEditor').summernote({
                placeholder: 'Escreva suas anotações aqui...',
                height: 260,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['view', ['undo', 'redo']]
                ]
            });
        }

        // (Opcional) Carregar anotação existente
        const payloadLoad = new URLSearchParams({
            iduser: document.getElementById('iduserCaderno').value,
            idturma: document.getElementById('idturmaCaderno').value,
            idmodulo: document.getElementById('idmoduloCaderno').value,
            idartigo: document.getElementById('idartigoCaderno').value
        });

        fetch('caderno1.0/ajax_carregarAnotacao.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: payloadLoad.toString()
            })
            .then(r => r.ok ? r.json() : null)
            .then(json => {
                if (json && json.sucesso && json.html) {
                    $('#anotacaoEditor').summernote('code', json.html);
                }
            })
            .catch(() => {});
    });

    // Salvar anotação
    document.getElementById('btnSalvarAnotacao').addEventListener('click', function() {
        const btn = this;
        const html = $('#anotacaoEditor').summernote('code');

        const data = new FormData();
        data.append('iduser', document.getElementById('iduserCaderno').value);
        data.append('idturma', document.getElementById('idturmaCaderno').value);
        data.append('idmodulo', document.getElementById('idmoduloCaderno').value);
        data.append('idartigo', document.getElementById('idartigoCaderno').value);
        data.append('anotacao', html);

        btn.disabled = true;
        const old = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Salvando...';

        fetch('caderno1.0/ajax_salvarAnotacao.php', {
                method: 'POST',
                body: data
            })
            .then(r => r.json())
            .then(res => {
                if (res.sucesso) {
                    btn.innerHTML = '<i class="bi bi-check2-circle me-2"></i> Salvo!';
                    setTimeout(() => {
                        btn.innerHTML = old;
                        btn.disabled = false;
                    }, 900);
                } else {
                    alert('Não foi possível salvar a anotação.');
                    btn.innerHTML = old;
                    btn.disabled = false;
                }
            })
            .catch(() => {
                alert('Erro na requisição.');
                btn.innerHTML = old;
                btn.disabled = false;
            });
    });

    // Modal arrastável (sem jQuery UI)
    (function enableDraggableModal(modalId, handleId) {
        const modal = document.querySelector(modalId + ' .modal-dialog');
        const handle = document.getElementById(handleId);
        if (!modal || !handle) return;

        let isDown = false,
            startX = 0,
            startY = 0,
            startLeft = 0,
            startTop = 0;

        const onMouseDown = (e) => {
            // somente botão esquerdo
            if (e.button !== 0) return;
            isDown = true;
            const rect = modal.getBoundingClientRect();
            startX = e.clientX;
            startY = e.clientY;
            startLeft = rect.left;
            startTop = rect.top;
            document.body.style.userSelect = 'none';
        };

        const onMouseMove = (e) => {
            if (!isDown) return;
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            modal.style.left = (startLeft + dx) + 'px';
            modal.style.top = (startTop + dy) + 'px';
        };

        const onMouseUp = () => {
            isDown = false;
            document.body.style.userSelect = '';
        };

        handle.addEventListener('mousedown', onMouseDown);
        window.addEventListener('mousemove', onMouseMove);
        window.addEventListener('mouseup', onMouseUp);

        // Ajusta posição inicial ao abrir
        modalCadernoEl.addEventListener('shown.bs.modal', () => {
            const vw = window.innerWidth;
            const vh = window.innerHeight;
            const rect = modal.getBoundingClientRect();
            // centraliza aproximadamente
            modal.style.left = Math.max(10, (vw - rect.width) / 2) + 'px';
            modal.style.top = Math.max(10, (vh - rect.height) * 0.15) + 'px';
        });
    })('#modalCaderno', 'dragHandleCaderno');
</script>