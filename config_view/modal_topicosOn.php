<div class="modal fade show" id="modalLinks" tabindex="-1" aria-hidden="true"
    style="display: block; background: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog">
        <div class="modal-content p-3">
            <div class="modal-header">
                <h6>Tópicos desta lição

                    <?php echo $aut;  ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="modal-title"><?php echo $tituloPublicacao;  ?></h6>
                <ul class="list-group" id="topics-list">
                </ul>
            </div>
        </div>
    </div>
</div>