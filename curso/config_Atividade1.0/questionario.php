<?php
defined('BASEPATH') or die('Acesso negado');

$idAluno      = (int)($codigoUser ?? 0);
$codigoAula   = (int)($codigoaula ?? 0);
$codigocurso  = (int)($codigocurso ?? 0);
$codigomodulo = (int)($codigomodulo ?? 0);

if (!$idAluno || !$codigoAula) {
    echo '<div class="alert alert-warning">Aula ou usuário inválidos.</div>';
    return;
}

// Consulta perguntas
$q = $con->prepare("
    SELECT codigoquestionario, titulocq, tipocq, 
           COALESCE(opcaoa,'') AS opcaoa, COALESCE(opcaob,'') AS opcaob,
           COALESCE(opcaoc,'') AS opcaoc, COALESCE(opcaod,'') AS opcaod
    FROM a_curso_questionario
    WHERE idpublicacaocq = :aula AND visivelcq = 1
    ORDER BY codigoquestionario ASC
");
$q->execute([':aula' => $codigoAula]);
$perguntas = $q->fetchAll(PDO::FETCH_ASSOC);
$total = count($perguntas);

if ($total === 0) {
    echo '<div class="alert alert-info">Nenhuma pergunta disponível para esta aula.</div>';
    return;
}
?>

<style>
    .navcnum .numero-btn {
        margin: .25rem;
        min-width: 40px;
    }

    .numero-btn.ativa {
        outline: 2px solid #0d6efd;
    }

    .numero-btn.respondida {
        background: #198754;
        color: #fff;
        border-color: #198754;
    }

    .tituloPergunta {
        font-size: 1.05rem;
        margin-bottom: .75rem;
        color: #ffffff;
    }

    .botoes-navegacao {
        display: flex;
        gap: .5rem;
        margin-top: 1rem;
    }

    .pergunta {
        background: #0f1220;
        color: #fff;
        border-radius: 1rem;
        padding: 1rem;
    }

    @media (prefers-color-scheme: light) {
        .pergunta {
            background: #f8f9fa;
            color: #212529;
        }
    }
</style>

<div class="container " id="questionarioContainer">
    <!-- Barra topo: progresso -->
    <div class="d-flex align-items-center justify-content-between ">

        <!-- <div class="small text-muted">
            Aula ID: <span class="fw-semibold"><?= $codigoAula ?></span> •
            Questões: <span class="fw-semibold"><?= $total ?></span>
        </div> -->
    </div>

    <!-- Navegação superior -->
    <div class="navcnum">
        <div class="nav-numero d-flex justify-content-center mb-3 flex-wrap">
            <?php foreach ($perguntas as $i => $_): ?>
                <button type="button"
                    class="btn btn-outline-secondary numero-btn"
                    data-index="<?= $i ?>"><?= $i + 1 ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Formulário -->
    <form id="formQuestionario">
        <input type="hidden" name="idaluno" value="<?= $idAluno ?>">
        <input type="hidden" name="idaula" value="<?= $codigoAula ?>">
        <input type="hidden" name="idcurso" value="<?= $codigocurso ?>">
        <input type="hidden" name="idmodulo" value="<?= $codigomodulo ?>">

        <?php foreach ($perguntas as $i => $pergunta): ?>
            <div class="pergunta"
                style="display: <?= $i === 0 ? 'block' : 'none' ?>; background-color:#e2e2e2"
                data-index="<?= $i ?>"
                data-aos="fade-up" data-aos-duration="600">
                <h6 class="mb-2"><strong>Pergunta <?= $i + 1 ?></strong></h6>
                <div class="tituloPergunta"><?= htmlspecialchars($pergunta['titulocq']) ?></div>

                <?php if ((int)$pergunta['tipocq'] === 1): ?>
                    <!-- Dissertativa -->
                    <textarea name="resposta_<?= (int)$pergunta['codigoquestionario'] ?>"
                        class="form-control" rows="4"
                        placeholder="Digite sua resposta..."></textarea>

                <?php else: ?>
                    <!-- Múltipla escolha -->
                    <?php
                    $opcoes = [
                        'A' => $pergunta['opcaoa'],
                        'B' => $pergunta['opcaob'],
                        'C' => $pergunta['opcaoc'],
                        'D' => $pergunta['opcaod'],
                    ];
                    $qid = (int)$pergunta['codigoquestionario'];
                    $name = "resposta_{$qid}";
                    ?>
                    <?php foreach ($opcoes as $letra => $texto): if ($texto === '') continue; ?>
                        <?php $idradio = "{$letra}{$i}{$qid}"; ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" style="border: solid 1px #000000;"
                                name="<?= $name ?>" value="<?= $letra ?>" id="<?= $idradio ?>">
                            <label class="form-check-label" style="color:black" for="<?= $idradio ?>">
                                <?= htmlspecialchars($texto) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="botoes-navegacao">
                    <button type="button" class="btn btn-secondary voltar" <?= $i === 0 ? 'disabled' : '' ?>>
                        <i class="bi bi-arrow-left-circle"></i> Voltar
                    </button>

                    <?php if ($i + 1 < $total): ?>
                        <button type="button" class="btn btn-primary avancar">
                            Avançar <i class="bi bi-arrow-right-circle"></i>
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Concluir Questionário
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </form>
</div>

<!-- TOAST -->
<div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1080;">
    <div id="toastQuestionario" class="toast align-items-center text-white bg-success border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div id="toastMessage" class="toast-body">OK</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
</div>

<script>
    (function() {
        const perguntas = Array.from(document.querySelectorAll('.pergunta'));
        const navBotoes = Array.from(document.querySelectorAll('.numero-btn'));
        const form = document.getElementById('formQuestionario');

        function ativarBotaoTopo(index) {
            navBotoes.forEach((btn, i) => {
                btn.classList.toggle('ativa', i === index);
            });
        }

        // Navegações
        document.querySelectorAll('.avancar').forEach((btn, i) => {
            btn.addEventListener('click', () => showPage(i + 1));
        });
        document.querySelectorAll('.voltar').forEach((btn, i) => {
            btn.addEventListener('click', () => showPage(i - 1));
        });
        navBotoes.forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.dataset.index);
                showPage(idx);
            });
        });

        function showPage(idx) {
            if (idx < 0 || idx >= perguntas.length) return;
            perguntas.forEach((p, i) => p.style.display = (i === idx ? 'block' : 'none'));
            ativarBotaoTopo(idx);
            // Atualiza "current" no Autosave
            try {
                const key = buildKeyFromHidden();
                const st = getLocal(key);
                setLocal(key, {
                    ...st,
                    current: idx,
                    answers: st?.answers || {}
                });
            } catch (e) {}
        }

        // Marca "respondida" no topo quando usuário interagir
        form.addEventListener('change', (e) => {
            const name = e.target.name || '';
            if (!name.startsWith('resposta_')) return;
            const idx = perguntas.findIndex(p => p.contains(e.target));
            if (idx >= 0 && navBotoes[idx]) {
                navBotoes[idx].classList.add('respondida');
                ativarBotaoTopo(idx);
            }
        });

        // Validação e submit
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Checar todas as perguntas
            let todas = true;
            perguntas.forEach(p => {
                const radios = p.querySelectorAll('input[type="radio"]');
                const txt = p.querySelector('textarea');
                if (radios.length > 0) {
                    const marcado = Array.from(radios).some(r => r.checked);
                    if (!marcado) todas = false;
                } else if (txt && txt.value.trim() === '') {
                    todas = false;
                }
            });

            if (!todas) {
                showToast('Você precisa responder todas as perguntas antes de concluir.', 'danger');
                return;
            }

            const btnSubmit = form.querySelector('button[type="submit"]');
            const originalHTML = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando...';

            const formData = new FormData(form);

            fetch('config_Atividade1.0/ajax_salvar_resposta.php', {
                    method: 'POST',
                    body: formData
                })
                .then(async res => {
                    const txt = await res.text();
                    if (!res.ok) throw new Error(txt || 'Erro ao enviar');
                    // Se o backend retornar "sucesso/ok/enviado", o autosave será limpo pelo clearOnFetchMatch
                    showToast('Questionário enviado com sucesso!', 'success');
                    setTimeout(() => {
                        window.location.href = 'modulo_ViewAtividade.php';
                    }, 1200);
                })
                .catch(err => {
                    console.error(err);
                    showToast('Falha ao enviar. Tente novamente.', 'danger');
                })
                .finally(() => {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalHTML;
                });
        });

        // --------- AUTOSAVE (mínimo necessário – compatível com o seu IIFE) ---------
        const NS = 'autosave:v1';

        function buildKeyFromHidden() {
            const user = parseInt(form.querySelector('input[name="idaluno"]')?.value || '0');
            const curso = parseInt(form.querySelector('input[name="idcurso"]')?.value || '0');
            const modulo = parseInt(form.querySelector('input[name="idmodulo"]')?.value || '0');
            const aula = parseInt(form.querySelector('input[name="idaula"]')?.value || '0');
            return `${NS}:u${user}:c${curso}:m${modulo}:a${aula}:questionario`;
        }

        function getLocal(key) {
            try {
                return JSON.parse(localStorage.getItem(key) || '{}');
            } catch {
                return {};
            }
        }

        function setLocal(key, val) {
            try {
                localStorage.setItem(key, JSON.stringify(val));
            } catch {}
        }

        function delLocal(key) {
            try {
                localStorage.removeItem(key);
            } catch {}
        }
        const debounce = (fn, ms = 250) => {
            let t;
            return (...a) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...a), ms);
            }
        };

        function serializeForm(el) {
            const data = {};
            el.querySelectorAll('input, textarea, select').forEach((el, i) => {
                if (el.disabled) return;
                const type = (el.type || '').toLowerCase();
                if (type === 'file' || type === 'password') return;
                const k = el.name || el.id || `__idx_${i}`;
                if (type === 'checkbox') data[k] = !!el.checked;
                else if (type === 'radio') {
                    if (el.checked) data[k] = el.value;
                } else data[k] = el.value;
            });
            return data;
        }

        function restoreForm(el, data) {
            if (!data) return;
            el.querySelectorAll('input, textarea, select').forEach((el, i) => {
                const type = (el.type || '').toLowerCase();
                if (type === 'file' || type === 'password') return;
                const k = el.name || el.id || `__idx_${i}`;
                if (!(k in data)) return;
                if (type === 'checkbox') el.checked = !!data[k];
                else if (type === 'radio') el.checked = (el.value === data[k]);
                else el.value = data[k];
            });
        }

        // Restaurar (respostas + página + marcação de respondidas)
        const KEY = buildKeyFromHidden();
        const saved = getLocal(KEY);
        if (saved?.answers) {
            restoreForm(form, saved.answers);
            // Marca botões respondidos
            perguntas.forEach((p, i) => {
                const hasRadio = p.querySelectorAll('input[type="radio"]').length > 0;
                const answered = hasRadio ?
                    p.querySelector('input[type="radio"]:checked') :
                    (p.querySelector('textarea') && p.querySelector('textarea').value.trim() !== '');
                if (answered && navBotoes[i]) navBotoes[i].classList.add('respondida');
            });
        }
        const startIdx = Number.isInteger(saved?.current) ? Math.max(0, Math.min(saved.current, perguntas.length - 1)) : 0;
        showPage(startIdx);

        // Salvar rascunho continuamente
        const saver = debounce(() => {
            const answers = serializeForm(form);
            const st = getLocal(KEY);
            setLocal(KEY, {
                ...st,
                answers
            });
        }, 250);
        form.addEventListener('input', saver, true);
        form.addEventListener('change', saver, true);

        // Limpeza automática do rascunho quando backend confirma
        const _fetch = window.fetch.bind(window);
        window.fetch = function(...args) {
            return _fetch(...args).then(async res => {
                try {
                    const clone = res.clone();
                    const txt = await clone.text();
                    if (res.ok && /sucesso|success|ok|enviado/i.test(txt)) {
                        delLocal(KEY);
                    }
                } catch (_) {}
                return res;
            });
        };

        // Toast
        window.showToast = function(mensagem, cor = 'success') {
            const toast = document.getElementById('toastQuestionario');
            const toastMsg = document.getElementById('toastMessage');
            toast.classList.remove('bg-danger', 'bg-success', 'bg-warning', 'bg-info');
            toast.classList.add(`bg-${cor}`);
            toastMsg.textContent = mensagem;
            const t = new bootstrap.Toast(toast);
            t.show();
        };
    })();
</script>