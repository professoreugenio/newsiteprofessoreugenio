<h5>Editando Foto</h5>

<form style="margin-left:auto;margin-right:auto; text-align: center;" id="idformupdate"
    method="post" enctype="multipart/form-data">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-sm-12" id="msgredesocial">
                <div style="position: relative;">
                    <div class="input-group"
                        style="display: flex; flex-direction: column; align-items: center;">
                        <input type="file" id="imageInput" name="imageInput" accept="image/*"
                            style="display: none;">
                        <label for="imageInput"
                            style="background-color: #007bff; color: white; padding: 10px 20px; border-radius: 5px; cursor: pointer; display: inline-flex; align-items: center; gap: 10px; font-weight: bold;">
                            <i class="fa fa-upload"></i> Escolher Foto
                        </label>
                        <button type="button" id="btenviafoto" class="btn btn-success"
                            style="display: none; margin-top: 10px;">ENVIAR FOTO</button>
                    </div>
                    <div id="imageContainer" style="margin-top: 10px;"></div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- <script src="perfilv2.0/JS_updatefoto.js?<?= time(); ?>"></script> -->