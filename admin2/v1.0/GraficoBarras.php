<!-- GRÁFICO CHART.JS -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-bar-chart-line-fill me-2"></i> Estatísticas</h5>
                <canvas id="graficoDashboard" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    // Gráfico
    const ctx = document.getElementById('graficoDashboard').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Usuários', 'Cursos', 'Acessos', 'Alunos', 'Mensagens'],
            datasets: [{
                label: 'Quantidade',
                data: [132, 18, 320, 97, 7],
                backgroundColor: '#0d6efd',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>