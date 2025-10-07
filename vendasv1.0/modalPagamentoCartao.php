 <div class="modal fade" id="modalCartao" tabindex="-1" aria-labelledby="modalCartaoLabel" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content bg-dark text-white">
             <div class="modal-header border-0">
                 <h5 class="modal-title" id="modalCartaoLabel">Pagamento com Cartão</h5>
                 <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
             </div>
             <div class="modal-body text-center gp-4">
                 <p class="small mb-4">Escolha uma das opções abaixo:</p>
                 <?php if ($plano == 'Plano Anual'): ?>



                     <div><?= $linkpagseguro; ?></div>
                     <div style="margin-top: 40px;"><?= $linkmercadopago; ?></div>

                 <?php else: ?>

                     <div>Ou pagar com</div>

                     <div><?= $linkpagsegurovitalicia; ?></div>
                     <div style="margin-top: 40px;"><?= $linkmercadopagovitalicio; ?></div>
                 <?php endif; ?>
             </div>
         </div>
     </div>
 </div>