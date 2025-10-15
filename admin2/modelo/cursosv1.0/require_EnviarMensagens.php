<?php
$idUsuario = $idUsuario ?? $idAluno;
$nomeAluno = $nomeAluno ?? $idAluno;


?>

<div class="col-md-3 text-end">
    <div class="dropdown">
        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuBtn<?= $idAluno ?>" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-send"></i> Enviar Mensagem
        </button>
        <!-- Botão na lista de alunos -->
        <button class="btn btn-outline-success btn-sm abrirPagamentoBtn"
            data-idusuario="<?= $idUsuario; ?>"
            data-idturma="<?= $idTurma; ?>"
            data-nomealuno="<?= htmlspecialchars($nomeAluno); ?>">
            <i class="bi bi-currency-dollar"></i> Pagamento
        </button>

        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuBtn<?= $idAluno ?>">
            <?php if ($temWhats): ?>
                <li>
                    <a class="dropdown-item" target="_blank"
                        href="<?= linkWhats($row['celular'], $msgDepoimento) ?>">
                        <i class="bi bi-whatsapp text-success"></i> Depoimento
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" target="_blank"
                        href="<?= linkWhats($row['celular'], $msgSaudacao) ?>">
                        <i class="bi bi-whatsapp text-success"></i> WhatsApp Saudação
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" target="_blank"
                        href="<?= linkWhats($row['celular'], $msgSenha) ?>">
                        <i class="bi bi-key"></i> WhatsApp Recuperar Senha
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" target="_blank"
                        href="<?= linkWhats($row['celular'], $msgRedes) ?>">
                        <i class="bi bi-instagram"></i> WhatsApp Siga nas Redes
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" target="_blank"
                        href="<?= $linkAcessoWhats ?>">
                        <i class="bi bi-clock-history text-warning"></i> WhatsApp Último Acesso/Motivação
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
            <?php endif; ?>
            <li>
                <a class="dropdown-item" target="_blank" href="<?= $emailPromo ?>">
                    <i class="bi bi-envelope-paper"></i> E-mail Promoção de Cursos
                </a>
            </li>
            <li>
                <a class="dropdown-item" target="_blank" href="<?= $emailMotiv ?>">
                    <i class="bi bi-emoji-smile"></i> E-mail Motivacional
                </a>
            </li>
        </ul>
    </div>
</div>