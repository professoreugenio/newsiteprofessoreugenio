<?php

/**
 * BodyVendasLista.php
 * Lista de vendas pendentes (statussv != 1) + CONFIRMAR PGTO via AJAX
 * Usa $con (PDO) disponível na página principal.
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
  a.nome         AS nome_aluno,
  a.celular      AS cel_aluno,

  af.idusuarioSA AS id_afiliado,
  afc.nome       AS nome_afiliado
FROM a_site_vendas v
LEFT JOIN new_sistema_cursos c 
       ON c.codigocursos = v.idcursosv
LEFT JOIN new_sistema_cadastro a
       ON a.codigocadastro = v.idalunosv
LEFT JOIN a_site_afiliados_chave af
       ON (af.codigochaveafiliados = v.chaveafiliadosv OR af.chaveafiliadoSA = v.chaveafiliadosv)
LEFT JOIN new_sistema_cadastro afc
       ON afc.codigocadastro = af.idusuarioSA
WHERE (v.statussv IS NULL OR v.statussv <> 1)
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

    @media (max-width: 768px) {
        .venda-right {
            margin-top: .75rem;
        }
    }
</style>

<div class="vendas-wrap" data-aos="fade-up" data-aos-delay="100">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="fw-bold fs-5" style="color:#112240;">Vendas pendentes de confirmação</div>
        <span class="badge bg-success-subtle text-success border border-success-subtle">
            <?= count($vendas); ?> registros
        </span>
    </div>

    <div id="toastArea"></div>

    <div class="vstack gap-2" id="listaVendas">
        <?php if (!$vendas): ?>
            <div class="alert alert-info mb-0">Nenhuma venda pendente encontrada.</div>
        <?php else: ?>
            <?php foreach ($vendas as $row):
                $dataHora = '';
                if (!empty($row['datacomprasv'])) {
                    $dataFmt = date('d/m/Y', strtotime($row['datacomprasv']));
                    $horaFmt = !empty($row['horacomprasv']) ? date('H:i', strtotime($row['horacomprasv'])) : '00:00';
                    $dataHora = $dataFmt . ' ' . $horaFmt;
                }
                $curso = $row['nomecurso'] ?? '—';
                $aluno = primeiroESobrenome($row['nome_aluno'] ?? '—');
                $cel   = $row['cel_aluno'] ?? '';
                $idCursov   = $row['idcursosv'] ?? '';
                $encIdCursov = encrypt($idCursov, $action = 'e');
                $whats = whatsLink($cel);

                $temAf  = !empty($row['chaveafiliadosv']) && !empty($row['nome_afiliado']);
                $afNome = $row['nome_afiliado'] ?? '';
                $linkAfiliado = 'afiliadoPerfil.php?af=' . urlencode((string)$row['chaveafiliadosv']);

                $encIdUsuario = encrypt($row['codigocadastro'], $action = 'e');

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

                            <a href="alunoAtendimento.php?idUsuario=<?= e($encIdUsuario); ?>"><i class="bi bi-person-circle me-1"></i><?= e($aluno); ?></a>

                        </div>
                        <span class="dot d-none d-lg-inline"></span>

                        <!-- ATENDIMENTO PERSONALIZADO -->

                        <?php
                        // Helpers (se já existirem no topo, não repetir)
                        if (!function_exists('saudacaoBR')) {
                            function saudacaoBR()
                            {
                                $h = (int)date('G');
                                if ($h < 12) return 'Bom dia';
                                if ($h < 18) return 'Boa tarde';
                                return 'Boa noite';
                            }
                        }
                        if (!function_exists('primeiroNome')) {
                            function primeiroNome($nome)
                            {
                                $nome = trim((string)$nome);
                                if ($nome === '') return '';
                                return explode(' ', $nome)[0];
                            }
                        }
                        if (!function_exists('whatsMsgLink')) {
                            function whatsMsgLink($celular, $texto)
                            {
                                $nums = preg_replace('/\D+/', '', (string)$celular);
                                if ($nums && substr($nums, 0, 2) !== '55') $nums = '55' . $nums;
                                $base = 'https://wa.me/' . $nums;
                                return $nums ? ($base . '?text=' . rawurlencode($texto)) : '#';
                            }
                        }

                        // Variáveis
                        $nomeAlunoCompleto = (string)($row['nome_aluno'] ?? '');
                        $nomeAlunoPrimeiro = primeiroNome($nomeAlunoCompleto);
                        $nomeCurso         = (string)($row['nomecurso'] ?? '');
                        $emailAluno        = (string)($row['email_aluno'] ?? 'seu-email');
                        $senhaAluno        = isset($row['senha_aluno']) ? (string)$row['senha_aluno'] : 'sua-senha';
                        $nomePlano         = (string)($row['nome_plano'] ?? 'Anual'); // ajustar conforme coluna disponível
                        $saudacao          = saudacaoBR();

                        // Mensagens
                        $msg1 = "{$saudacao}, {$nomeAlunoPrimeiro}! Seja bem-vindo(a) ao curso online do Professor Eugênio. "
                            . "Confirmamos que sua inscrição no curso de {$nomeCurso} foi registrada com sucesso. "
                            . "Fique à vontade para explorar o portal e dar início às suas aulas.";

                        $msg2 = "Seguem seus dados de acesso:\n"
                            . "Login: {$emailAluno}\n"
                            . "Senha: {$senhaAluno}\n\n"
                            . "Acesse a página de login e utilize seus dados:\n"
                            . "https://professoreugenio.com/login_aluno.php";

                        $msg3 = "{$nomeAlunoPrimeiro}, estamos aguardando a confirmação de pagamento do financeiro, "
                            . "mas fique tranquilo(a): seu acesso ao portal já está garantido para hoje. "
                            . "Aproveite para iniciar seus estudos e nos chame se tiver qualquer dúvida!";

                        $urlDicasUso   = "https://youtube.com";
                        $urlAlterarPwd = "https://youtube.com";

                        $msg4 = "💡 Dicas de uso do sistema (vídeo):\n{$urlDicasUso}\n\n"
                            . "Explore o menu de aulas, baixe seus materiais e acompanhe seu progresso.";

                        $msg5 = "🔐 Como alterar sua senha de acesso (tutorial):\n{$urlAlterarPwd}\n\n"
                            . "Lembre-se de escolher uma senha segura e pessoal.";

                        $msg6 = "🎉 Parabéns, {$nomeAlunoPrimeiro}! Recebemos a confirmação do pagamento do seu curso *{$nomeCurso}*. "
                            . "Seu acesso ao plano *{$nomePlano}* foi liberado com sucesso! "
                            . "Aproveite para começar suas aulas agora mesmo — e conte comigo em qualquer etapa da sua jornada!";

                        // Links
                        $waLink1 = whatsMsgLink($cel, $msg1);
                        $waLink2 = whatsMsgLink($cel, $msg2);
                        $waLink3 = whatsMsgLink($cel, $msg3);
                        $waLink4 = whatsMsgLink($cel, $msg4);
                        $waLink5 = whatsMsgLink($cel, $msg5);
                        $waLink6 = whatsMsgLink($cel, $msg6);
                        ?>

                        <div class="venda-meta me-lg-3">

                            <?php if ($cel): ?>


                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-success btn-sm dropdown-toggle" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">

                                        <i class="bi bi-whatsapp me-1"><i class="bi bi-telephone-outbound me-1"></i>
                                        </i> <?= e($cel); ?>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li class="dropdown-header small text-muted">Enviar no WhatsApp</li>
                                        <li><a class="dropdown-item" href="<?= e($waLink1); ?>" target="_blank">1. Saudação e Confirmação</a></li>
                                        <li><a class="dropdown-item" href="<?= e($waLink2); ?>" target="_blank">2. Dados de Acesso (login/senha)</a></li>
                                        <li><a class="dropdown-item" href="<?= e($waLink3); ?>" target="_blank">3. Aguardando Confirmação (financeiro)</a></li>
                                        <li><a class="dropdown-item" href="<?= e($waLink6); ?>" target="_blank">4. Confirmação de Pagamento ✅</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item" href="<?= e($waLink4); ?>" target="_blank">5. Dicas de Uso do Sistema (vídeo)</a></li>
                                        <li><a class="dropdown-item" href="<?= e($waLink5); ?>" target="_blank">6. Dicas para Alterar Senha (vídeo)</a></li>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">sem celular</span>
                            <?php endif; ?>
                        </div>


                        <!-- <div class="venda-meta me-lg-3">
                            <i class="bi bi-telephone-outbound me-1"></i>
                            <?php if ($cel): ?>
                                <a href="<?= e($whats); ?>" target="_blank" class="link-primary" title="Abrir WhatsApp"><?= e($cel); ?></a>
                            <?php else: ?>
                                <span class="text-muted">sem celular</span>
                            <?php endif; ?>
                        </div> -->
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
                    <div class="venda-right">
                        <button
                            type="button"
                            class="btn btn-success btn-sm px-3 shadow-sm confirmar-pgto"
                            data-idvenda="<?= (int)$row['codigovendas']; ?>">
                            <i class="bi bi-check2-circle me-1"></i> CONFIRMAR PGTO
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    (function() {
        const lista = document.getElementById('listaVendas');
        const toastArea = document.getElementById('toastArea');

        function showToast(msg, ok = true) {
            const id = 't' + Date.now();
            const cls = ok ? 'success' : 'danger';
            const el = document.createElement('div');
            el.className = 'alert alert-' + cls + ' alert-dismissible fade show';
            el.id = id;
            el.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            toastArea.appendChild(el);
            setTimeout(() => {
                const myAlert = bootstrap.Alert.getOrCreateInstance(el);
                myAlert.close();
            }, 3000);
        }

        document.addEventListener('click', async function(e) {
            const btn = e.target.closest('.confirmar-pgto');
            if (!btn) return;

            const idvenda = btn.getAttribute('data-idvenda');
            const item = btn.closest('.venda-item');

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processando...';

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

                if (!resp.ok || !data || !data.ok) {
                    throw new Error(data?.msg || 'Falha ao confirmar pagamento.');
                }

                // Remover visualmente a venda (já não deveria mais aparecer por statussv = 1)
                item.style.opacity = '0.2';
                setTimeout(() => {
                    item.remove();
                }, 180);
                showToast('Pagamento confirmado com sucesso!', true);
            } catch (err) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> CONFIRMAR PGTO';
                showToast(err.message || 'Erro inesperado.', false);
                console.error(err);
            }
        });
    })();
</script>