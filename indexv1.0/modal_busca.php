  <!-- Modal -->
  <div class="modal fade" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content text-center">
              <div class="modal-header">
                  <h3 class="modal-title w-100" id="welcomeModalLabel">
                      <img src="https://professoreugenio.com/img/ideia.gif" class="img-fluid mb-3" alt="Lâmpada" style="height: 60px;width: auto;"> <?php echo saudacao();  ?> Seja bem vindo!
                  </h3>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <p>O que você deseja encontrar no site?</p>
                  <div class="d-grid gap-2 mb-3"></div>
                  <a href="?#cursos" class="btn botoesextraspesquisa d-block mb-2">Curso On-line</a>
                  <a href="?#cursoslivres" class="btn botoesextraspesquisa d-block mb-2">Cursos Gratuitos</a>
                  <a href="https://professoreugenio.com/action.php?idpage=ZFZDdkVwN2RDa0plVWdienNUQTRqdz09" class="btn botoesextraspesquisa d-block mb-2">Contato-Serviços  Designer -Anúncios</a>
                  <?php if (!empty($codigoUser)): ?>

                  <?php endif; ?>
              </div>

              <div class="input-group" style="margin: 10px;">

                  <input type="text" id="searchQuery" class="form-control " placeholder="Digite sua pesquisa">
                  <button class="btn btn-search" type="button" id="searchButton">
                      <i class="bi bi-search"></i>
                  </button>


              </div>
          </div>
      </div>
  </div>
  </div>