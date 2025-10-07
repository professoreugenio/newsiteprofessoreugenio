
<form action="" id="idformupdate" method="post" enctype="multipart/form-data">

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div>
                    <?php if (!empty($_SESSION['resupdperfil'])) {
                        echo $_SESSION['resupdperfil'];
                    } ?>
                </div>
                <div id="colconteudo">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="bi bi-person"></i>
                        </span>
                        <input id="nome" name="nome" value="<?php echo $rwUser['nome']; ?>"
                            type="text" class="form-control" placeholder="Username"
                            aria-label="Username" aria-describedby="basic-addon1">
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="bi bi-calendar"></i>
                        </span>
                        <input id="datanasc" name="datanasc"
                            value="<?php echo $rwUser['datanascimento_sc'] ?? ''; ?>"
                            type="date" class="form-control"
                            aria-label="Username" aria-describedby="basic-addon1">

                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input id="email" readonly name="email"
                            value="<?php echo $rwUser['email']; ?>" type="email"
                            class="form-control" placeholder="Email" aria-label="Email"
                            aria-describedby="basic-addon1">
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="bi bi-telephone"></i>
                        </span>
                        <input id="celular" maxlength="11" name="celular"
                            value="<?php echo $rwUser['celular']; ?>" type="text"
                            class="form-control" placeholder="85999999999 sem traço"
                            aria-label="Telefone" aria-describedby="basic-addon1">
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="bi bi-key"></i>
                        </span>
                        <input id="senhaatual" name="senhaatual" type="password"
                            class="form-control" placeholder="Senha atual" aria-label="Senha Atual">
                        <button class="btn btn-outline-secondary" type="button"
                            onclick="togglePassword('senhaatual', 'eyeIcon1')">
                            <i id="eyeIcon1" class="bi bi-eye"></i>
                        </button>
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="bi bi-key"></i>
                        </span>
                        <input id="senhanova" name="senhanova" type="password" class="form-control"
                            placeholder="Senha nova" aria-label="Senha Nova">
                        <button class="btn btn-outline-secondary" type="button"
                            onclick="togglePassword('senhanova', 'eyeIcon2')">
                            <i id="eyeIcon2" class="bi bi-eye"></i>
                        </button>
                    </div>

                    <div class="input-group mb-3">
                        <legend style="font-size: 14px;">Insira senha atual se for alterar o seu
                            e-mail</legend>
                        <span class="input-group-text" id="basic-addon1">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input id="emailnovo" readonly name="emailnovo" value="" type="email"
                            class="form-control" placeholder="Emailnovo" aria-label="Emailnovo"
                            aria-describedby="basic-addon1">
                    </div>

                    <script>
                        function togglePassword(inputId, eyeIconId) {
                            let input = document.getElementById(inputId);
                            let icon = document.getElementById(eyeIconId);
                            if (input.type === "password") {
                                input.type = "text";
                                icon.classList.remove("bi-eye");
                                icon.classList.add("bi-eye-slash", "text-warning");
                            } else {
                                input.type = "password";
                                icon.classList.remove("bi-eye-slash", "text-warning");
                                icon.classList.add("bi-eye");
                            }
                        }
                    </script>

                    <div class="" style="position: relative;">
                        <div class="input-group">
                            <button id="btnupdate" type="button" name="updateperfil" value="upload"
                                class="btn btn-success">Atualizar <i
                                    class="bi bi-save"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>