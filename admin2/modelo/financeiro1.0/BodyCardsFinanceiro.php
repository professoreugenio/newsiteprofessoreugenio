<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once APP_ROOT . '/conexao/class.conexao.php';

// Data atual
$anoMesAtual = date('Y-m');

// Cálculo do saldo do mês atual
$sqlSaldo = "
    SELECT 
        SUM(CASE WHEN l.tipoLancamentos = 1 THEN f.valorCF ELSE 0 END) AS total_credito,
        SUM(CASE WHEN l.tipoLancamentos = 2 THEN f.valorCF ELSE 0 END) AS total_debito
    FROM a_curso_financeiro f
    INNER JOIN a_curso_financeiroLancamentos l 
        ON f.idLancamentoCF = l.codigolancamentos
    WHERE DATE_FORMAT(f.dataFC, '%Y-%m') = :anoMes
";
$stmtSaldo = $con->prepare($sqlSaldo);
$stmtSaldo->bindValue(':anoMes', $anoMesAtual);
$stmtSaldo->execute();
$saldo = $stmtSaldo->fetch(PDO::FETCH_ASSOC);

$credito = floatval($saldo['total_credito']);
$debito = floatval($saldo['total_debito']);
$saldoAtual = $credito - $debito;
?>

<div class="container mt-4">

    <!-- Card Saldo Atual -->
    <div class="row mb-4">
        <div class="col-md-12" data-aos="fade-up">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body text-center">
                    <h5 class="card-title text-secondary">Saldo Atual do Mês (<?= date('m/Y') ?>)</h5>
                    <h2 class="fw-bold <?= $saldoAtual >= 0 ? 'text-success' : 'text-danger' ?>">
                        R$ <?= number_format($saldoAtual, 2, ',', '.') ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Acesso -->
    <div class="row g-4">
        <div class="col-md-6 col-xl-3" data-aos="fade-up">
            <a href="curso_FinanceiroReceitas.php" class="text-decoration-none">
                <div class="card shadow-sm border-start border-4 border-success h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-arrow-down-circle-fill text-success fs-2 mb-2"></i>
                        <h5 class="card-title text-success">Créditos</h5>
                        <p class="card-text">Lançamentos de receitas</p>
                    </div>
                </div>
            </a>
        </div>


        <div class="col-md-6 col-xl-3" data-aos="fade-up">
            <a href="curso_FinanceiroDespesas.php" class="text-decoration-none">
                <div class="card shadow-sm border-start border-4 border-danger h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-arrow-up-circle-fill text-danger fs-2 mb-2"></i>
                        <h5 class="card-title text-danger">Débitos</h5>
                        <p class="card-text">Despesas e saídas</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-xl-3" data-aos="fade-up">
            <a href="curso_FinanceiroReceitasporTurma.php" class="text-decoration-none">
                <div class="card shadow-sm border-start border-4 border-success h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-arrow-down-circle-fill text-success fs-2 mb-2"></i>
                        <h5 class="card-title text-success">Créditos</h5>
                        <p class="card-text">Valor por Turma</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-xl-3" data-aos="fade-up">
            <a href="financeiro_tipos.php" class="text-decoration-none">
                <div class="card shadow-sm border-start border-4 border-warning h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-tags-fill text-warning fs-2 mb-2"></i>
                        <h5 class="card-title text-warning">Tipos</h5>
                        <p class="card-text">Categorias de lançamentos</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-xl-3" data-aos="fade-up">
            <a href="curso_FinanceiroExtrato.php" class="text-decoration-none">
                <div class="card shadow-sm border-start border-4 border-info h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-journal-text text-info fs-2 mb-2"></i>
                        <h5 class="card-title text-info">Extrato</h5>
                        <p class="card-text">Movimentações completas</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

</div>