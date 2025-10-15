<?php
// meudepoimento.php (raiz)
define('BASEPATH', true);
require_once __DIR__ . '/conexao/class.conexao.php';
require_once __DIR__ . '/autenticacao.php'; // para encrypt()/decrypt()
@date_default_timezone_set('America/Fortaleza');

// ---------- Helpers ----------
function decryptUserFromGet(): array
{
    $enc = $_GET['idUser'] ?? '';
    if ($enc === '') return [0, ''];
    try {
        $dec = encrypt($enc, 'd'); // ajuste se sua função diferir
        if (strpos($dec, '&') !== false) {
            $parts = explode('&', $dec);
            $id = (int)($parts[0] ?? 0);
        } else {
            $id = (int)$dec;
        }
        return [$id > 0 ? $id : 0, $enc];
    } catch (Throwable $e) {
        return [0, $enc];
    }
}

function montarFotoUsuarioRoot(?string $pasta, ?string $img): string
{
    $pasta = trim((string)$pasta);
    $img   = trim((string)$img);
    if ($img === 'usuario.jpg' || $img === '' || $img === null) {
        return 'fotos/usuarios/usuario.jpg';
    }
    if ($pasta !== '' && $img !== '') {
        return 'fotos/usuarios/' . $pasta . '/' . $img;
    }
    return 'fotos/usuarios/usuario.jpg';
}

// ---------- Resolve aluno a partir do GET ----------
[$idAluno, $idUserEnc] = decryptUserFromGet();
$erroUser = ($idAluno <= 0);

// ---------- Busca dados do aluno (foto + nome) ----------
$alunoNome = 'Aluno';
$alunoFoto = 'fotos/usuarios/usuario.jpg';

if (!$erroUser) {
    $stU = config::connect()->prepare("
    SELECT nome, imagem50, pastasc
    FROM new_sistema_cadastro
    WHERE codigocadastro = :id
    LIMIT 1
  ");
    $stU->execute([':id' => $idAluno]);
    if ($usr = $stU->fetch(PDO::FETCH_ASSOC)) {
        $alunoNome = $usr['nome'] ?: 'Aluno';
        // regra da foto
        if (($usr['imagem50'] ?? '') === 'usuario.jpg') {
            $alunoFoto = 'fotos/usuarios/usuario.jpg';
        } else {
            $alunoFoto = montarFotoUsuarioRoot($usr['pastasc'] ?? '', $usr['imagem50'] ?? '');
        }
    }
}

// ---------- Lista depoimentos do aluno ----------
$depos = [];
if (!$erroUser) {
    $st = config::connect()->prepare("
    SELECT codigoforum, textoCF, permissaoCF, dataCF, horaCF
    FROM a_curso_forum
    WHERE idusuarioCF = :u
    ORDER BY dataCF DESC, horaCF DESC, codigoforum DESC
    LIMIT 200
  ");
    $st->execute([':u' => $idAluno]);
    $depos = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Meu Depoimento</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Use seus assets globais; abaixo CDN apenas se precisar -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">



    <style>
        :root {
            --brand-1: #00BB9C;
            /* h1 / destaques, botões primários */
            --brand-2: #FF9C00;
            /* h2 / badges curso, botões secundários de ação */
            --bg-card: #112240;
            /* fundo de cards e modal */
            --bg-page: #0c1833;
            /* fundo da página */
            --text: #ffffff;
            --muted: #A3B1C2;
            --ring: rgba(0, 187, 156, .35);
            --border: rgba(255, 255, 255, .08);
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: radial-gradient(1200px 600px at 20% -10%, rgba(0, 187, 156, .20), transparent 60%), var(--bg-page);
            color: var(--text);
        }

        /* ---------- layout / nav ---------- */
        .nav-shadow {
            box-shadow: 0 1px 0 var(--border);
        }

        .navbar {
            background: transparent !important;
        }

        .navbar .navbar-brand {
            color: var(--text);
        }

        .navbar .navbar-brand i {
            color: var(--brand-1);
        }

        /* ---------- cards / modal ---------- */
        .card {
            background: var(--bg-card);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 14px;
        }

        .modal-content {
            background: var(--bg-card);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 16px;
        }

        .depo-card pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            color: var(--text);
        }

        /* ---------- tipografia / estados ---------- */
        a {
            color: var(--brand-1);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .text-muted,
        .form-text,
        .small,
        .badge-status {
            color: var(--muted) !important;
        }

        .badge-status {
            background: transparent;
            border: 1px solid var(--border);
            padding: .35rem .6rem;
            border-radius: 999px;
        }

        /* ---------- inputs ---------- */
        .form-control,
        .form-select,
        textarea {
            background: rgba(255, 255, 255, 0.03);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .form-control:focus,
        .form-select:focus,
        textarea:focus {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
            border-color: var(--brand-1);
            box-shadow: 0 0 0 3px var(--ring);
            outline: none;
        }

        .form-control::placeholder,
        textarea::placeholder {
            color: var(--muted);
        }

        /* ---------- botões ---------- */
        .btn {
            border-radius: 12px;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: var(--brand-1);
            border-color: var(--brand-1);
            color: #031a15;
            font-weight: 600;
        }

        .btn-primary:hover {
            filter: brightness(1.05);
        }

        .btn-success {
            background: var(--brand-2);
            border-color: var(--brand-2);
            color: #231700;
            font-weight: 600;
        }

        .btn-success:hover {
            filter: brightness(1.05);
        }

        .btn-outline-secondary {
            color: var(--muted);
            border-color: var(--border);
            background: transparent;
        }

        .btn-outline-secondary:hover {
            color: var(--text);
            border-color: var(--brand-1);
            background: rgba(0, 187, 156, 0.08);
        }

        /* ---------- avatar ---------- */
        .avatar-64 {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid var(--border);
        }

        /* ---------- FAB (novo depoimento) ---------- */
        .fab-depo {
            position: fixed;
            right: 18px;
            bottom: 18px;
            z-index: 1070;
            border-radius: 999px;
            box-shadow: 0 10px 22px rgba(0, 0, 0, .28);
            background: var(--brand-1);
            border-color: var(--brand-1);
        }

        .fab-depo:hover {
            filter: brightness(1.06);
        }

        /* ---------- toast ---------- */
        .toast {
            background: var(--bg-card);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .toast.text-bg-danger {
            background: #3b1a1a !important;
            border-color: rgba(255, 99, 99, .25) !important;
        }

        /* ---------- separadores/linhas ---------- */
        hr,
        .border,
        .card .border,
        .modal .border {
            border-color: var(--border) !important;
        }

        /* ---------- utilitários ---------- */
        .badge-curso {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            color: var(--text);
        }
    </style>
</head>

<body>

    <!-- NAV -->
    <nav class="navbar navbar-expand-lg bg-white nav-shadow">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="/">
                <i class="bi bi-chat-quote"></i> Meu Depoimento
            </a>
            <div class="ms-auto small text-muted">
                <?= $erroUser ? 'Usuário não identificado' : 'Bem-vindo!' ?>
            </div>
        </div>
    </nav>

    <main class="container my-4" style="max-width: 960px;">

        <!-- Cabeçalho do aluno -->
        <?php if ($erroUser): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i>Link inválido. Solicite novamente.</div>
        <?php else: ?>
            <div class="d-flex align-items-center gap-3 mb-4">
                <img src="<?= htmlspecialchars($alunoFoto) ?>" alt="Foto do aluno" class="avatar-64 border">
                <div>
                    <div class="fw-semibold fs-5"><?= htmlspecialchars($alunoNome) ?></div>
                    <div class="text-muted small">Conte sua experiência e ajude a valorizar o curso 🙂</div>
                </div>
            </div>

            <!-- Lista de depoimentos -->
            <h5 class="mb-3"><i class="bi bi-list-stars me-2"></i>Meus depoimentos</h5>
            <?php if (!$depos): ?>
                <div class="alert alert-light border">
                    <i class="bi bi-info-circle me-1"></i>Você ainda não enviou depoimentos.
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($depos as $d):
                        $idf  = (int)$d['codigoforum'];
                        $txt  = (string)($d['textoCF'] ?? '');
                        $perm = is_null($d['permissaoCF']) ? null : (int)$d['permissaoCF'];
                        $data = htmlspecialchars($d['dataCF'] ?? '');
                        $hora = htmlspecialchars($d['horaCF'] ?? '');
                    ?>
                        <div class="col-12">
                            <div class="card depo-card shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="me-3">
                                            <div class="small text-muted mb-2">
                                                <i class="bi bi-calendar2-week me-1"></i><?= $data ?>
                                                <i class="bi bi-dot mx-1"></i>
                                                <i class="bi bi-clock me-1"></i><?= $hora ?>
                                            </div>
                                            <pre class="mb-0"><?= htmlspecialchars($txt) ?></pre>
                                        </div>
                                        <div class="text-end">
                                            <?php if ($perm === 1): ?>
                                                <span class="badge badge-status"><i class="bi bi-unlock me-1"></i>Permissão concedida</span>
                                            <?php else: ?>
                                                <button type="button"
                                                    class="btn btn-sm btn-success btnPermitirItem"
                                                    data-id="<?= $idf ?>">
                                                    <i class="bi bi-unlock-fill me-1"></i>Permitir
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <!-- Botão flutuante: Novo depoimento -->
    <button type="button" class="btn btn-primary fab-depo" id="btnNovoDepo">
        <i class="bi bi-plus-lg"></i>
    </button>

    <!-- Modal do formulário (abre ao carregar) -->
    <div class="modal fade" id="modalNovoDepo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Novo depoimento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <?php if ($erroUser): ?>
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle me-1"></i>Link inválido. Feche este modal e solicite novamente.
                        </div>
                    <?php else: ?>
                        <form id="formDepo" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="texto" class="form-label fw-semibold">Seu depoimento</label>
                                <textarea class="form-control" id="texto" name="texto" rows="5" maxlength="4000" placeholder="Escreva aqui..." required></textarea>
                                <div class="form-text text-end"><span id="countChars">0</span>/4000</div>
                                <div class="invalid-feedback">Escreva seu depoimento.</div>
                            </div>

                            <div class="alert alert-info">
                                <div class="d-flex">
                                    <i class="bi bi-megaphone me-2"></i>
                                    <div>
                                        <strong>Permissão de uso:</strong> autorizo o professor a utilizar meu depoimento publicamente para valorizar o curso
                                        (páginas, redes sociais, anúncios, etc.).
                                    </div>
                                </div>
                                <div class="mt-2 d-flex gap-2">
                                    <button type="button" class="btn btn-success" id="btnPermitir">
                                        <i class="bi bi-check-lg me-1"></i>Permitir
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="btnNaoPermitir">
                                        <i class="bi bi-x-lg me-1"></i>Não permitir
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-1"></i>Enviar depoimento
                                </button>
                            </div>

                            <!-- Hidden -->
                            <input type="hidden" name="permissao" id="permissao" value="">
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080">
        <div id="toastDepo" class="toast text-bg-primary" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastDepoMsg">OK</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        const ID_USER_ENC = <?= json_encode($idUserEnc) ?>;

        function showToast(msg, kind) {
            const el = document.getElementById('toastDepo');
            const body = document.getElementById('toastDepoMsg');
            body.textContent = msg || 'OK';
            el.className = 'toast ' + (kind === 'err' ? 'text-bg-danger' : 'text-bg-primary');
            const t = new bootstrap.Toast(el, {
                delay: 2200
            });
            t.show();
        }

        // Abre modal no carregamento
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('modalNovoDepo');
            if (modalEl && <?= $erroUser ? 'false' : 'true' ?>) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        });

        // FAB abre modal
        $('#btnNovoDepo').on('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalNovoDepo'));
            modal.show();
        });

        // Contador de caracteres
        $(document).on('input', '#texto', function() {
            $('#countChars').text(($(this).val() || '').length);
        });

        // Define permissão
        $('#btnPermitir').on('click', () => {
            $('#permissao').val('1');
            showToast('Você autorizou o uso público.');
        });
        $('#btnNaoPermitir').on('click', () => {
            $('#permissao').val('0');
            showToast('Você não autorizou o uso público.', 'err');
        });

        // Envio do formulário: depoimento1.0/ajax_novodepoimento.php
        $('#formDepo').on('submit', function(e) {
            e.preventDefault();
            // valida permissão
            const perm = $('#permissao').val();
            if (perm !== '1' && perm !== '0') {
                showToast('Escolha Permitir ou Não permitir.', 'err');
                return;
            }
            // valida conteúdo
            const texto = ($('#texto').val() || '').trim();
            if (!texto) {
                showToast('Escreva seu depoimento.', 'err');
                return;
            }

            const dados = {
                idUserEnc: ID_USER_ENC,
                texto: texto,
                permissao: parseInt(perm, 10)
            };

            const $btn = $(this).find('button[type="submit"]').prop('disabled', true);

            $.ajax({
                url: 'depoimento1.0/ajax_novodepoimento.php',
                type: 'POST',
                data: dados,
                dataType: 'json',
                success: function(r) {
                    if (r && r.ok) {
                        showToast('Depoimento enviado. Obrigado!');
                        // limpa e fecha modal
                        $('#texto').val('');
                        $('#permissao').val('');
                        $('#countChars').text('0');
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovoDepo'));
                        if (modal) modal.hide();
                        // atualiza a página para aparecer na lista
                        setTimeout(() => location.reload(), 900);
                    } else {
                        showToast((r && r.msg) ? r.msg : 'Falha ao enviar.', 'err');
                    }
                },
                error: function() {
                    showToast('Erro de comunicação.', 'err');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        // Botão "Permitir" na lista (opcional)
        $(document).on('click', '.btnPermitirItem', function() {
            const idForum = $(this).data('id');
            const $btn = $(this).prop('disabled', true);

            $.ajax({
                url: 'depoimento1.0/ajax_permitir.php',
                type: 'POST',
                data: {
                    idUserEnc: ID_USER_ENC,
                    idForum: idForum
                },
                dataType: 'json',
                success: function(r) {
                    if (r && r.ok) {
                        showToast('Permissão registrada.');
                        setTimeout(() => location.reload(), 600);
                    } else {
                        showToast((r && r.msg) ? r.msg : 'Não foi possível registrar.', 'err');
                    }
                },
                error: function() {
                    showToast('Erro de comunicação.', 'err');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>