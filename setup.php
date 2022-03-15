<?php include_once 'setup/bootstrap.php';

?><!DOCTYPE html>
<html
    lang="en"
    x-data="window.Installer"
    class="h-full"
>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= sprintf(lang('text_title'), lang('text_installation')); ?></title>
    <link type="image/png" rel="shortcut icon" href="setup/assets/images/favicon.svg">
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Barlow:200,200i,400,400i,500,500i,600,600i,700,700i|Droid+Sans+Mono">
    <link type="text/css" rel="stylesheet" href="setup/assets/css/app.css">
</head>
<body class="flex h-full bg-gray-100 leading-6 tracking-wide">
<div class="container mt-24">
    <main id="page" class="px-3 md:w-3/4 mx-auto">
        <div class="flex flex-row">
            <div :class="{'hidden': hideSidebar}" class="hidden pr-4 basis-1/4">
                <?php include_once PARTIALPATH.'_floating_logo.php'; ?>
                <?php include_once PARTIALPATH.'_wizard.php'; ?>
            </div>
            <div :class="{'basis-full': hideSidebar, 'basis-3/4': !hideSidebar}">
                <form @submit.prevent="submitForm" id="setup-form" accept-charset="utf-8" method="POST" role="form">
                    <div
                        class="text-gray-600 pb-7"
                        data-html="content"
                    ></div>

                    <input type="hidden" id="current-step" value="<?= $page->currentStep ?>">
                    <input type="hidden" name="requirement">

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

                    <div id="flash-message" class="fixed top-9 right-6"></div>
                </form>
            </div>
        </div>
    </main>
</div>

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

<?php foreach ([
    'success' => ['background' => 'bg-emerald-600', 'text' => 'text-emerald-600'],
    'warning' => ['background' => 'bg-amber-600', 'text' => 'text-amber-600'],
    'danger' => ['background' => 'bg-red-600', 'text' => 'text-red-600'],
] as $type => $options) { ?>
    <script type="text/template" data-partial="alert-<?php echo $type ?>">
        <div class="inline-flex items-center <?php echo $options['text'] ?> bg-white leading-none rounded-full p-3 shadow-xl mb-3">
            <span class="inline-flex <?php echo $options['background'] ?> text-white rounded-full h-6 px-3 justify-center items-center"></span>
            <span class="inline-flex px-2">{{message}}</span>
        </div>
    </script>
<?php } ?>
<?php foreach ($partialList as $partial) { ?>
    <script type="text/template" data-partial="<?= $partial ?>">
        <?php include PARTIALPATH.$partial.'.php'; ?>
    </script>
<?php } ?>

<script src="setup/assets/js/app.js"></script>
</body>
</html>
