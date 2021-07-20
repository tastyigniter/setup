<?php include_once 'setup/bootstrap.php';

?><!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= sprintf(lang('text_title'), lang('text_installation')); ?></title>
    <link type="image/png" rel="shortcut icon" href="https://d980sf4vy2c60.cloudfront.net/web/icons/favicon.png">
    <link type="text/css" rel="stylesheet" href="setup/assets/css/app.css">
</head>
<body class="">
<main id="page" class="setup-page">
    <?php include_once PARTIALPATH.'_floating_logo.php'; ?>
    <form id="setup-form" accept-charset="utf-8" method="POST" role="form">
        <div data-html="content"></div>

        <input type="hidden" id="current-step" value="<?= $page->currentStep ?>">

        <div
            id="page-modal"
            class="modal"
            tabindex="-1"
            role="dialog"
            data-html="modal">
        </div>

        <div id="progress-box" class="card-footer d-none" style="display: none;">
            <h4 class="message"></h4>
        </div>

        <div id="flash-message"></div>
    </form>
</main>

<?php
$partialList = [
    '_alert_check_failed',
    '_alert_check_complete',
    '_alert_install_progress',
    '_alert_install_failed',
    '_popup_upgrade',
    'start',
    'license',
    'requirements',
    'database',
    'settings',
    'install',
    'theme',
    'proceed',
];
?>

<?php foreach ($partialList as $partial) { ?>
    <script type="text/template" data-partial="<?= $partial ?>">
        <?php include PARTIALPATH.$partial.'.php'; ?>
    </script>
<?php } ?>

<script src="https://tastyigniter.com/assets/ui/js/global.js"></script>
<script src="setup/assets/js/app.js"></script>
<script type="text/javascript">
    Installer.Steps.start.view = "[data-partial=\"start\"]"
    Installer.Steps.license.view = "[data-partial=\"license\"]"
    Installer.Steps.requirements.view = "[data-partial=\"requirements\"]"
    Installer.Steps.database.view = "[data-partial=\"database\"]"
    Installer.Steps.settings.view = "[data-partial=\"settings\"]"
    Installer.Steps.install.view = "[data-partial=\"install\"]"
    Installer.Steps.proceed.view = "[data-partial=\"proceed\"]"

    Installer.init()
</script>
</body>
</html>
