<?php
// Exemplo: Verificação se hoje é o aniversário do usuário
$hoje = date('m-d'); // Formato mês-dia
$aniversario = !empty($dataaniversario) ? date('m-d', strtotime($dataaniversario)) : '';

$mostrarModal = ($hoje === $aniversario);
?>

<?php foreach ($aniversariantes as $aniv): ?>
    <div class="modal fade" id="modalAniversario<?= md5($aniv['nome_aluno']) ?>" tabindex="-1" aria-labelledby="modalAniversarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-3" style="border-radius: 15px; background: linear-gradient(135deg, #ff8a00, #e52e71); color: white;">

                <div class="modal-header border-0">
                    <h5 class="modal-title w-100 fw-bold">
                        🎉 Feliz Aniversário! 🎂
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <img src="https://cdn-icons-png.flaticon.com/512/2917/2917995.png" alt="Festa" style="width: 80px;">
                    <h4 class="mt-3">Parabéns, <strong><?= htmlspecialchars($aniv['nome_aluno']) ?></strong>! 🥳</h4>
                    <p class="mt-2">
                        Sua turma <strong><?= htmlspecialchars($aniv['nome_turma']) ?></strong> deseja muito sucesso, saúde e alegrias no seu dia especial! 💖
                    </p>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light text-dark fw-bold" data-bs-dismiss="modal">
                        Obrigado(a)! 💝
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php if (!empty($aniversariantes)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php foreach ($aniversariantes as $aniv): ?>
                var modal<?= md5($aniv['nome_aluno']) ?> = new bootstrap.Modal(document.getElementById('modalAniversario<?= md5($aniv['nome_aluno']) ?>'));
                modal<?= md5($aniv['nome_aluno']) ?>.show();
            <?php endforeach; ?>
        });
    </script>
<?php endif; ?>