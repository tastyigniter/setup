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
<h2 class="text-3xl font-medium mb-3"><?= lang('text_requirement_heading'); ?></h2>
<p class="text-gray-500 mb-4"><?= lang('text_requirement_sub_heading'); ?></p>

<div id="requirements">
    <div class="flex flex-col">
        <?php foreach ($definitions as $code => $requirement) { ?>
            <div
                class="p-3 bg-white rounded-lg border border-gray-200 shadow-sm mb-2 opacity-25 animated"
                data-requirement="<?= $code; ?>"
                data-label="<?= lang($requirement['label']); ?>"
                data-hint="<?= lang($requirement['hint']); ?>"
            >
                <div class="flex items-center">
                    <div class="mr-3">
                        <svg class="loading animate-spin h-5 w-5 text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="success h-5 w-5 hidden" viewBox="0 0 20 20">
                            <path class="fill-emerald-600" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="failure h-5 w-5 hidden" viewBox="0 0 20 20" fill="none">
                            <path class="fill-red-600" fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div data-label class="grow text-sm"><?= lang($requirement['label']); ?></div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<input type="hidden" name="license_agreed" value="accepted">

<div id="requirement-check-result"></div>
