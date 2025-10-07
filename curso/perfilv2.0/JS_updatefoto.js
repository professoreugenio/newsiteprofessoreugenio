$(document).ready(function () {

    document.getElementById('imageInput').addEventListener('change', function () {
        var input = this;
        var container = document.getElementById('imageContainer');
        var button = document.getElementById('btenviafoto');

        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                container.innerHTML = '<img src="' + e.target.result +
                    '" alt="Selected Image" style="max-width:100%; height:auto; border: 2px solid #007bff; border-radius: 10px; padding: 5px;">';
                button.style.display = 'inline-block'; // Exibe o botão
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            container.innerHTML = ''; // Limpa o container se o usuário limpar a seleção
            button.style.display = 'none'; // Oculta o botão
        }
    });



    $("#imageContainer").load("perfilv2.0/perfil_loadfoto.php");
    $(document).on('click', '#btenviafoto', function () {
        $("#imageContainer").html(
            '<div class="spinner-border text-success" style="width: 1.8rem; height: 1.8rem;" role="status" role="status"></div>'
        );
        var img = document.getElementById("imageInput").files[0];
        document.getElementById("imageContainer").value = "";
        if ($('input[name="imageInput"]').val()) {
            var form_data = new FormData();
            form_data.append("imguser", img);
            $.ajax({
                url: "perfilv2.0/perfil_enviafoto.php",
                type: "post",
                data: form_data,
                contentType: false,
                cache: false,
                processData: false,
                success: function (d) {
                    $("#imageContainer").load(
                        "perfilv2.0/perfil_loadfoto.php");

                    setTimeout(function () {
                        // $("#result").html(
                        //     "Inserido com sucesso!");
                        // window.open("configuracoes_foto.php",
                        //     "_self");
                    }, 500);

                }
            });
        } else {
            $("#imageContainer").load("perfilv2.0/perfil_loadfoto.php");
        }
    });
});
