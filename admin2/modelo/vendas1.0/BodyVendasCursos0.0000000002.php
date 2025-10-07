<?php

/** 
 * BodyVendasLista.php
 * Lista de vendas realizadas (uma por linha)
 * Requisitos:
 * - Exibir: data/hora, curso, aluno (nome + sobrenome), celular (link WhatsApp), valor
 * - Se tiver afiliado: exibir nome do afiliado com link para perfil
 * - Botão à direita: "CONFIRMAR PGTO"
 * Observações:
 * - Supondo conexão PDO disponível em $con (já incluída na página principal).
 * - Ajuste o link do perfil do afiliado caso use outro padrão de rota.
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
        if ($v === null || $v === '') return 'R$ 0,00';
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
        // Remove tudo que não for dígito
        $nums = preg_replace('/\D+/', '', (string)$celular);
        // Se não começar com 55, adiciona (Brasil)
        if ($nums !== '' && substr($nums, 0, 2) !== '55') {
            $nums = '55' . $nums;
        }
        return 'https://wa.me/' . $nums;
    }
}

// Você pode controlar paginação/limite se desejar
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

  c.nomecurso,
  c.bgcolor,

  a.nome         AS nome_aluno,
  a.celular      AS cel_aluno,

  af.idusuarioSA AS id_afiliado,
  afc.nome       AS nome_afiliado
FROM a_site_vendas v
LEFT JOIN new_sistema_cursos c 
       ON c.codigocursos = v.idcursosv
LEFT JOIN new_sistema_cadastro a
       ON a.codigocadastro = v.idalunosv
/* 
Join de afiliado:
   cobrimos os dois cenários:
   - v.chaveafiliadosv guarda o código numérico (codigochaveafiliados)
   - v.chaveafiliadosv guarda a chave textual (chaveafiliadoSA)
   
*/
LEFT JOIN a_site_afiliados_chave af
       ON (af.codigochaveafiliados = v.chaveafiliadosv OR af.chaveafiliadoSA = v.chaveafiliadosv)
LEFT JOIN new_sistema_cadastro afc
       ON afc.codigocadastro = af.idusuarioSA
ORDER BY v.datacomprasv DESC, v.horacomprasv DESC
LIMIT :lim
";

$stmt = $con->prepare($sql);
$stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
$stmt->execute();
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    /* Estilo leve e moderno, compatível com Bootstrap 5 */
    .vendas-wrap {
        max-width: 1400px;
        margin: 0 auto;
    }

    .venda-item {
        border: 1px solid rgba(17, 34, 64, .08);
        border-left: 6px solid #00BB9C;
        /* acento da sua paleta */
        border-radius: 16px;
        padding: 14px 16px;
        background: #fff;
        transition: transform .15s ease, box-shadow .15s ease;
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

    @media (max-width: 768px) {
        .venda-right {
            margin-top: .75rem;
        }
    }
</style>

<div class="vendas-wrap" data-aos="fade-up" data-aos-delay="100">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="fw-bold fs-5" style="color:#112240;">Vendas realizadas</div>
        <span class="badge bg-success-subtle text-success border border-success-subtle">
            <?= count($vendas); ?> registros
        </span>
    </div>

    <div class="vstack gap-2">
        <?php if (!$vendas): ?>
            <div class="alert alert-info mb-0">Nenhuma venda encontrada.</div>
        <?php else: ?>
            <?php foreach ($vendas as $row):
                $dataHora = '';
                if (!empty($row['datacomprasv'])) {
                    // Formato DD/MM/YYYY HH:MM
                    $dataFmt = date('d/m/Y', strtotime($row['datacomprasv']));
                    $horaFmt = !empty($row['horacomprasv']) ? date('H:i', strtotime($row['horacomprasv'])) : '00:00';
                    $dataHora = $dataFmt . ' ' . $horaFmt;
                }
                $curso = $row['nomecurso'] ?? '—';
                $aluno = primeiroESobrenome($row['nome_aluno'] ?? '—');
                $cel   = $row['cel_aluno'] ?? '';
                $whats = whatsLink($cel);

                $temAf  = !empty($row['chaveafiliadosv']) && !empty($row['nome_afiliado']);
                $afNome = $row['nome_afiliado'] ?? '';
                // Link do afiliado: ajuste se seu roteamento usa ":" em vez de "?"
                $linkAfiliado = 'afiliadoPerfil.php?af=' . urlencode((string)$row['chaveafiliadosv']);
            ?>
                <div class="venda-item d-flex flex-column flex-md-row align-items-md-center justify-content-between" data-aos="fade-up">
                    <!-- Lado Esquerdo -->
                    <div class="venda-left d-flex flex-column flex-lg-row align-items-lg-center">
                        <div class="venda-meta me-lg-3">
                            <i class="bi bi-calendar2-check me-1"></i><?= e($dataHora); ?>
                        </div>

                        <span class="dot d-none d-lg-inline"></span>

                        <div class="venda-curso me-lg-3">
                            <i class="bi bi-journal-code me-1"></i><?= e($curso); ?>
                        </div>

                        <span class="dot d-none d-lg-inline"></span>

                        <div class="venda-aluno me-lg-3">
                            <i class="bi bi-person-circle me-1"></i><?= e($aluno); ?>
                        </div>

                        <span class="dot d-none d-lg-inline"></span>

                        <div class="venda-meta me-lg-3">
                            <i class="bi bi-telephone-outbound me-1"></i>
                            <?php if ($cel): ?>
                                <a href="<?= e($whats); ?>" target="_blank" class="link-primary" title="Abrir WhatsApp"><?= e($cel); ?></a>
                            <?php else: ?>
                                <span class="text-muted">sem celular</span>
                            <?php endif; ?>
                        </div>

                        <span class="dot d-none d-lg-inline"></span>

                        <div class="venda-valor me-lg-3">
                            <i class="bi bi-cash-coin me-1"></i><?= e(formatBRL($row['valorvendasv'])); ?>
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
                            data-idvenda="<?= (int)$row['codigovendas']; ?>"
                            data-idcurso="<?= (int)$row['idcursosv']; ?>"
                            data-idaluno="<?= (int)$row['idalunosv']; ?>">
                            <i class="bi bi-check2-circle me-1"></i> CONFIRMAR PGTO
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    // Placeholder para ação do botão "CONFIRMAR PGTO" (AJAX posterior)
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.confirmar-pgto');
        if (!btn) return;

        const idvenda = btn.getAttribute('data-idvenda');
        const idcurso = btn.getAttribute('data-idcurso');
        const idaluno = btn.getAttribute('data-idaluno');

        // Aqui você pode abrir um modal ou chamar um endpoint AJAX para confirmar pagamento
        // Exemplo (placeholder):
        console.log('Confirmar pagamento :: ', {
            idvenda,
            idcurso,
            idaluno
        });

        // Feedback visual simples
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processando...';
        setTimeout(() => {
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-success');
            btn.innerHTML = '<i class="bi bi-check2-all me-1"></i> Pagamento confirmado';
        }, 900);
    });
</script>