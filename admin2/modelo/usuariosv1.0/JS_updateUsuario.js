(function () {
    let bsToast;

    function showToast(msg, tipo = 'primary') {
        const $t = document.getElementById('toastMsg');
        $t.classList.remove('text-bg-primary', 'text-bg-success', 'text-bg-danger', 'text-bg-warning');
        $t.classList.add('text-bg-' + tipo);
        $t.querySelector('.toast-body').innerHTML = msg;
        if (bsToast) bsToast.hide();
        setTimeout(() => {
            bsToast = new bootstrap.Toast($t, {
                delay: 2800
            });
            bsToast.show();
        }, 120);
    }

    // ===== Preview + habilitar upload =====
    const inputFoto = document.getElementById('arquivoFoto');
    const btnUpload = document.getElementById('btnUploadFoto');
    const imgFoto = document.getElementById('fotoAluno');

    inputFoto.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            const f = this.files[0];
            // preview
            const reader = new FileReader();
            reader.onload = e => {
                imgFoto.src = e.target.result;
            };
            reader.readAsDataURL(f);
            btnUpload.disabled = false;
        } else {
            btnUpload.disabled = true;
        }
    });

    // ===== Upload da nova foto =====
    btnUpload.addEventListener('click', function () {
        const file = inputFoto.files && inputFoto.files[0];
        if (!file) {
            showToast('Selecione uma imagem.', 'warning');
            return;
        }

        const fd = new FormData();
        fd.append('idUsuario', '<?= (int)$codigoCadastro ?>');
        fd.append('pastasc', document.getElementById('pastascAtual').value);
        fd.append('foto', file);

        fetch('usuariosv1.0/ajax_upload_foto_aluno.php', {
            method: 'POST',
            body: fd
        })
            .then(r => r.json())
            .then(j => {
                if (j.status === 'ok') {
                    // Atualiza campo imagem200 e URL pública
                    document.getElementById('imagem200').value = j.nomeArquivo;
                    imgFoto.src = j.urlPublica; // já com cache-buster no backend
                    btnUpload.disabled = true;
                    inputFoto.value = '';
                    showToast('Foto atualizada com sucesso!', 'success');
                } else {
                    showToast(j.msg || 'Erro ao enviar imagem.', 'danger');
                }
            })
            .catch(() => showToast('Falha na comunicação no upload.', 'danger'));
    });

    // ===== Salvar dados =====
    document.getElementById('formEditarAluno').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        fetch('usuariosv1.0/ajax_update_aluno.php', {
            method: 'POST',
            body: fd
        })
            .then(r => r.json())
            .then(j => {
                if (j.status === 'ok') {
                    showToast(j.msg, 'success');
                } else {
                    showToast(j.msg || 'Erro ao salvar.', 'danger');
                }
            })
            .catch(() => showToast('Falha na comunicação.', 'danger'));
    });

    // ===== Alterar senha (admin) =====
    document.getElementById('formSenha').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        const s1 = fd.get('novaSenha').trim();
        const s2 = fd.get('confirmaSenha').trim();
        if (!s1 || s1 !== s2) {
            showToast('Senhas vazias ou diferentes.', 'warning');
            return;
        }
        fetch('usuariosv1.0/ajax_update_senha_aluno.php', {
            method: 'POST',
            body: fd
        })
            .then(r => r.json())
            .then(j => {
                if (j.status === 'ok') {
                    this.reset();
                    showToast(j.msg, 'success');
                } else {
                    showToast(j.msg || 'Erro ao alterar senha.', 'danger');
                }
            })
            .catch(() => showToast('Falha na comunicação.', 'danger'));
    });

    // ===== Excluir aluno =====
    document.getElementById('btnExcluirAluno').addEventListener('click', function () {
        if (!confirm('Tem certeza que deseja excluir este aluno e TODOS os seus dados relacionados? Esta ação não pode ser desfeita.')) {
            return;
        }
        const fd = new FormData();
        fd.append('idUsuario', '<?= (int)$codigoCadastro ?>');
        fetch('usuariosv1.0/ajax_delete_aluno.php', {
            method: 'POST',
            body: fd
        })
            .then(r => r.json())
            .then(j => {
                if (j.status === 'ok') {
                    showToast(j.msg, 'success');
                    setTimeout(() => {
                        window.location.href = 'alunos.php';
                    }, 1200);
                } else {
                    showToast(j.msg || 'Erro ao excluir.', 'danger');
                }
            })
            .catch(() => showToast('Falha na comunicação.', 'danger'));
    });

})();
