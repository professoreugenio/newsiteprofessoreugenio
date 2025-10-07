<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Professoreugenio</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- AOS CSS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />

    <link rel="stylesheet" href="v2.0/config_css/config.css">
</head>

<body>

    <!-- Navbar -->
    <?php include 'v2.0/nav.php'; ?>

    <!-- Conteúdo -->
    <main class="container my-5">
        <div data-aos="fade-up">
            <h1>Bem-vindo ao site do Professor Eugênio</h1>
            <p>Explore nossos cursos e entre em contato para mais informações.</p>
        </div>

        <div data-aos="fade-right" class="mt-5">
            <h2>Cursos Disponíveis</h2>
            <p>Design Gráfico, Desenvolvimento Web, Excel e muito mais...</p>
        </div>

        <div data-aos="fade-left" class="mt-5">
            <h2>Entre em Contato</h2>
            <p>Use o link no rodapé para falar conosco via WhatsApp.</p>
        </div>
    </main>

    <!-- Rodapé -->

    <?php require 'v2.0/footer.php'; ?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AOS JS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>

</html>