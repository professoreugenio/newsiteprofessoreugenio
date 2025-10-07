   <nav class="navbar navbar-expand-lg">
       <div class="container">
           <a class="navbar-brand" href="#">Minhas Turmas</a>
       </div>
   </nav>

   <?php echo $dadosDecodificados;  ?>
   <section id="Corpo" class="bg-dark mt-4">
       <div class="container text-center">
           <!-- Saudação do Usuário -->
           <div>
               <!-- Texto de acolhimento -->
               <div class="texto-acolhimento">
                   <h3>Seelcione um módulo abaixo</h3>
                   <p><a class="btn btn-success btn-sm" href="../curso/modulos.php">Turmas</a></p>
               </div>
           </div>
       </div>
   </section>

   <div class="container">
       <div class="cards-container">
           <?php if ($codigoUser == 1): ?>
               <?php echo $idCurso;  ?>
               <?php require 'config_curso1.0/ListaModulos3.php'; ?>
           <?php else: ?>
               <?php require 'config_curso1.0/ListaModulos3.php'; ?>
           <?php endif; ?>
       </div>
   </div>

   <script src="config_turmas1.0/JS_accessturma.js"></script>
   <script src="regixv2.0/acessopaginas.js?<?= time(); ?>"></script>
   <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
   <!-- Função para o botão Sair -->
   <script src="config_default1.0/JS_logoff.js"></script>
   <script>
       function abrirPagina(url) {
           window.open(url, '_self');
       }
   </script>