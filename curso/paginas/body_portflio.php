<?php
// ========= Se tem portfólio, carregar módulos e imagens =========

$idUser = $codigoUser;
// ========= Verifica se o usuário tem Portfólio =========
$stmtPort = $con->prepare("SELECT chaveap, dataap FROM a_aluno_portfolio WHERE idalunoap = :idaluno LIMIT 1");
$stmtPort->bindParam(':idaluno', $idUser, PDO::PARAM_INT);
$stmtPort->execute();
$rowPort = $stmtPort->fetch(PDO::FETCH_ASSOC);

// Tipos de imagem aceitáveis
$tiposImg = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
$ph = implode(',', array_fill(0, count($tiposImg), '?'));

// Consulta: módulos que possuem anexos (imagens) do aluno
$sqlModulos = "
SELECT m.codigomodulos, m.modulo AS nome_modulo
FROM new_sistema_modulos_PJA m
INNER JOIN a_curso_AtividadeAnexos a
ON a.idmoduloAA = m.codigomodulos
AND a.idalulnoAA = ?
AND a.extensaoAA IN ($ph)
GROUP BY m.codigomodulos, m.modulo
ORDER BY m.modulo
";

$stmtM = $con->prepare($sqlModulos);
$bind = 1;
$stmtM->bindValue($bind++, $idUser, PDO::PARAM_INT);
foreach ($tiposImg as $ext) $stmtM->bindValue($bind++, $ext);
$stmtM->execute();
$modulos = $stmtM->fetchAll(PDO::FETCH_ASSOC);

// Função de URL final para as imagens
function pf_url($pasta, $img)
{
    $pasta = trim((string)$pasta, '/');
    $img = ltrim((string)$img, '/');
    return "../../fotos/atividades/{$pasta}/{$img}";
}
?>

<!-- Navbar -->
<?php include 'v2.0/nav.php'; ?>

<style>
    /* Grade fixa com cards 100x100 */
    .pf-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, 200px);
        gap: .75rem;
        justify-content: start;
    }

    .pf-card {
        width: 200px;
        height: 200px;
        border-radius: .75rem;
        overflow: hidden;
        position: relative;
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
        box-shadow: 0 6px 14px rgba(0, 0, 0, .10);
        border: 1px solid rgba(255, 255, 255, .12);
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .pf-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 18px rgba(0, 0, 0, .16);
    }

    .pf-module-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .pf-module-head h6 {
        margin: 0;
        color: #fff;
    }

    .pf-legend {
        position: absolute;
        left: 8px;
        bottom: 6px;
        background: rgba(0, 0, 0, .45);
        color: #fff;
        font-size: .7rem;
        padding: .1rem .4rem;
        border-radius: .35rem;
    }
</style>

<!-- Conteúdo -->
<section id="Corpo" class="py-4">
    <div class="container">

        <div class="text-center mb-4" data-aos="fade-down">
            <h4 class="mt-2 mb-1 text-white">
                <i class="bi bi-layers"></i> Portfólio
            </h4>
            <div class="small text-light-50">Chave: <strong><?= htmlspecialchars($rowPort['chaveap']) ?></strong></div>
        </div>

        <!-- Caixa: enviar link do portfólio por WhatsApp -->
        <div class="card card-portfolio mb-4" data-aos="fade-down">
            <div class="card-body">
                <label for="whatsPhone" class="form-label mb-2">
                    <i class="bi bi-whatsapp me-1"></i> Enviar link do meu portfólio por WhatsApp
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                    <input type="text"
                        class="form-control"
                        id="whatsPhone"
                        placeholder="DDD + número (ex: 85997810324)">
                    <button class="btn btn-success" id="btnEnviarWhats">
                        Enviar
                    </button>
                </div>
                <div class="form-text mt-1">
                    Será enviado o link:
                    <code>https://professoreugenio.com/portfolio.php?key=<?= urlencode($rowPort['chaveap']) ?></code>
                </div>
            </div>
        </div>


        <?php if (empty($modulos)): ?>
            <div class="container">
                <div class="alert alert-info text-center" data-aos="fade-up">
                    Você ainda não possui imagens de atividades para exibir no portfólio.
                </div>
            </div>
        <?php else: ?>

            <?php
            // Para cada módulo, buscar imagens
            $sqlImgs = "
        SELECT 
          codigoatividadeanexos, idpublicacacaoAA, idalulnoAA, idmoduloAA,
          fotoAA, pastaAA, extensaoAA, dataenvioAA, horaenvioAA, sizeAA
        FROM a_curso_AtividadeAnexos
        WHERE idalulnoAA = ?
          AND idmoduloAA = ?
          AND extensaoAA IN ($ph)
        ORDER BY dataenvioAA DESC, horaenvioAA DESC, codigoatividadeanexos DESC
      ";
            $stmtI = $con->prepare($sqlImgs);

            foreach ($modulos as $mod):
                $bind = 1;
                $stmtI->bindValue($bind++, $idUser, PDO::PARAM_INT);
                $stmtI->bindValue($bind++, (int)$mod['codigomodulos'], PDO::PARAM_INT);
                foreach ($tiposImg as $ext) $stmtI->bindValue($bind++, $ext);
                $stmtI->execute();
                $imgs = $stmtI->fetchAll(PDO::FETCH_ASSOC);
                if (!$imgs) continue;
            ?>

                <div class="mb-4" data-aos="fade-up">
                    <div class="pf-module-head mb-2">
                        <h6><i class="bi bi-folder2-open me-2"></i><?= htmlspecialchars($mod['nome_modulo']) ?></h6>
                        <span class="badge bg-warning text-dark"><?= count($imgs) ?> itens</span>
                    </div>
                    <div class="pf-grid">
                        <?php foreach ($imgs as $im):
                            $url = pf_url($im['pastaAA'], $im['fotoAA']);
                            $info = 'Enviado em ' . date('d/m/Y', strtotime($im['dataenvioAA'] ?? date('Y-m-d'))) . ' às ' . substr($im['horaenvioAA'] ?? '00:00:00', 0, 5);
                        ?>
                            <a href="#"
                                class="pf-card"
                                style="background-image:url('<?= htmlspecialchars($url) ?>');"
                                data-bs-toggle="modal"
                                data-bs-target="#lightboxModal"
                                data-url="<?= htmlspecialchars($url) ?>"
                                data-info="<?= htmlspecialchars($info) ?>"
                                title="<?= htmlspecialchars($info) ?>">
                                <span class="pf-legend">#<?= (int)$im['codigoatividadeanexos'] ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</section>

<!-- Rodapé -->
<?php require 'v2.0/footer.php'; ?>

<!-- Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h6 class="modal-title">Pré-visualização</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body text-center">
                <img id="lbImg" src="" alt="" class="img-fluid rounded">
                <div id="lbInfo" class="small text-muted mt-2"></div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS JS -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 700,
        once: true
    });

    // Lightbox handler
    const lbModal = document.getElementById('lightboxModal');
    lbModal?.addEventListener('show.bs.modal', (ev) => {
        const trigger = ev.relatedTarget;
        const url = trigger?.getAttribute('data-url');
        const info = trigger?.getAttribute('data-info') || '';
        if (url) {
            document.getElementById('lbImg').src = url;
            document.getElementById('lbInfo').textContent = info;
        }
    });
</script>

<script src="regixv2.0/acessopaginas.js?<?= time(); ?>"></script>
<script src="config_default1.0/JS_logoff.js?<?= time(); ?>"></script>

<script>
    (function() {
        const chave = "<?= addslashes($rowPort['chaveap']) ?>";
        const baseLink = "https://professoreugenio.com/portfolio.php?key=" + encodeURIComponent(chave);

        const input = document.getElementById('whatsPhone');
        const btn = document.getElementById('btnEnviarWhats');

        function normalizaTelefone(v) {
            // Mantém apenas dígitos
            let f = (v || '').replace(/\D/g, '');
            // Se parecer BR (10 ou 11 dígitos) e não começar com 55, adiciona 55
            if ((f.length === 10 || f.length === 11) && !f.startsWith('55')) {
                f = '55' + f;
            }
            return f;
        }

        function enviarWhats() {
            const raw = input?.value || '';
            const fone = normalizaTelefone(raw);
            if (!fone) {
                alert('Informe um número de WhatsApp.');
                input?.focus();
                return;
            }
            const msg = `Olá! Veja meu portfólio: ${baseLink}`;
            const wa = `https://wa.me/${fone}?text=${encodeURIComponent(msg)}`;
            window.open(wa, '_blank');
        }

        btn?.addEventListener('click', enviarWhats);
        input?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') enviarWhats();
        });
    })();
</script>