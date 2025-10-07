<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 600,
        once: true
    });
</script>
</div>
<!-- Popper e Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Modal de Confirmação -->
<div class="modal fade" id="modalLogoff" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Logoff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja sair do sistema?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="confirmLogoff" class="btn btn-danger">Sair</button>
            </div>
        </div>
    </div>
</div>



<script>
    $(document).ready(function() {
        // Abre o modal ao clicar em sair
        $("#btnLogoff").on("click", function(e) {
            e.preventDefault();
            var modal = new bootstrap.Modal(document.getElementById('modalLogoff'));
            modal.show();
        });

        // Confirma logoff
        $("#confirmLogoff").on("click", function() {
            $.ajax({
                url: "../../defaultv1.0/logoff.php",
                method: "POST",
                success: function(d) {
                    window.open("../", "_self");
                    window.close();
                },
                error: function() {
                    alert("Erro ao tentar sair. Tente novamente.");
                }
            });
        });
    });
</script>