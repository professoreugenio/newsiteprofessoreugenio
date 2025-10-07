<?php
// BodyAfiliadoformProdutoNovo.php
// Requisitos: $con (PDO), jQuery, jquery.mask, Bootstrap 5+, Bootstrap Icons, AOS já carregados fora.

?>
<div class="container-fluid" data-aos="fade-up">
    <div class="row g-4">
        <!-- Coluna ESQUERDA: Formulário de cadastro -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="m-0"><i class="bi bi-plus-circle me-2"></i>Nova Campanha Afiliados</h5>
                    </div>

                    <form id="formProdutoNovo" autocomplete="off">
                        <!-- Nome do produto -->
                        <div class="mb-3">
                            <label class="form-label">Nome do produto</label>
                            <input type="text" class="form-control" name="nomeap" required maxlength="200" placeholder="Ex.: Curso Excel + IA">
                        </div>

                        <!-- Novo campo: URL do produto -->
                        <div class="mb-3">
                            <label for="urlproduto" class="form-label">URL do produto</label>
                            <input type="url" class="form-control" name="urlproduto" id="urlproduto"
                                placeholder="https://exemplo.com/seu-produto" maxlength="500">
                            <div class="form-text">Informe a URL completa (incluindo https://).</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Valor (R$)</label>
                                <input type="text" class="form-control money" name="valorap" placeholder="0,00" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Comissão (R$)</label>
                                <input type="text" class="form-control money" name="comissaoap" placeholder="0,00" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label d-block">Visibilidade</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="visivelap" name="visivelap" checked>
                                    <label class="form-check-label" for="visivelap">Visível</label>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            A pasta do produto será gerada automaticamente no cadastro (ex.: <code>Set202509_101757558570</code>).
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check2-circle me-1"></i> Cadastrar
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-eraser me-1"></i> Limpar
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- Coluna DIREITA: Imagens (desabilitadas até salvar) -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="mb-3"><i class="bi bi-images me-2"></i>Imagens da Camapnha</h6>
                    <div class="text-muted small mb-3">
                        As imagens serão habilitadas após salvar o produto.
                        <div>Diretório: <code>fotos/produtosafiliados/&lt;pasta&gt;/imagem</code></div>
                    </div>

                    <div class="row g-3 opacity-50" aria-disabled="true">
                        <div class="col-6">
                            <div class="border rounded-3 p-3 text-center">
                                <div class="fw-semibold">1080×1920</div>
                                <div class="small text-secondary">Retrato (Stories)</div>
                                <button class="btn btn-outline-secondary btn-sm mt-2" disabled>
                                    <i class="bi bi-upload me-1"></i> Enviar
                                </button>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-3 p-3 text-center">
                                <div class="fw-semibold">1024×1024</div>
                                <div class="small text-secondary">Quadrado (Feed)</div>
                                <button class="btn btn-outline-secondary btn-sm mt-2" disabled>
                                    <i class="bi bi-upload me-1"></i> Enviar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3">
                        Após salvar, você será redirecionado para a edição, onde poderá enviar as imagens.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // máscara de moeda (padrão do Eugênio)
    $(function() {
        if ($.fn.mask) {
            $('.money').mask('000.000.000,00', {
                reverse: true
            });
        }
    });

    // Submissão AJAX - cria produto
    $('#formProdutoNovo').on('submit', function(e) {
        e.preventDefault();

        const form = this;
        const fd = new FormData(form);

        // normaliza visível
        fd.set('visivelap', $('#visivelap').is(':checked') ? '1' : '0');

        // envia para o backend criar pasta, gravar e retornar { ok:true, idenc:'...' }
        $.ajax({
            url: 'afiliados1.0/ajax_afiliadosProdutoInsert.php',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(r) {
                if (r && r.ok) {
                    // redireciona para edição com id encryptado
                    window.location.href = 'sistema_afiliadosProdutosEditar.php?id=' + encodeURIComponent(r.idenc);
                } else {
                    alert(r && r.msg ? r.msg : 'Não foi possível cadastrar o produto.');
                }
            },
            error: function(xhr) {
                alert('Erro ao enviar. ' + (xhr.responseText || ''));
            }
        });
    });
</script>