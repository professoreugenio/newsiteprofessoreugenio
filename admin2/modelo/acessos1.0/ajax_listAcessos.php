<?php
// acessos1.0/ajax_listAcessos.php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: text/html; charset=UTF-8');

$data = isset($_POST['data']) ? trim($_POST['data']) : date('Y-m-d');
$q    = isset($_POST['q']) ? trim($_POST['q']) : '';

try {
  $pdo = config::connect();

  // Monta filtro básico por data e busca
  $where = " r.datara = :data ";
  $params = [':data' => $data];

  if ($q !== '') {
    // busca por nome do aluno, nome da turma e url
    $where .= " AND ( c.nome LIKE :q OR COALESCE(t.nometurma, CONCAT('Turma ', r.idturmara)) LIKE :q OR COALESCE(u.urlra, '') LIKE :q ) ";
    $params[':q'] = "%{$q}%";
  }

  // Query: agrupar por usuário no dia, pegar primeiro horário (min horara) e total de eventos
  // Tabelas:
  // - a_site_registraacessosvendas r (idusuariora, idturmara, ipra, idregistrourl, urlra, datara, horara, horafinalra)
  // - new_sistema_cadastro c (codigocadastro, nome, pastasc, imagem50)
  // - new_sistema_cursos_turmas t (idturma, nometurma)  <-- ajuste o nome do campo se necessário
  // - a_site_registraurl u (codigoregistrourl, urlra)   <-- se preferir mapear por idregistrourl
  $sql = "
    SELECT
      c.codigocadastro            AS idusuario,
      c.nome                      AS nome,
      c.pastasc                   AS pastaUsuario,
      c.imagem50                  AS img50,
      r.idturmara                 AS idturma,
      COALESCE(t.nometurma, CONCAT('Turma ', r.idturmara)) AS turma_nome,
      MIN(CONCAT(r.datara, ' ', r.horara)) AS primeiro_acesso,
      COUNT(*)                    AS total_eventos
    FROM a_site_registraacessosvendas r
    LEFT JOIN new_sistema_cadastro c
      ON c.codigocadastro = r.idusuariora
    LEFT JOIN new_sistema_cursos_turmas t
      ON t.codigoturma = r.idturmara
    LEFT JOIN a_site_registraurl u
      ON u.codigoregistrourl = r.idregistrourl
    WHERE {$where}
    GROUP BY c.codigocadastro, c.nome, c.pastasc, c.imagem50, r.idturmara, t.nometurma
    ORDER BY primeiro_acesso ASC
    LIMIT 500
  ";

  $stmt = $pdo->prepare($sql);
  foreach ($params as $k => $v) $stmt->bindValue($k, $v);
  $stmt->execute();

  if ($stmt->rowCount() === 0) {
    echo '<div class="col-12"><div class="alert alert-warning mb-0">Nenhum acesso encontrado para a data selecionada.</div></div>';
    exit;
  }

  while ($rw = $stmt->fetch(PDO::FETCH_ASSOC)) {

    // Foto do usuário
    $img = 'usuario.jpg';
    if (!empty($rw['img50']) && $rw['img50'] !== 'usuario.jpg') {
      // Padrão do projeto: /fotos/usuarios/{pastasc}/{imagem50}
      $img = '/fotos/usuarios/' . ($rw['pastaUsuario'] ?? '') . '/' . ($rw['img50'] ?? 'usuario.jpg');
    } else {
      // fallback padrão
      $img = '/fotos/usuarios/usuario.jpg';
    }

    // Link para dados do usuário (usa encrypt se existir)
    $hrefUser = 'alunos_editar.php?id=';
    if (function_exists('encrypt')) {
      $hrefUser .= encrypt((string)$rw['idusuario'], 'e');
    } else {
      $hrefUser .= (string)$rw['idusuario'];
    }

    // Data/hora inicial formatada
    $dtIni = $rw['primeiro_acesso'] ? date('d/m/Y H:i', strtotime($rw['primeiro_acesso'])) : '-';

    echo '<div class="col-12 col-md-6 col-xl-4" data-aos="fade-up">';
    echo '<div class="card card-acesso bg-body-tertiary border-0 shadow-sm rounded-4 h-100">';
    echo '<div class="card-body d-flex gap-3 align-items-center">';
    echo '<img src="' . htmlspecialchars($img) . '" class="avatar-50 flex-shrink-0" alt="Foto">';
    echo '<div class="flex-grow-1">';
    echo '<div class="d-flex align-items-center justify-content-between">';
    echo '<a href="' . htmlspecialchars($hrefUser) . '" class="fw-semibold text-decoration-none text-truncate-1" title="Ver dados do aluno">' . htmlspecialchars($rw['nome'] ?? '—') . '</a>';
    echo '<span class="badge bg-info-subtle text-info-emphasis ms-2">' . htmlspecialchars($rw['turma_nome'] ?? 'Turma') . '</span>';
    echo '</div>';
    echo '<div class="small text-muted mt-1"><i class="bi bi-clock-history me-1"></i> Início: <strong>' . $dtIni . '</strong></div>';
    echo '<div class="small text-muted"><i class="bi bi-list-ol me-1"></i> Eventos: <strong>' . (int)$rw['total_eventos'] . '</strong></div>';
    echo '</div>';
    echo '</div>';
    echo '<div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">';
    echo '<button class="btn btn-outline-primary btn-sm w-100 btn-detalhes-acesso"';
    echo ' data-idusuario="' . (int)$rw['idusuario'] . '">';
    echo '<i class="bi bi-eye"></i> Ver detalhes';
    echo '</button>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
  }
} catch (Exception $e) {
  echo '<div class="col-12"><div class="alert alert-danger mb-0">Erro: ' . htmlspecialchars($e->getMessage()) . '</div></div>';
}
