<?php include_once 'setup/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= lang('text_title'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="setup/assets/css/app.css">
    <link rel="shortcut icon" href="https://tastyigniter.com/images/icons/favicon.png">
</head>
<body x-data="setupWizard" data-step="<?= $page->currentStep ?? 'start' ?>">
<?php if (!empty($fatalError)) { ?>
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="setup-card border-red-200">
            <h1 class="text-xl font-semibold text-red-700">Setup error</h1>
            <p class="mt-3 text-slate-600"><?= htmlspecialchars($fatalError) ?></p>
        </div>
    </div>
<?php } else { ?>
<div class="setup-shell">
    <?php include PARTIALPATH.'_sidebar.php'; ?>

    <main class="setup-main">
        <div class="setup-main-inner">
            <form id="setup-form" method="POST" class="setup-form" novalidate>
                <input type="hidden" id="current-step" value="<?= htmlspecialchars($page->currentStep ?? 'start') ?>">
                <div data-html="content" class="setup-card"></div>
            </form>
        </div>
    </main>
</div>

<?php
$partials = ['start', 'database', 'install', 'complete'];
foreach ($partials as $partial) {
    echo '<script type="text/template" data-partial="'.$partial.'">';
    include PARTIALPATH.$partial.'.php';
    echo '</script>';
}
?>

<script>
    window.setupLang = {
        alert_requirement_error: <?= json_encode(lang('alert_requirement_error')) ?>,
        alert_install_failed: <?= json_encode(lang('alert_install_failed')) ?>,
        button_retry: <?= json_encode(lang('button_retry')) ?>,
        text_docs_link: <?= json_encode(lang('text_docs_link')) ?>,
        docs_troubleshooting: <?= json_encode(DOCS_TROUBLESHOOTING_URL) ?>,
    };
</script>
<script type="module" src="setup/assets/js/app.js"></script>

<?php
    $licensePath = BASEPATH.'/LICENSE.txt';
    $licenseText = is_readable($licensePath) ? file_get_contents($licensePath) : 'License file not found.';
?>

<div
    x-show="licenseOpen"
    x-cloak
    @keydown.escape.window="closeLicense()"
    class="setup-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="license-modal-title"
>
    <div class="setup-modal-backdrop" @click="closeLicense()"></div>
    <div
        class="setup-modal-panel"
        x-show="licenseOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.stop
    >
        <div class="setup-modal-header">
            <h2 id="license-modal-title" class="setup-modal-title"><?= lang('text_license_heading'); ?></h2>
            <button type="button" class="setup-modal-close" @click="closeLicense()" aria-label="Close">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="setup-modal-body">
            <pre class="setup-modal-license"><?= htmlspecialchars($licenseText) ?></pre>
        </div>
        <div class="setup-modal-footer">
            <button type="button" class="setup-btn setup-btn-secondary" @click="closeLicense()">Close</button>
        </div>
    </div>
</div>
<?php } ?>
<div id="toast-container" class="setup-toast-container" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
