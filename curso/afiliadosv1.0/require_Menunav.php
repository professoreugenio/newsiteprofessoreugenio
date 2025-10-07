<?php

if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}


if (empty($activeTab)) {
    $map = [
        'afiliados.php'            => 'home',
        'afiliados_extrato.php'    => 'extrato',
        'afiliados_hisatorico.php' => 'historico', // conforme você informou
        'afiliados_perfil.php'     => 'perfil',
    ];
    $activeTab = $map[basename($_SERVER['SCRIPT_NAME'] ?? '')] ?? 'home';
}

$tabs = [
    'home'      => ['label' => 'home',      'href' => 'afiliados.php',            'icon' => 'bi-house-fill'],
    'extrato'   => ['label' => 'extrato',   'href' => 'afiliados_extrato.php',    'icon' => 'bi-cash-coin'],
    'historico' => ['label' => 'histórico', 'href' => 'afiliados_hisatorico.php', 'icon' => 'bi-clock-history'],
    'perfil'    => ['label' => 'perfil',    'href' => 'afiliados_perfil.php',     'icon' => 'bi-person-circle'],
];

?>

<div class="afi-tabs mb-3">
    <ul class="nav nav-pills gap-2">
        <?php foreach ($tabs as $k => $tab): ?>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === $k ? 'active' : '' ?>" href="<?= e($tab['href']) ?>">
                    <i class="bi <?= e($tab['icon']) ?> me-1"></i><?= e($tab['label']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>