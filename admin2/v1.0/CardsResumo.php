<div class="row g-4">
    <?php require '../v1.0/queryContTotalAlunos.php'; ?>
    <?php require '../v1.0/queryAniversariantes.php'; ?>
    <?php require '../v1.0/queryContCursos.php'; ?>
    <?php require '../v1.0/queryContAcessos.php'; ?>
    <!-- CARDS -->
    <?php require '../v1.0/require_PaginasAdmin.php'; ?>
    <?php require '../v1.0/require_Vendas.php'; ?>
    <?php require '../v1.0/require_Cursos.php'; ?>
    <?php require '../v1.0/require_Aniversariantes.php'; ?>
    <?php require '../v1.0/require_Usuarios.php'; ?>
    <?php require '../v1.0/require_Publicacoes.php'; ?>
    <?php require '../v1.0/require_Acessos.php'; ?>
    <?php require '../v1.0/require_Avaliacoes.php'; ?>
    <?php require '../v1.0/require_Mensagens.php'; ?>
    <?php require '../v1.0/require_Afiliados.php'; ?>
    <?php require '../v1.0/require_BancodeImagens.php'; ?>
    <?php require '../v1.0/require_Anuncio.php'; ?>
    <?php require '../v1.0/require_Financeiro.php'; ?>

   
    <?php if (temPermissao($niveladm, [1])): ?>
        <!-- CPanel -->
        <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="550">
            <div class="card stat-card card-slate h-100 border-start">
                <div class="card-body d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center gap-2">
                            <span class="icon-badge"><i class="bi bi-server"></i></span>
                            <h6 class="title mb-0">CPanel</h6>
                        </div>
                        <span class="value">Admin</span>
                    </div>
                    <span class="label text-muted">Acesso direto ao servidor</span>
                    <a target="_blank" href="https://professoreugenio.com:2083" class="stretched-link" aria-label="Abrir CPanel"></a>
                    <span class="corner"></span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toggleBtn = document.getElementById("toggleValores");
        const icon = document.getElementById("iconToggle");
        let visivel = false; // Começa oculto
        // Oculta todos os valores inicialmente
        document.querySelectorAll('.valor-card').forEach(el => {
            el.style.visibility = 'hidden';
        });
        toggleBtn.addEventListener("click", function() {
            visivel = !visivel;
            document.querySelectorAll('.valor-card').forEach(el => {
                el.style.visibility = visivel ? 'visible' : 'hidden';
            });
            icon.className = visivel ? 'bi bi-eye' : 'bi bi-eye-slash';
            toggleBtn.innerHTML = `<i id="iconToggle" class="${icon.className}"></i> ${visivel ? 'Ocultar' : 'Exibir'} valores`;
        });
        // Ajusta ícone e texto inicial
        icon.className = 'bi bi-eye-slash';
        toggleBtn.innerHTML = `<i id="iconToggle" class="bi bi-eye-slash"></i> Exibir valores`;
    });
</script>