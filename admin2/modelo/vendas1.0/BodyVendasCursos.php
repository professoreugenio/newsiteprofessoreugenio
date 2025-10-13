<?php

/**
 * BodyVendasLista.php
 * Lista de vendas com filtros (pendentes/confirmados/atuais), WhatsApp via modal,
 * confirmar pagamento via AJAX e EXCLUIR venda com confirmação.
 * Usa $con disponível na página principal.
 */

if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('formatBRL')) {
    function formatBRL($v)
    {
        return 'R$ ' . number_format((float)$v, 2, ',', '.');
    }
}
if (!function_exists('primeiroESobrenome')) {
    function primeiroESobrenome($nomeCompleto)
    {
        $nomeCompleto = trim((string)$nomeCompleto);
        if ($nomeCompleto === '') return '';
        $p = preg_split('/\s+/', $nomeCompleto);
        if (count($p) === 1) return $p[0];
        return $p[0] . ' ' . $p[count($p) - 1];
    }
}
if (!function_exists('whatsLink')) {
    function whatsLink($celular)
    {
        $nums = preg_replace('/\D+/', '', (string)$celular);
        if ($nums && substr($nums, 0, 2) !== '55') $nums = '55' . $nums;
        return $nums ? ('https://wa.me/' . $nums) : '#';
    }
}

/* ------------------ Filtros de visualização ------------------ */
$view = isset($_GET['view']) ? strtolower(trim($_GET['view'])) : 'pendentes';
$titulo = 'Vendas pendentes de confirmação';
$where  = '(v.statussv IS NULL OR v.statussv <> 1)'; // padrão

if ($view === 'confirmados') {
    $titulo = 'Pagamentos confirmados';
    $where  = 'v.statussv = 1';
} elseif ($view === 'atuais') {
    $titulo = 'Vendas de hoje';
    $where  = 'DATE(v.datacomprasv) = CURDATE()';
}

/* ------------------ Consulta ------------------ */
$limit = 300;

$sql = "
SELECT
  v.codigovendas,
  v.idcursosv,
  v.chaveturmasv,
  v.idalunosv,
  v.chaveafiliadosv,
  v.valorvendasv,
  v.datacomprasv,
  v.horacomprasv,
  v.statussv,
  v.tipopagamentosv,

  c.nomecurso,
  a.email AS email_aluno,
  c.bgcolor,
  a.codigocadastro,
  a.senha,
  a.nome         AS nome_aluno,
  a.celular      AS cel_aluno,

  af.idusuarioSA AS id_afiliado,
  afc.nome       AS nome_afiliado
FROM a_site_vendas v
LEFT JOIN new_sistema_cursos c  ON c.codigocursos     = v.idcursosv
LEFT JOIN new_sistema_cadastro a ON a.codigocadastro  = v.idalunosv
LEFT JOIN a_site_afiliados_chave af
       ON (af.codigochaveafiliados = v.chaveafiliadosv OR af.chaveafiliadoSA = v.chaveafiliadosv)
LEFT JOIN new_sistema_cadastro afc ON afc.codigocadastro = af.idusuarioSA
WHERE {$where}
ORDER BY v.datacomprasv DESC, v.horacomprasv DESC
LIMIT :lim
";

$stmt = $con->prepare($sql);
$stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
$stmt->execute();
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .vendas-wrap {
        max-width: 1400px;
        margin: 0 auto;
    }

    .venda-item {
        border: 1px solid rgba(17, 34, 64, .08);
        border-left: 6px solid #00BB9C;
        border-radius: 16px;
        padding: 14px 16px;
        background: #fff;
        transition: transform .15s ease, box-shadow .15s ease, opacity .2s ease;
    }

    .venda-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(17, 34, 64, .08);
    }

    .venda-left {
        gap: .75rem;
    }

    .venda-meta {
        font-size: .9rem;
        color: #6b7280;
    }

    .venda-curso {
        font-weight: 700;
        line-height: 1.15;
    }

    .venda-aluno,
    .venda-valor {
        font-weight: 600;
    }

    .venda-valor {
        white-space: nowrap;
    }

    .venda-afiliado a {
        text-decoration: none;
    }

    .dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #00BB9C;
        margin: 0 8px;
        opacity: .7;
    }

    .venda-pagto i {
        font-size: 1.1rem;
    }

    .toolbar .btn {
        min-width: 180px;
    }

    @media (max-width: 768px) {
        .venda-right {
            margin-top: .75rem;
        }
    }
</style>

<div class="vendas-wrap" data-aos="fade-up" data-aos-delay="100">

    <!-- Toolbar de filtros -->
    <div class="toolbar d-flex flex-wrap align-items-center gap-2 mb-3">
        <a href="?view=confirmados" class="btn btn-outline-success <?= $view === 'confirmados' ? 'active' : '' ?>">
            <i class="bi bi-check2-circle me-1"></i> Pagamentos confirmados
        </a>
        <a href="?view=atuais" class="btn btn-outline-primary <?= $view === 'atuais' ? 'active' : '' ?>">
            <i class="bi bi-calendar-date me-1"></i> Vendas atuais (hoje)
        </a>
        <a href="?view=pendentes" class="btn btn-outline-warning <?= $view === 'pendentes' ? 'active' : '' ?>">
            <i class="bi bi-hourglass-split me-1"></i> Pendentes
        </a>
    </div>

    <!-- Cabeçalho -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="fw-bold fs-5" style="color:#112240;"><?= e($titulo); ?></div>
        <span class="badge bg-success-subtle text-success border border-success-subtle">
            <?= count($vendas); ?> registros
        </span>
    </div>

    <div id="toastArea"></div>

    <div class="vstack gap-2" id="listaVendas">
        <?php if (!$vendas): ?>
            <div class="alert alert-info mb-0">Nenhum registro encontrado para este filtro.</div>
        <?php else: ?>
            <?php foreach ($vendas as $row):
                $dataHora = '';
                if (!empty($row['datacomprasv'])) {
                    $dataFmt = date('d/m/Y', strtotime($row['datacomprasv']));
                    $horaFmt = !empty($row['horacomprasv']) ? date('H:i', strtotime($row['horacomprasv'])) : '00:00';
                    $dataHora = $dataFmt . ' ' . $horaFmt;
                }

                $decSenha = encrypt($row['senha'], $action = 'd');
                $exp = explode('&', $decSenha);
                $email = $exp[0];
                $senha = $exp[1];
                $curso  = $row['nomecurso'] ?? '—';
                $aluno  = primeiroESobrenome($row['nome_aluno'] ?? '—');
                $cel    = $row['cel_aluno'] ?? '';
                $whats  = whatsLink($cel);
                $idCursov   = $row['idcursosv'] ?? '';
                $encIdCursov = encrypt($idCursov, $action = 'e');
                $encIdUsuario = encrypt($row['codigocadastro'], $action = 'e');

                $temAf  = !empty($row['chaveafiliadosv']) && !empty($row['nome_afiliado']);
                $afNome = $row['nome_afiliado'] ?? '';
                $linkAfiliado = 'afiliadoPerfil.php?af=' . urlencode((string)$row['chaveafiliadosv']);

                // Ícone tipo de pagamento
                $tipo = strtolower(trim($row['tipopagamentosv'] ?? ''));
                $iconePagto = '<span class="text-muted">—</span>';
                if ($tipo === 'pix') {
                    $iconePagto = '<i class="bi bi-qr-code text-success" title="Pagamento via Pix"></i> <span class="text-success">Pix</span>';
                } elseif ($tipo === 'cartão' || $tipo === 'cartao') {
                    $iconePagto = '<i class="bi bi-credit-card-2-front text-primary" title="Pagamento via Cartão"></i> <span class="text-primary">Cartão</span>';
                }
            ?>
                <div class="venda-item d-flex flex-column flex-md-row align-items-md-center justify-content-between"
                    data-aos="fade-up"
                    data-idvenda="<?= (int)$row['codigovendas']; ?>">

                    <!-- Lado Esquerdo -->
                    <div class="venda-left d-flex flex-column flex-lg-row align-items-lg-center">
                        <div class="venda-meta me-lg-3">
                            <i class="bi bi-calendar2-check me-1"></i><?= e($dataHora); ?>
                        </div>
                        <span class="dot d-none d-lg-inline"></span>

                        <div class="venda-curso me-lg-3">
                            <a href="cursos_turmas.php?id=<?= e($encIdCursov); ?>">
                                <i class="bi bi-journal-code me-1"></i><?= e($curso); ?>
                            </a>
                        </div>
                        <span class="dot d-none d-lg-inline"></span>

                        <div class="venda-aluno me-lg-3">
                            <a href="alunoTurmas.php?idUsuario=<?= e($encIdUsuario); ?>">
                                <i class="bi bi-person-circle me-1"></i><?= e($aluno); ?>
                            </a>
                        </div>
                        <span class="dot d-none d-lg-inline"></span>

                        <!-- Botão Mensagens (abre modal) -->
                        <?php
                        $nomeAlunoCompleto = (string)($row['nome_aluno'] ?? '');
                        $emailAluno        = (string)($row['email_aluno'] ?? '');
                        $nomeCurso         = (string)($row['nomecurso'] ?? '');
                        $nomePlano         = (string)($row['nome_plano'] ?? '');
                        $senhaAluno        = $senha ?? '';
                        ?>
                        <div class="venda-meta me-lg-3">
                            <?php if ($cel): ?>
                                <button
                                    type="button"
                                    class="btn btn-outline-success btn-sm btn-wpp-msg"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalWppMensagens"
                                    data-cel="<?= e($cel); ?>"
                                    data-nome="<?= e($nomeAlunoCompleto); ?>"
                                    data-curso="<?= e($nomeCurso); ?>"
                                    data-email="<?= e($emailAluno); ?>"
                                    data-senha="<?= e($senhaAluno); ?>"
                                    data-plano="<?= e($nomePlano); ?>"
                                    title="Mensagens rápidas no WhatsApp">
                                    <i class="bi bi-whatsapp me-1"></i><i class="bi bi-telephone-outbound ms-1 me-1"></i><?= e($cel); ?>
                                </button>
                            <?php else: ?>
                                <span class="text-muted">sem celular</span>
                            <?php endif; ?>
                        </div>
                        <span class="dot d-none d-lg-inline"></span>

                        <div class="venda-valor me-lg-3">
                            <i class="bi bi-cash-coin me-1"></i><?= e(formatBRL($row['valorvendasv'])); ?>
                        </div>

                        <span class="dot d-none d-lg-inline"></span>
                        <div class="venda-pagto me-lg-3">
                            <?= $iconePagto; ?>
                        </div>

                        <?php if ($temAf): ?>
                            <span class="dot d-none d-lg-inline"></span>
                            <div class="venda-afiliado">
                                <i class="bi bi-people-fill me-1"></i>
                                Afiliado:
                                <a href="<?= e($linkAfiliado); ?>" class="link-dark fw-semibold" title="Ver perfil do afiliado">
                                    <?= e(primeiroESobrenome($afNome)); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Lado Direito -->
                    <div class="venda-right d-flex gap-2">
                        <?php if ($view !== 'confirmados'): ?>
                            <button
                                type="button"
                                class="btn btn-success btn-sm px-3 shadow-sm confirmar-pgto"
                                data-idvenda="<?= (int)$row['codigovendas']; ?>">
                                <i class="bi bi-check2-circle me-1"></i> CONFIRMAR PGTO
                            </button>
                        <?php endif; ?>

                        <!-- Botão Excluir -->
                        <button
                            type="button"
                            class="btn btn-outline-danger btn-sm px-3 shadow-sm excluir-pgto"
                            data-idvenda="<?= (int)$row['codigovendas']; ?>">
                            <i class="bi bi-trash3 me-1"></i> Excluir
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Mensagens WhatsApp (único) -->
<div class="modal fade" id="modalWppMensagens" tabindex="-1" aria-labelledby="modalWppMensagensLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title d-flex align-items-center gap-2" id="modalWppMensagensLabel">
                    <i class="bi bi-whatsapp"></i> Mensagens rápidas no WhatsApp
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="small text-muted">Aluno</div>
                        <div class="fw-semibold" id="wppAlunoNome">—</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="small text-muted">Curso</div>
                        <div class="fw-semibold" id="wppCurso">—</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="small text-muted">Plano</div>
                        <div class="fw-semibold" id="wppPlano">—</div>
                    </div>
                </div>

                <hr>

                <div class="list-group" id="wppMsgsList">
                    <!-- Itens gerados via JS -->
                </div>
            </div>

            <div class="modal-footer">
                <a class="btn btn-outline-primary" id="wppAbrirChat" href="#" target="_blank">
                    <i class="bi bi-chat-dots me-1"></i> Abrir chat
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<style>
    #wppMsgsList .list-group-item {
        border-radius: 10px;
    }

    #wppMsgsList .btn-copy {
        min-width: 100px;
    }

    pre.wpp-text {
        white-space: pre-wrap;
        word-wrap: break-word;
        margin: 0;
        font-size: .95rem;
    }
</style>

<script>
    /* ------------ WhatsApp Modal ------------ */
    (function() {
        const URL_LOGIN = 'https://professoreugenio.com/login_aluno.php';
        const URL_DICAS_USO = 'https://youtube.com';
        const URL_ALTERAR_PWD = 'https://youtube.com';

        const modalEl = document.getElementById('modalWppMensagens');
        const listEl = document.getElementById('wppMsgsList');
        const nomeEl = document.getElementById('wppAlunoNome');
        const cursoEl = document.getElementById('wppCurso');
        const planoEl = document.getElementById('wppPlano');
        const abrirChatBtn = document.getElementById('wppAbrirChat');

        function soNumeros(v) {
            return (v || '').toString().replace(/\D+/g, '');
        }

        function waLink(cel, texto) {
            let n = soNumeros(cel);
            if (n && !n.startsWith('55')) n = '55' + n;
            return n ? `https://wa.me/${n}?text=${encodeURIComponent(texto)}` : '#';
        }

        function saudacao() {
            const h = new Date().getHours();
            if (h < 12) return 'Bom dia';
            if (h < 18) return 'Boa tarde';
            return 'Boa noite';
        }

        function primeiroNome(nome) {
            if (!nome) return '';
            const p = nome.trim().split(/\s+/);
            return p[0] || '';
        }

        function buildMensagens({
            cel,
            nome,
            curso,
            email,
            senha,
            plano
        }) {
            const oi = saudacao();
            const pn = primeiroNome(nome || '');
            const pwd = senha || 'sua-senha';

            const m1 = `${oi}, ${pn}!\nSeja bem-vindo(a) ao curso online do Professor Eugênio.\n\n` +
                `Confirmamos que sua inscrição no curso de ${curso || 'seu curso'} foi registrada com sucesso.\n` +
                `Acesse o portal e já pode começar suas aulas!`;

            const m2 = `Seguem seus dados de acesso:\n` +
                `Login: ${email || 'seu-email'}\n` +
                `Senha: ${pwd}\n\n` +
                `Acesse: ${URL_LOGIN}`;

            const m3 = `${pn}, estamos aguardando a confirmação de pagamento do financeiro, ` +
                `mas fique tranquilo(a): seu acesso ao portal já está garantido para hoje.\n` +
                `Aproveite para iniciar seus estudos e, se precisar, estou à disposição!`;

            const m4 = `💡 Dicas de uso do sistema (vídeo):\n${URL_DICAS_USO}\n\n` +
                `Explore o menu de aulas, baixe seus materiais e acompanhe seu progresso.`;

            const m5 = `🔐 Como alterar sua senha de acesso (tutorial):\n${URL_ALTERAR_PWD}\n\n` +
                `Escolha uma senha segura e pessoal.`;

            const m6 = `🎉 Parabéns, ${pn}! Recebemos a confirmação do pagamento do seu curso *${curso || 'seu curso'}*.\n` +
                `Seu acesso ao plano *${plano || 'Anual'}* foi liberado com sucesso.\n` +
                `Aproveite para começar suas aulas agora mesmo — conte comigo no que precisar!`;

            return [{
                    id: 'm1',
                    titulo: '1) Saudação e Confirmação',
                    texto: m1
                },
                {
                    id: 'm2',
                    titulo: '2) Dados de Acesso (login/senha)',
                    texto: m2
                },
                {
                    id: 'm3',
                    titulo: '3) Aguardando Confirmação (financeiro)',
                    texto: m3
                },
                {
                    id: 'm6',
                    titulo: '4) Confirmação de Pagamento ✅',
                    texto: m6
                },
                {
                    id: 'm4',
                    titulo: '5) Dicas de Uso do Sistema (vídeo)',
                    texto: m4
                },
                {
                    id: 'm5',
                    titulo: '6) Dicas para Alterar Senha (vídeo)',
                    texto: m5
                },
            ];
        }

        function renderList(msgs, cel) {
            listEl.innerHTML = '';
            msgs.forEach(m => {
                const item = document.createElement('div');
                item.className = 'list-group-item d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2';
                item.innerHTML = `
        <div class="me-lg-3">
          <div class="fw-semibold">${m.titulo}</div>
          <pre class="wpp-text">${m.texto}</pre>
        </div>
        <div class="d-flex gap-2">
          <a class="btn btn-success btn-sm" href="${waLink(cel, m.texto)}" target="_blank">
            <i class="bi bi-send me-1"></i> Enviar
          </a>
          <button type="button" class="btn btn-outline-secondary btn-sm btn-copy" data-text="${encodeURIComponent(m.texto)}">
            <i class="bi bi-clipboard-check me-1"></i> Copiar
          </button>
        </div>`;
                listEl.appendChild(item);
            });
            listEl.querySelectorAll('.btn-copy').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const txt = decodeURIComponent(btn.getAttribute('data-text') || '');
                    try {
                        await navigator.clipboard.writeText(txt);
                        btn.innerHTML = '<i class="bi bi-clipboard-check-fill me-1"></i> Copiado';
                        setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard-check me-1"></i> Copiar', 1200);
                    } catch (e) {
                        alert('Não foi possível copiar o texto.');
                    }
                });
            });
        }
        modalEl.addEventListener('show.bs.modal', function(ev) {
            const btn = ev.relatedTarget;
            if (!btn) return;
            const cel = btn.getAttribute('data-cel') || '';
            const nome = btn.getAttribute('data-nome') || '';
            const curso = btn.getAttribute('data-curso') || '';
            const email = btn.getAttribute('data-email') || '';
            const senha = btn.getAttribute('data-senha') || '';
            const plano = btn.getAttribute('data-plano') || '';

            document.getElementById('wppAlunoNome').textContent = nome || '—';
            document.getElementById('wppCurso').textContent = curso || '—';
            document.getElementById('wppPlano').textContent = plano || '—';

            let n = (cel || '').toString().replace(/\D+/g, '');
            if (n && !n.startsWith('55')) n = '55' + n;
            document.getElementById('wppAbrirChat').href = n ? `https://wa.me/${n}` : '#';

            renderList(buildMensagens({
                cel,
                nome,
                curso,
                email,
                senha,
                plano
            }), cel);
        });
    })();
</script>

<script>
    /* ------------ Toast helper ------------ */
    (function() {
        const toastArea = document.getElementById('toastArea');
        window.__showToast = function(msg, ok = true) {
            const id = 't' + Date.now();
            const cls = ok ? 'success' : 'danger';
            const el = document.createElement('div');
            el.className = 'alert alert-' + cls + ' alert-dismissible fade show';
            el.id = id;
            el.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            toastArea.appendChild(el);
            setTimeout(() => {
                bootstrap.Alert.getOrCreateInstance(el).close();
            }, 3000);
        };
    })();
</script>

<script>
    /* ------------ Ações: Confirmar Pagto & Excluir ------------ */
    (function() {
        const lista = document.getElementById('listaVendas');

        document.addEventListener('click', async function(e) {
            /* Confirmar pagamento */
            const btnConf = e.target.closest('.confirmar-pgto');
            if (btnConf) {
                const idvenda = btnConf.getAttribute('data-idvenda');
                const item = btnConf.closest('.venda-item');
                btnConf.disabled = true;
                btnConf.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processando...';

                try {
                    const form = new FormData();
                    form.append('idvenda', idvenda);
                    const resp = await fetch('vendas1.0/ajax_RegistrarPagamento.php', {
                        method: 'POST',
                        body: form,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await resp.json();
                    if (!resp.ok || !data || !data.ok) throw new Error(data?.msg || 'Falha ao confirmar pagamento.');

                    item.style.opacity = '0.2';
                    setTimeout(() => item.remove(), 180);
                    window.__showToast('Pagamento confirmado com sucesso!', true);
                } catch (err) {
                    btnConf.disabled = false;
                    btnConf.innerHTML = '<i class="bi bi-check2-circle me-1"></i> CONFIRMAR PGTO';
                    window.__showToast(err.message || 'Erro inesperado.', false);
                    console.error(err);
                }
                return;
            }

            /* EXCLUIR venda/pagamento */
            const btnDel = e.target.closest('.excluir-pgto');
            if (btnDel) {
                const idvenda = btnDel.getAttribute('data-idvenda');
                const item = btnDel.closest('.venda-item');

                const ok = confirm('Confirma a exclusão deste registro de venda/pagamento? Esta ação não pode ser desfeita.');
                if (!ok) return;

                btnDel.disabled = true;
                btnDel.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Excluindo...';

                try {
                    const form = new FormData();
                    form.append('idvenda', idvenda);
                    const resp = await fetch('vendas1.0/ajax_ExcluirPagamento.php', {
                        method: 'POST',
                        body: form,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await resp.json();
                    if (!resp.ok || !data || !data.ok) throw new Error(data?.msg || 'Falha ao excluir.');

                    item.style.opacity = '0.2';
                    setTimeout(() => item.remove(), 180);
                    window.__showToast('Registro excluído com sucesso.', true);
                } catch (err) {
                    btnDel.disabled = false;
                    btnDel.innerHTML = '<i class="bi bi-trash3 me-1"></i> Excluir';
                    window.__showToast(err.message || 'Erro inesperado.', false);
                    console.error(err);
                }
                return;
            }
        });
    })();
</script>