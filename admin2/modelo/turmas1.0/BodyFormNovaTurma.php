<?php

/**
 * BodyFormNovaTurma.php
 * - Formulário para criar nova turma de um curso
 * - Padrões: Bootstrap 5+, AOS, rótulos internos (floating labels)
 * - NÃO incluir bootstrapping PHP (defines/require), pois é módulo.
 *
 * Requisitos:
 * - $con (PDO ou mysqli compatível com sua função de execução)
 * - Parâmetro GET 'id' com o ID do curso (codcursost)
 */

$idCurso = isset($_GET['id']) ? trim((string)$_GET['id']) : '';
if ($idCurso === '') {
    echo '<div class="alert alert-warning mb-3">ID do curso não informado.</div>';
    return;
}

// (Opcional) Buscar nome do curso para exibir no topo
$nomeCurso = '';
try {
    if ($con instanceof PDO) {
        $q = $con->prepare("SELECT nomecurso FROM new_sistema_cursos WHERE codigocursos = :id LIMIT 1");
        $q->bindValue(':id', (int)$idCurso, PDO::PARAM_INT);
        $q->execute();
        $nomeCurso = (string)($q->fetchColumn() ?: '');
    } else {
        // Caso utilize mysqli, adapte conforme seu wrapper.
    }
} catch (\Throwable $e) { /* silencioso */
}
?>

<div class="card shadow-sm" data-aos="fade-up">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="fw-semibold">
            Nova Turma <?= $nomeCurso ? '— <span class="text-muted">' . htmlspecialchars($nomeCurso) . '</span>' : '' ?>
        </div>
        <a href="cursos_turmas.php?id=<?= htmlspecialchars($idCurso) ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card-body">
        <form id="formNovaTurma" class="row g-3">

            <!-- ID do Curso (hidden) -->
            <input type="hidden" name="codcursost" value="<?= htmlspecialchars($idCurso) ?>">

            <!-- CHAVE (gerada no submit) -->
            <input type="hidden" name="chave" id="chaveAuto">

            <!-- Ordem -->
            <div class="col-12 col-sm-4">
                <div class="form-floating">
                    <input type="number" class="form-control" id="ordemct" name="ordemct" placeholder="Ordem" value="1" min="1">
                    <label for="ordemct">Ordem</label>
                </div>
            </div>

            <!-- Nome da Turma -->
            <div class="col-12 col-md-8">
                <div class="form-floating">
                    <input type="text" class="form-control" id="nometurma" name="nometurma" placeholder="Nome da Turma" required>
                    <label for="nometurma">Nome da Turma *</label>
                </div>
            </div>

            <!-- Nome do Professor -->
            <div class="col-12 col-md-6">
                <div class="form-floating">
                    <input type="text" class="form-control" id="nomeprofessor" name="nomeprofessor" placeholder="Nome do Professor" required>
                    <label for="nomeprofessor">Nome do Professor *</label>
                </div>
            </div>

            <!-- Link do WhatsApp -->
            <div class="col-12 col-md-6">
                <div class="form-floating">
                    <input type="url" class="form-control" id="linkwhatsapp" name="linkwhatsapp" placeholder="Link do WhatsApp" value="#">
                    <label for="linkwhatsapp">Link do WhatsApp</label>
                </div>
            </div>

            <!-- Datas -->
            <div class="col-12 col-md-3">
                <div class="form-floating">
                    <input type="date" class="form-control" id="datainiciost" name="datainiciost" placeholder="Data Início">
                    <label for="datainiciost">Data Início</label>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-floating">
                    <input type="date" class="form-control" id="datafimst" name="datafimst" placeholder="Data Fim">
                    <label for="datafimst">Data Fim</label>
                </div>
            </div>

            <!-- Horários -->
            <div class="col-12 col-md-3">
                <div class="form-floating">
                    <input type="time" class="form-control" id="horainiciost" name="horainiciost" placeholder="Hora Início">
                    <label for="horainiciost">Hora Início</label>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-floating">
                    <input type="time" class="form-control" id="horafimst" name="horafimst" placeholder="Hora Fim">
                    <label for="horafimst">Hora Fim</label>
                </div>
            </div>

            <!-- Ano da Turma -->
            <div class="col-12 col-sm-4">
                <div class="form-floating">
                    <input type="number" class="form-control" id="ano_turma" name="ano_turma" placeholder="Ano da Turma" min="2000" max="2099" value="<?= date('Y') ?>">
                    <label for="ano_turma">Ano da Turma</label>
                </div>
            </div>

            <!-- Flags (switches) -->
            <div class="col-12 col-sm-8 d-flex align-items-center gap-4 flex-wrap">
                <!-- Cada switch envia 1/0 -->
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="visivelst_sw">
                    <label class="form-check-label" for="visivelst_sw">Visível</label>
                    <input type="hidden" name="visivelst" id="visivelst" value="0">
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="comercialt_sw">
                    <label class="form-check-label" for="comercialt_sw">Comercial</label>
                    <input type="hidden" name="comercialt" id="comercialt" value="0">
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="institucional_sw">
                    <label class="form-check-label" for="institucional_sw">Institucional</label>
                    <input type="hidden" name="institucional" id="institucional" value="0">
                </div>
            </div>

            <!-- Descritivo -->
            <div class="col-12">
                <div class="form-floating">
                    <textarea class="form-control" id="texto" name="texto" placeholder="Detalhes/descrição da turma" style="height: 140px"></textarea>
                    <label for="texto">Descrição da turma</label>
                </div>
            </div>

            <!-- Ações -->
            <div class="col-12 d-flex justify-content-end gap-2">
                <button type="reset" class="btn btn-outline-secondary">
                    <i class="bi bi-eraser"></i> Limpar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Cadastrar turma
                </button>
            </div>

            <!-- Ajuda -->
            <div class="col-12">
                <small class="text-muted">Campos marcados com * são obrigatórios.</small>
            </div>
        </form>

        <!-- Toast central -->
        <div class="position-fixed top-50 start-50 translate-middle" style="z-index: 1080;">
            <div id="toastNovaTurma" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body" id="toastNovaTurmaMsg">Enviando…</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('formNovaTurma');
        const toastEl = document.getElementById('toastNovaTurma');
        const toastMsg = document.getElementById('toastNovaTurmaMsg');
        const bsToast = typeof bootstrap !== 'undefined' ? new bootstrap.Toast(toastEl) : null;

        // Switch -> Hidden 1/0
        const bindSwitch = (swId, hiddenId) => {
            const sw = document.getElementById(swId);
            const hid = document.getElementById(hiddenId);
            if (!sw || !hid) return;
            const sync = () => hid.value = sw.checked ? '1' : '0';
            sw.addEventListener('change', sync);
            sync();
        };
        bindSwitch('visivelst_sw', 'visivelst');
        bindSwitch('comercialt_sw', 'comercialt');
        bindSwitch('institucional_sw', 'institucional');

        // Gera CHAVE = yyyymmdd + time()
        function gerarChave() {
            const now = new Date();
            const y = now.getFullYear().toString();
            const m = String(now.getMonth() + 1).padStart(2, '0');
            const d = String(now.getDate()).padStart(2, '0');
            const epoch = Math.floor(Date.now() / 1000); // time()
            return y + m + d + epoch;
        }

        // Preenche ano_turma automaticamente se vazio e houver datainiciost
        const dtInicio = document.getElementById('datainiciost');
        const anoTurma = document.getElementById('ano_turma');
        if (dtInicio && anoTurma) {
            dtInicio.addEventListener('change', () => {
                if (!anoTurma.value && dtInicio.value) {
                    anoTurma.value = dtInicio.value.slice(0, 4);
                }
            });
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Valida mínimos
            const nomeTurma = document.getElementById('nometurma').value.trim();
            const nomeProfessor = document.getElementById('nomeprofessor').value.trim();
            if (!nomeTurma || !nomeProfessor) {
                if (bsToast) {
                    toastMsg.textContent = 'Preencha os campos obrigatórios (Nome da Turma e Nome do Professor).';
                    bsToast.show();
                } else {
                    alert('Preencha os campos obrigatórios (Nome da Turma e Nome do Professor).');
                }
                return;
            }

            // Chave
            document.getElementById('chaveAuto').value = gerarChave();

            const fd = new FormData(form);

            try {
                if (bsToast) {
                    toastMsg.textContent = 'Cadastrando turma…';
                    bsToast.show();
                }

                const resp = await fetch('turmas1.0/ajax_cadastrarTurma.php', {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await resp.json().catch(() => ({}));

                if (!resp.ok || !data || data.ok !== true) {
                    const msg = (data && data.msg) ? data.msg : 'Não foi possível cadastrar a turma.';
                    if (bsToast) {
                        toastMsg.textContent = msg;
                        bsToast.show();
                    } else {
                        alert(msg);
                    }
                    return;
                }

                // Sucesso -> redireciona
                const idCurso = <?= json_encode($idCurso) ?>;
                const idTurma = data.idturma || '';
                const encTm = data.enc_tm || ''; // espere que o backend devolva a versão criptografada

                const url = 'cursos_turmasEditar.php?id=' + encodeURIComponent(idCurso) +
                    '&tm=' + encodeURIComponent(encTm || idTurma);

                if (bsToast) {
                    toastMsg.textContent = 'Turma cadastrada com sucesso! Redirecionando…';
                    bsToast.show();
                }
                window.location.href = url;

            } catch (err) {
                if (bsToast) {
                    toastMsg.textContent = 'Erro de comunicação. Tente novamente.';
                    bsToast.show();
                } else {
                    alert('Erro de comunicação. Tente novamente.');
                }
            }
        });
    })();
</script>

<style>
    /* Ajustes sutis de espaçamento vertical (preferência do Eugênio) */
    #formNovaTurma .form-floating>.form-control,
    #formNovaTurma .form-floating>.form-select {
        padding-top: 1.05rem;
        padding-bottom: 0.55rem;
        min-height: 3.1rem;
    }

    #formNovaTurma .form-floating>label {
        padding: .35rem .75rem;
    }

    /* Card visual clean */
    #formNovaTurma .btn.btn-primary {
        border-radius: .65rem;
    }

    #formNovaTurma .btn.btn-outline-secondary {
        border-radius: .65rem;
    }
</style>