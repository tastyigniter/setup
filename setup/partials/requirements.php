<?php
$definitions = [
    'php' => [
        'label' => 'label_php_version',
        'hint' => 'text_php_version',
    ],
    'pdo' => [
        'label' => 'label_pdo',
        'hint' => 'text_pdo_installed',
    ],
    'curl' => [
        'label' => 'label_curl',
        'hint' => 'text_curl_installed',
    ],
    'connection' => [
        'label' => 'label_connection',
        'hint' => 'text_live_connection',
    ],
    'mbstring' => [
        'label' => 'label_mbstring',
        'hint' => 'text_mbstring_installed',
    ],
    'ssl' => [
        'label' => 'label_ssl',
        'hint' => 'text_ssl_installed',
    ],
    'gd' => [
        'label' => 'label_gd',
        'hint' => 'text_gd_installed',
    ],
    'zip' => [
        'label' => 'label_zip',
        'hint' => 'text_zip_installed',
    ],
    'writable' => [
        'label' => 'label_writable',
        'hint' => 'text_is_file_writable',
    ],
];
?>
<h2 class="mb-3"><?= lang('text_requirement_heading'); ?></h2>
<p class="lead mb-4"><?= lang('text_requirement_sub_heading'); ?></p>

<div id="requirements">
    <div class="list-group list-group-flush list-requirement">
        <?php foreach ($definitions as $code => $requirement) { ?>
            <div
                class="list-group-item rounded border shadow-sm mb-2 animated"
                data-requirement="<?= $code; ?>"
                data-label="<?= lang($requirement['label']); ?>"
                data-hint="<?= lang($requirement['hint']); ?>"
            >
                <div class="d-flex align-items-center">
                    <div data-label class="flex-grow-1 px-2 fw-bold text-muted"><?= lang($requirement['label']); ?></div>
                    <div class="px-2">
                        <i data-spinner class="fas fa-circle text-muted" role="status"></i>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<input type="hidden" name="license_agreed" value="accepted">

<div id="requirement-check-result"></div>
