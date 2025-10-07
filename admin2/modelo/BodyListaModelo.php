<!-- Lista de Cursos -->
<?php
// Simulação de cursos
$cursos = [
    ['id' => 1, 'nome' => 'Power BI do Zero', 'status' => 1],
    ['id' => 2, 'nome' => 'Excel Profissional', 'status' => 0],
    ['id' => 3, 'nome' => 'Introdução ao Photoshop', 'status' => 1],
];

?>
<ul class="list-group shadow-sm">
    <?php foreach ($cursos as $curso): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <!-- Nome do curso -->
            <a href="editar_curso.php?id=<?= $curso['id']; ?>" class="text-decoration-none fw-semibold">
                <?= $curso['nome']; ?>
            </a>

            <!-- Botão de status -->
            <button class="btn btn-sm <?= $curso['status'] ? 'btn-success' : 'btn-secondary'; ?>"
                title="Clique para alternar status"
                onclick="alternarStatus(<?= $curso['id']; ?>)">
                <i class="bi <?= $curso['status'] ? 'bi-check-circle' : 'bi-x-circle'; ?>"></i>
                <?= $curso['status'] ? 'Online' : 'Offline'; ?>
            </button>
        </li>
    <?php endforeach; ?>
</ul>