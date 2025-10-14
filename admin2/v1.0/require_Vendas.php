<?php
$limit = 300;
$where  = 'DATE(v.datacomprasv) = CURDATE() and (v.statussv IS NULL OR v.statussv = 0)';

$sql = "
SELECT
v.codigovendas,
v.idcursosv,
v.chaveturmasv,
v.idalunosv,
v.chaveafiliadosv,
v.valorvendasv,
v.datacomprasv,
v.horacomprasv,
v.statussv,
v.tipopagamentosv,

c.nomecurso,
a.email AS email_aluno,
c.bgcolor,
a.codigocadastro,
a.senha,
a.nome AS nome_aluno,
a.celular AS cel_aluno,

af.idusuarioSA AS id_afiliado,
afc.nome AS nome_afiliado
FROM a_site_vendas v
LEFT JOIN new_sistema_cursos c ON c.codigocursos = v.idcursosv
LEFT JOIN new_sistema_cadastro a ON a.codigocadastro = v.idalunosv
LEFT JOIN a_site_afiliados_chave af
ON (af.codigochaveafiliados = v.chaveafiliadosv OR af.chaveafiliadoSA = v.chaveafiliadosv)
LEFT JOIN new_sistema_cadastro afc ON afc.codigocadastro = af.idusuarioSA
WHERE {$where}
ORDER BY v.datacomprasv DESC, v.horacomprasv DESC
LIMIT :lim
";

$stmt = $con->prepare($sql);
$stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
$stmt->execute();
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php if (temPermissao($niveladm, [1])): ?>
    <!-- Vendas -->
    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="200">
        <div class="card stat-card card-violet h-100 border-start">
            <div class="card-body d-flex flex-column gap-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center gap-2">
                        <span class="icon-badge"><i class="bi bi-bag-check-fill"></i></span>
                        <h6 class="title mb-0">Vendas</h6>
                    </div>
                    <span class="value"><?= count($vendas); ?></span>
                </div>
                <span class="label text-muted">Páginas de Venda</span>
                <a href="vendas.php?view=atuais" class="stretched-link" aria-label="Abrir Vendas"></a>
                <span class="corner"></span>
            </div>
        </div>
    </div>
<?php endif; ?>