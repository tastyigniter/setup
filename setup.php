<?php include_once 'setup/bootstrap.php';

?><!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= sprintf(lang('text_title'), lang('text_installation')); ?></title>
    <link type="image/ico" rel="shortcut icon" href="setup/assets/images/favicon.ico">
    <link type="text/css" rel="stylesheet" href="setup/assets/css/app.css">
</head>
<body class="">
<div id="page">
    <div class="container">
        <div class="page w-75 mx-auto my-5">
            <div class="page-header mb-4">
                <div class="d-md-flex align-items-center justify-content-center text-black-50">
                    <div class="px-3">
                        <i class="icon-ti-logo fa-3x"></i>
                    </div>
                    <div class="">
                        <h1 class="font-weight-bold"><?= lang('text_installation'); ?></h1>
                    </div>
                </div>
            </div>

            <div class="page-content">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h2 data-html="title"><?= lang('text_requirement_heading'); ?></h2>
                        <p class="lead" data-html="subTitle"><?= lang('text_requirement_sub_heading'); ?></p>

                        <?php include_once PARTIALPATH.'_wizard.php'; ?>
                    </div>
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
                    </form>

                    <div id="progress-box" class="card-footer d-none" style="display: none;">
                        <h4 class="message"></h4>
                    </div>
                </div>
            </div>

            <div class="page-footer">
                <div class="p-3 text-center">
                    <a
                        class="px-3 text-black-50"
                        target="_blank"
                        href="//tastyigniter.com"
                    ><?= lang('text_tastyigniter_home'); ?></a>
                    <a
                        class="px-3 text-black-50"
                        target="_blank"
                        href="//tastyigniter.com/docs"
                    ><?= lang('text_documentation'); ?></a>
                    <a
                        class="px-3 text-black-50"
                        target="_blank"
                        href="//forum.tastyigniter.com"
                    ><?= lang('text_community_forums'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="flash-message"></div>

<?php
$partialList = [
    '_alert_check_failed',
    '_alert_check_complete',
    '_alert_install_progress',
    '_alert_install_failed',
    '_popup_license',
    '_popup_upgrade',
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
    Installer.Steps.requirements.view = "[data-partial=\"requirements\"]"
    Installer.Steps.requirements.title = "<?= lang('text_requirement_heading'); ?>"
    Installer.Steps.requirements.subTitle = "<?= lang('text_requirement_sub_heading'); ?>"

    Installer.Steps.database.view = "[data-partial=\"database\"]"
    Installer.Steps.database.title = "<?= lang('text_database_heading'); ?>"
    Installer.Steps.database.subTitle = "<?= lang('text_database_sub_heading'); ?>"

    Installer.Steps.settings.view = "[data-partial=\"settings\"]"
    Installer.Steps.settings.title = "<?= lang('text_settings_heading'); ?>"
    Installer.Steps.settings.subTitle = "<?= lang('text_settings_sub_heading'); ?>"

    Installer.Steps.install.view = "[data-partial=\"install\"]"
    Installer.Steps.install.title = "<?= lang('text_complete_heading'); ?>"
    Installer.Steps.install.subTitle = "<?= lang('text_complete_sub_heading'); ?>"

    Installer.Steps.proceed.view = "[data-partial=\"proceed\"]"
    Installer.Steps.proceed.title = "&#127881;&nbsp;Yippee!"
    Installer.Steps.proceed.subTitle = "Setup has been successfully completed"

    Installer.init()
</script>
</body>
</html>
