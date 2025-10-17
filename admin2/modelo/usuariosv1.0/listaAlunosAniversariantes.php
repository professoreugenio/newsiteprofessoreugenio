<ul class="list-group">
    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
        $nomeArr = explode(' ', trim($row['nome']));
        $nomeExib = htmlspecialchars($nomeArr[0] . ' ' . ($nomeArr[1] ?? ''));
        $foto = fotoAlunoUrl($row['pastasc'], $row['imagem200']);
        $emailBloq = intval($row['emailbloqueio']);
        $temWhats = !empty($row['celular']);
        $email = htmlspecialchars($row['email']);
        $nmTurma = htmlspecialchars($row['nome_turma']);
        $encIdUser = encrypt($row['codigocadastro'], 'e');
        $msgBday = "{$saudacao}! Aqui é o professor Eugênio do curso de {$nmTurma}\nVenho aqui lhe desejar 🎉 *Parabéns, {$nomeExib}!* 🎂\n";
        $linkWhatsBday = $temWhats ? linkWhats($row['celular'], $msgBday) : '#';
    ?>
        <li class="list-group-item py-3 px-2 mb-2 rounded shadow-sm">
            <div class="row align-items-center gx-2">

                <div class="col-md-9 d-flex align-items-center">
                    <img src="<?= $foto ?>" class="rounded-circle me-3" style="width:56px; height:56px; object-fit:cover; border:2px solid #eee;">
                    <div>
                        <div>
                            <a href="alunoTurmas.php?idUsuario=<?= $encIdUser ?>"> <span class="fw-bold fs-6"><?= $nomeExib ?></span></a>
                            <span class="badge bg-warning text-dark ms-2">
                                <i class="bi bi-cake"></i>
                                <?= date('d/m/Y', strtotime($row['datanascimento_sc'])) ?> - Aniversário!
                            </span>
                        </div>
                        <div class="small mt-1">
                            <span>
                                <i class="bi bi-envelope<?= $emailBloq ? '-slash text-danger' : '-at text-success' ?>" title="<?= $emailBloq ? 'E-mail bloqueado' : 'E-mail liberado' ?>"></i>
                                <?= $email ?>
                            </span>
                            <span class="ms-3">
                                <?php if ($temWhats): ?>
                                    <i class="bi bi-whatsapp text-success"></i> WhatsApp
                                <?php else: ?>
                                    <i class="bi bi-whatsapp text-secondary"></i> Sem WhatsApp
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php if (!empty($row['nome_turma'])): ?>
                            <div class="text-muted small mt-1">
                                <i class="bi bi-mortarboard"></i>
                                Última turma: <?= htmlspecialchars($row['nome_turma']) ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
                <!-- Direita: Dropdown -->
                <div class="col-md-3 text-end">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuBtn<?= $row['codigocadastro'] ?>" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-send"></i> Enviar Parabéns
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuBtn<?= $row['codigocadastro'] ?>">
                            <?php if ($temWhats): ?>
                                <li>
                                    <a class="dropdown-item" target="_blank" href="<?= $linkWhatsBday ?>">
                                        <i class="bi bi-whatsapp text-success"></i> WhatsApp Parabéns
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item" target="_blank" href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= $email ?>&su=Feliz%20Aniversário!&body=<?= urlencode("🎉 Parabéns, {$nomeExib}!\n\n\nAbraços, Prof. Eugênio\nDo curso {$nmTurma}") ?>">
                                    <i class="bi bi-envelope-paper"></i> E-mail Parabéns
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </li>
    <?php endwhile; ?>
</ul>