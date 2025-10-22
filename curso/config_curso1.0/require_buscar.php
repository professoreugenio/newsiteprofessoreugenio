<div id="buscaInline" class=" mt-4">
    <form class="d-flex justify-content-center" role="search" action="modulos_buscalicao.php" method="get">
        <!-- opcionais -->
        <input type="hidden" name="idcurso" value="<?= htmlspecialchars($idcurso ?? '') ?>">
        <input type="hidden" name="idturma" value="<?= htmlspecialchars($idturma ?? '') ?>">

        <div class="position-relative w-100" style="max-width:520px;">
            <label class="visually-hidden" for="busca_q">Buscar</label>
            <input
                id="busca_q"
                type="search"
                class="form-control form-control-sm rounded-pill ps-3 pe-5 busca-input"
                name="q"
                placeholder="Buscar conteúdos (título, olho, tag, texto)…"
                aria-label="Buscar"
                autocomplete="off"
                required>
            <button
                class="btn btn-sm btn-icon position-absolute top-50 end-0 translate-middle-y me-2"
                type="submit" title="Buscar" aria-label="Buscar">
                <i class="bi bi-search fs-5"></i>
            </button>
        </div>
    </form>
</div>

<style>
    /* Escopo local do componente */
    #buscaInline {
        --brand: #00BB9C;
        --line: rgba(255, 255, 255, .12);
        --bg: #0b1220;
        --text: #e2e8f0;
        --muted: #9aa4b2;
    }

    #buscaInline .busca-input {
        height: 40px;
        /* menor */
        /* background: var(--bg); */
        color: #000000;
        border: 1px solid var(--line);
        box-shadow: 0 0 0 0 rgba(0, 0, 0, 0);
        transition: border-color .2s ease, box-shadow .2s ease;
        font-size: 1.1rem;
    }

    #buscaInline .busca-input::placeholder {
        color: #7c8896;
    }

    #buscaInline .busca-input:focus {
        border-color: var(--brand);
        box-shadow: 0 0 0 .2rem rgba(0, 187, 156, .15);
        outline: none;
    }

    #buscaInline .btn-icon {
        background: transparent;
        /* transparente */
        border: 0;
        color: var(--muted);
        /* só a lupa */
        padding: .25rem .35rem;
        line-height: 1;
    }

    #buscaInline .btn-icon:hover {
        color: var(--text);
    }
</style>