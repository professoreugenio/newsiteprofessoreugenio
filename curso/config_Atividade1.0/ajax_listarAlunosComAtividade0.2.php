<?php
define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';

$idCurso = intval($_POST['idcurso'] ?? 0);
$idTurma = intval($_POST['idturma'] ?? 0);
$idPublicacao = intval($_POST['idpublicacao'] ?? 0);

$stmt = $con->prepare("
    SELECT c.codigocadastro, c.nomecurso, c.pastasc, c.imagem50,
        (SELECT COUNT(*) FROM a_curso_AtividadeAnexos 
         WHERE idalulnoAA = c.codigocadastro AND idpublicacacaoAA = :idpublicacao) as total
    FROM new_sistema_cadastro c
    INNER JOIN new_sistema_inscricao_PJA i ON i.codigousuario = c.codigocadastro
    WHERE i.chaveturma = :chave
");

$stmt->execute([
    ':idpublicacao' => $idPublicacao,
    ':chave' => $idTurma
]);

$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($alunos as $a) {
    $nomeCompleto = htmlspecialchars($a['nome']);
    $primeiroNome = explode(' ', $a['nome'])[0];
    $pasta = htmlspecialchars($a['pastasc']);
    $foto = (!empty($a['imagem50']) && file_exists("../../fotos/usuarios/$pasta/{$a['imagem50']}"))
        ? "../../fotos/usuarios/$pasta/{$a['imagem50']}"
        : "../../fotos/usuarios/usuario.png";

    $temAtividade = $a['total'] > 0;
    $classe = $temAtividade ? 'border border-white' : 'opacity-50';

    echo "
    <div class='text-center'>
        <img src='$foto' 
             class='rounded-circle $classe shadow-sm' 
             data-bs-toggle='tooltip' title='$nomeCompleto'
             style='width: 40px; height: 40px; object-fit: cover; cursor: pointer;' 
             onclick='carregarAtividadesAluno({$a['codigocadastro']})'>
        <div class='small mt-1 text-white fw-semibold text-truncate' style='max-width: 50px;'>$primeiroNome</div>
    </div>";
}
