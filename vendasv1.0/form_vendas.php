<section class="vitrine-venda text-white d-flex align-items-center justify-content-center min-vh-100" style="background-color: #0A192F;" data-aos="fade-up">
    <div class="container px-4 py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <form id="cadastroForm" method="post" class="needs-validation bg-dark bg-opacity-75 p-4 p-md-5 rounded shadow-lg" novalidate>

                    <!-- 🔒 Cabeçalho de Escolha -->
                    <div class="bg-secondary bg-opacity-25 p-4 rounded shadow-sm mb-4 border-start border-4 border-info">
                        <p class="mb-0 fs-5 text-white fw-semibold">
                            <i class="bi bi-unlock-fill text-info me-2"></i>
                            Escolha sua liberdade: <span class="text-warning">Anual ou Vitalício?</span>
                        </p>
                    </div>

                    <!-- 🎯 Botões de Seleção -->
                    <div class="btn-group d-flex gap-3 flex-column flex-md-row mb-4" role="group" aria-label="Tipo de Assinatura">
                        <!-- Assinatura Anual -->
                        <input type="radio" class="btn-check" name="tipoAssinatura" id="assinaturaAnual" value="Assinatura Anual" autocomplete="off" checked>
                        <label class="btn btn-outline-primary bg-dark text-light border border-info rounded-3 p-3 shadow-sm w-100"
                            for="assinaturaAnual" onclick="rolarParaCadastro()">
                            <i class="bi bi-calendar3 me-2 text-info"></i>
                            <strong>Assinatura Anual</strong><br>
                            <small>R$ <?php echo $valor; ?></small>
                        </label>

                        <!-- Assinatura Vitalícia -->
                        <input type="radio" class="btn-check" name="tipoAssinatura" id="assinaturaVitalicia" value="Assinatura Vitalícia + Bônus" autocomplete="off">
                        <label class="btn btn-outline-success bg-dark text-light border border-success rounded-3 p-3 shadow-sm w-100"
                            for="assinaturaVitalicia" onclick="rolarParaCadastro()">
                            <i class="bi bi-infinity me-2 text-success"></i>
                            <strong>Assinatura Vitalícia + </strong><br>
                            <small>Bônus: mini-curso Excel + <span class="text-warning">atualizações vitalícias</span><br>
                                R$ <?php echo $valorvitalicio; ?></small>
                        </label>
                    </div>

                    <!-- 🎯 Valor e Tipo Selecionado -->
                    <h4 class="text-white text-center mb-3" id="tituloassinatura">Assinatura Anual*</h4>
                    <input type="hidden" name="assinatura" id="assinatura" value="Assinatura Anual">

                    <!-- 👤 Nome -->
                    <div class="mb-3 text-start">
                        <label for="nomeCadastro" class="form-label text-white">Nome Completo</label>
                        <input type="text" class="form-control" id="nomeCadastro" name="nomeCadastro" required>
                        <div class="invalid-feedback">Por favor, insira seu nome completo.</div>
                    </div>

                    <!-- 📧 E-mail -->
                    <div class="mb-3 text-start">
                        <label for="emailCadastro" class="form-label text-white">E-mail</label>
                        <input type="email" class="form-control" name="emailCadastro" id="emailCadastro" required>
                        <div class="invalid-feedback">Por favor, insira um e-mail válido.</div>
                    </div>

                    <!-- 🔐 Senha -->
                    <div class="mb-3 text-start">
                        <label for="senhaCadastro" class="form-label text-white">Criar senha de acesso</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="senhaCadastro" name="senhaCadastro" required>
                            <button type="button" class="btn btn-warning" id="toggleSenha">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Por favor, insira sua senha.</div>
                    </div>

                    <input type="hidden" id="chaveCadastro" name="chaveCadastro" value="<?php echo $encChave; ?>">

                    <!-- 🎯 Botão -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-gradient btn-lg mt-4 shadow">
                            <i class="bi bi-check-circle me-2"></i>Continuar para pagamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        document.querySelectorAll('input[name="tipoAssinatura"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById("tituloassinatura").textContent = this.value;
                document.getElementById("assinatura").value = this.value;
            });
        });

        document.getElementById('toggleSenha').addEventListener('click', function() {
            const senha = document.getElementById('senhaCadastro');
            const icon = this.querySelector('i');
            senha.type = senha.type === "password" ? "text" : "password";
            icon.classList.toggle("bi-eye");
            icon.classList.toggle("bi-eye-slash");
        });

        function rolarParaCadastro() {
            const destino = document.getElementById("nomeCadastro");
            if (destino) {
                destino.scrollIntoView({
                    behavior: "smooth",
                    block: "center"
                });
            }
        }
    </script>
</section>