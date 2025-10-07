<?php



/* Variáveis esperadas (já existem nas páginas):
   $codigomodulo  -> ID do módulo atual
   $codigoaula    -> ID da lição/publicação atual
   $nomeModulo    -> (opcional) nome do módulo (ou obter abaixo)
   $tituloAula    -> (opcional) título da lição (ou obter abaixo)
*/

// Carrega nomes do Módulo e da Lição (se não vierem prontos)
$con = config::connect();

// Título do Módulo
if (empty($nomeModulo) && !empty($codigomodulo)) {
    $qM = $con->prepare("SELECT modulo AS nome FROM new_sistema_modulos_PJA WHERE codigomodulos = :idm LIMIT 1");
    $qM->bindValue(':idm', (int)$codigomodulo, PDO::PARAM_INT);
    $qM->execute();
    $nomeModulo = ($qM->fetch(PDO::FETCH_ASSOC)['nome'] ?? 'Módulo');
}

// Título da Lição
if (empty($tituloAula) && !empty($codigoaula)) {
    $qA = $con->prepare("SELECT titulo AS titulo FROM new_sistema_publicacoes_PJA WHERE codigopublicacoes = :ida LIMIT 1");
    $qA->bindValue(':ida', (int)$codigoaula, PDO::PARAM_INT);
    $qA->execute();
    $tituloAula = ($qA->fetch(PDO::FETCH_ASSOC)['titulo'] ?? 'Lição');
}

/* 
   Listagem dos depoimentos (a_curso_forum)
   Assumindo que idcodforumCF referencia a lição (aula). 
   Se preferir filtrar por módulo também, pode adicionar condição extra no WHERE.
*/
$sql = "
    SELECT 
        f.codigoforum,
        f.idusuarioCF,
        f.idartigoCF,
        f.idcodforumCF,
        f.textoCF,
        f.visivelCF,
        f.dataCF,
        f.horaCF,
        f.destaqueCF,
        c.nome,
        c.pastasc,
        c.imagem50
    FROM a_curso_forum f
    LEFT JOIN new_sistema_cadastro c ON c.codigocadastro = f.idusuarioCF
    WHERE f.visivelCF = 1
      AND (:idaula IS NULL OR f.idcodforumCF = :idaula)
    ORDER BY f.destaqueCF DESC, f.dataCF DESC, f.horaCF DESC, f.codigoforum DESC
";
$q = $con->prepare($sql);
$q->bindValue(':idaula', !empty($codigoaula) ? (int)$codigoaula : null, !empty($codigoaula) ? PDO::PARAM_INT : PDO::PARAM_NULL);
$q->execute();
$mensagens = $q->fetchAll(PDO::FETCH_ASSOC);

// Função helper para caminho da imagem 50px
function fotoUsuario50($row)
{
    if (!empty($row['pastasc']) && !empty($row['imagem50'])) {
        return "/fotos/usuarios/{$row['pastasc']}/{$row['imagem50']}";
    }
    return "https://via.placeholder.com/50x50?text=U";
}

// BR data/hora
function brData($dataYmd)
{
    if (!$dataYmd) return '';
    $p = explode('-', $dataYmd);
    return (count($p) === 3) ? ($p[2] . '/' . $p[1] . '/' . $p[0]) : $dataYmd;
}
?>

<!-- Cabeçalho da seção -->
<div class="container mt-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-0 text-uppercase fw-bold"><?= htmlspecialchars($nomeModulo ?? 'Módulo') ?></h4>
            <div class="text-muted small">Lição: <?= htmlspecialchars($tituloAula ?? '—') ?></div>
        </div>
        <div class="text-end">
            <span class="badge bg-secondary">Depoimentos</span>
        </div>
    </div>

    <?php if (empty($mensagens)) : ?>
        <div class="alert alert-warning">
            Nenhum depoimento encontrado para esta lição.
        </div>
    <?php else : ?>
        <div class="list-group shadow-sm">
            <?php foreach ($mensagens as $m):
                $img = fotoUsuario50($m);
                $nome = $m['nome'] ?? 'Usuário';
                $texto = trim($m['textoCF'] ?? '');
                $data = brData($m['dataCF'] ?? '');
                $hora = $m['horaCF'] ?? '';
                $fav  = (int)$m['destaqueCF'] === 1;
            ?>
                <div class="list-group-item bg-body-tertiary border-0 border-bottom d-flex gap-3 py-3">
                    <img src="<?= htmlspecialchars($img) ?>" alt="Foto de <?= htmlspecialchars($nome) ?>"
                        class="rounded-circle flex-shrink-0" width="50" height="50" style="object-fit:cover;">

                    <div class="w-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($nome) ?></div>
                                <div class="small text-muted"><?= $data ?> <?= $hora ? 'às ' . htmlspecialchars($hora) : '' ?></div>
                            </div>
                            <button
                                type="button"
                                class="btn btn-sm <?= $fav ? 'btn-warning' : 'btn-outline-warning' ?> btn-favoritar"
                                data-id="<?= (int)$m['codigoforum'] ?>"
                                data-fav="<?= $fav ? '1' : '0' ?>"
                                title="<?= $fav ? 'Remover destaque' : 'Destacar depoimento' ?>">
                                <i class="bi <?= $fav ? 'bi-star-fill' : 'bi-star' ?>"></i>
                            </button>
                        </div>
                        <div class="mt-2">
                            <div class="p-3 bg-white rounded-3 shadow-sm border">
                                <?= nl2br(htmlspecialchars($texto)) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Toast central para feedback -->
<div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1080">
    <div id="toastForum" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-favoritar');
        if (!btn) return;

        const id = btn.getAttribute('data-id');
        const favAtual = btn.getAttribute('data-fav') === '1' ? 1 : 0;

        // loading state
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

        try {
            const form = new URLSearchParams();
            form.append('idforum', id);
            form.append('valor', favAtual === 1 ? '0' : '1'); // toggle

            const resp = await fetch('forumv1.0/ajax_forumFavoritar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: form.toString()
            });

            const data = await resp.json();

            if (data && data.sucesso) {
                // Atualiza UI
                const novoFav = data.destaque == 1;
                btn.classList.toggle('btn-warning', novoFav);
                btn.classList.toggle('btn-outline-warning', !novoFav);
                btn.setAttribute('data-fav', novoFav ? '1' : '0');
                btn.title = novoFav ? 'Remover destaque' : 'Destacar depoimento';
                btn.innerHTML = `<i class="bi ${novoFav ? 'bi-star-fill' : 'bi-star'}"></i>`;
                showToast(novoFav ? 'Depoimento destacado.' : 'Destaque removido.');
            } else {
                btn.innerHTML = originalHTML;
                showToast(data?.mensagem || 'Falha ao atualizar destaque.');
            }
        } catch (err) {
            btn.innerHTML = originalHTML;
            showToast('Erro de conexão.');
        } finally {
            btn.disabled = false;
        }
    });

    function showToast(msg) {
        const toastEl = document.getElementById('toastForum');
        toastEl.querySelector('.toast-body').textContent = msg;
        const t = new bootstrap.Toast(toastEl, {
            delay: 1800
        });
        t.show();
    }
</script>