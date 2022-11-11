<?php

$definitions = [
    'php' => [
        'label' => 'label_php_version',
        'hint' => 'text_php_version',
    ],
    'php_extensions' => [
        'label' => 'label_php_extensions',
        'hint' => 'label_php_extensions',
    ],
    'connection' => [
        'label' => 'label_connection',
        'hint' => 'text_live_connection',
    ],
    'uploads' => [
        'label' => 'label_file_uploads',
        'hint' => 'text_file_uploads_enabled',
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
