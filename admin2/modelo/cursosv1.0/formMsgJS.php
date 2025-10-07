  <script>
      $(function() {
          // Inicializa Summernote
          $('#mensagem').summernote({
              height: 190,
              lang: 'pt-BR'
          });

          // Toast ÚNICO, sempre sobrescreve antes de mostrar
          let bsToast;

          function showToast(msg, tipo = 'primary') {
              let $toast = $('#toastMsg');
              $toast.removeClass('text-bg-primary text-bg-success text-bg-danger text-bg-warning')
                  .addClass('text-bg-' + tipo);
              $toast.find('.toast-body').html(msg);

              if (bsToast) {
                  bsToast.hide(); // Garante fechar anterior
                  setTimeout(() => {
                      bsToast = new bootstrap.Toast($toast[0], {
                          delay: 2800
                      });
                      bsToast.show();
                  }, 250);
              } else {
                  bsToast = new bootstrap.Toast($toast[0], {
                      delay: 2800
                  });
                  bsToast.show();
              }
          }

          // Atualiza lista de padrões
          function listarPadroes(callback) {
              $.getJSON('cursosv1.0/ajax_ListarMsgsUsuariosPadrao.php', function(dados) {
                  let $sel = $('#msgPadraoSelect').empty();
                  $sel.append('<option value="">Nova mensagem...</option>');
                  dados.forEach(function(msg) {
                      $sel.append('<option value="' + msg.codigopadraomsg + '">' + msg.titulomsgPM + '</option>');
                  });
                  if (typeof callback === "function") callback();
              });
          }
          listarPadroes();

          // Refresh manual (botão ao lado do select)
          $('#btnRefreshPadroes').on('click', function() {
              location.reload(true);
          });

          // Carregar mensagem padrão ao selecionar
          $('#msgPadraoSelect').on('change', function() {
              let id = $(this).val();
              $('#btnExcluirMsg').prop('disabled', !id); // Só habilita se tiver msg selecionada
              if (id) {
                  $.getJSON('cursosv1.0/ajax_GetMsgPadrao.php?id=' + id, function(msg) {
                      $('#titulo').val(msg.titulomsgPM);
                      $('#mensagem').summernote('code', msg.textoPM);
                      showToast('Mensagem carregada.', 'success');
                  });
              } else {
                  $('#titulo').val('');
                  $('#mensagem').summernote('code', '');
              }
          });


          // Script para excluir mensagem
          $('#btnExcluirMsg').on('click', function() {
              let id = $('#msgPadraoSelect').val();
              if (!id) {
                  showToast('Selecione uma mensagem para excluir.', 'warning');
                  return;
              }
              if (!confirm('Tem certeza que deseja excluir esta mensagem?')) return;
              $.post('cursosv1.0/ajax_DeleteMsgPadrao.php', {
                  id: id
              }, function(ret) {
                  let res = typeof ret === "string" ? JSON.parse(ret) : ret;
                  if (res.status == 'ok') {
                      showToast(res.msg, 'success');
                      $('#msgPadraoSelect').val('');
                      $('#titulo').val('');
                      $('#mensagem').summernote('code', '');
                      $('#btnExcluirMsg').prop('disabled', true);
                      listarPadroes();
                  } else {
                      showToast(res.msg, 'danger');
                  }
              });
          });

          // Copiar mensagem (apenas o texto limpo, sem HTML)
          $('#btnCopiarMsg').on('click', function() {
              // Pega o código do Summernote (com HTML)
              let msgHTML = $('#mensagem').summernote('code');
              // Remove tags HTML (fica só o texto)
              let tempDiv = document.createElement("div");
              tempDiv.innerHTML = msgHTML;
              let msgText = tempDiv.innerText.trim();

              // Usa a API moderna de clipboard
              if (navigator.clipboard && window.isSecureContext) {
                  navigator.clipboard.writeText(msgText)
                      .then(() => {
                          showToast('Mensagem copiada para a área de transferência!', 'success');
                      })
                      .catch(() => {
                          showToast('Erro ao copiar mensagem!', 'danger');
                      });
              } else {
                  // Fallback antigo (em caso de navegador não suportado)
                  let temp = document.createElement('textarea');
                  temp.value = msgText;
                  document.body.appendChild(temp);
                  temp.select();
                  try {
                      document.execCommand('copy');
                      showToast('Mensagem copiada para a área de transferência!', 'success');
                  } catch (err) {
                      showToast('Erro ao copiar mensagem!', 'danger');
                  }
                  document.body.removeChild(temp);
              }
          });

          // Copiar mensagem
          //   $('#btnCopiarMsg').on('click', function() {
          //       let msg = $('#mensagem').summernote('code');
          //       // Copia apenas o texto (sem HTML)
          //       let temp = document.createElement('textarea');
          //       temp.value = msg.replace(/<[^>]+>/g, '').trim();
          //       document.body.appendChild(temp);
          //       temp.select();
          //       document.execCommand('copy');
          //       document.body.removeChild(temp);
          //       showToast('Mensagem copiada para a área de transferência!', 'success');
          //   });

          // Salvar mensagem (só no botão)
          $('#btnSalvarMsg').on('click', function(e) {
              e.preventDefault();
              let titulo = $('#titulo').val().trim();
              let mensagem = $('#mensagem').summernote('code').trim();
              let acao = 'salvar';
              if (!titulo || !mensagem) {
                  showToast('Preencha o título e a mensagem.', 'warning');
                  return;
              }
              $.post('cursosv1.0/ajax_InsertMsgUsuariosPadrao.php', {
                  titulo: titulo,
                  mensagem: mensagem,
                  acao: acao
              }, function(ret) {
                  let res = typeof ret === "string" ? JSON.parse(ret) : ret;
                  if (res.status == 'ok') {
                      showToast(res.msg, 'success');
                      listarPadroes();
                  } else {
                      showToast(res.msg, 'danger');
                  }
              });
          });

          // Enviar mensagem (apenas no submit do form)
          $('#formMensagem').on('submit', function(e) {
              e.preventDefault();
              let titulo = $('#titulo').val().trim();
              let mensagem = $('#mensagem').summernote('code').trim();
              let acao = 'enviar';
              if (!titulo || !mensagem) {
                  showToast('Preencha o título e a mensagem.', 'warning');
                  return;
              }
              $.post('cursosv1.0/ajax_InsertMsgUsuariosPadrao.php', {
                  titulo: titulo,
                  mensagem: mensagem,
                  acao: acao
              }, function(ret) {
                  let res = typeof ret === "string" ? JSON.parse(ret) : ret;
                  if (res.status == 'ok') {
                      showToast(res.msg, 'success');
                      listarPadroes();
                  } else {
                      showToast(res.msg, 'danger');
                  }
              });
          });
      });
  </script>