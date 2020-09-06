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
<div id="requirements">
    <div class="list-group list-group-flush list-requirement">
        <?php foreach ($definitions as $code => $requirement) { ?>
            <div
                class="list-group-item py-3 animated pulse d-none"
                data-requirement="<?= $code; ?>"
                data-label="<?= lang($requirement['label']); ?>"
                data-hint="<?= lang($requirement['hint']); ?>"
            >
                <div class="d-flex align-items-center">
                    <div class="px-2">
                        <i class="spinner-border spinner-border-sm" role="status"></i>
                    </div>
                    <div class="px-2 font-weight-bold"><?= lang($requirement['label']); ?></div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<div id="requirement-check-result"></div>
