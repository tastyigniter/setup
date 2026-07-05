<div>
    <h1 class="text-2xl font-bold text-slate-900"><?= lang('text_install_heading'); ?></h1>
    <p class="mt-2 text-slate-600"><?= lang('text_install_sub_heading'); ?></p>

    <div class="mt-6 progress-track">
        <div class="progress-fill progress-shimmer" data-download-progress style="width: 0%"></div>
    </div>

    <div class="mt-8 space-y-3">
        <?php
        $steps = [
            'prepare' => lang('install_prepare'),
            'download' => lang('install_download'),
            'extract' => lang('install_extract'),
            'config' => lang('install_config'),
            'install' => lang('install_migrate'),
            'finalize' => lang('install_finalize'),
        ];
        foreach ($steps as $process => $label) { ?>
            <div class="timeline-row" data-timeline="<?= $process ?>">
                <div data-timeline-icon class="shrink-0">
                    <span class="inline-block h-3 w-3 rounded-full bg-slate-300"></span>
                </div>
                <p class="text-sm text-slate-700" data-timeline-label><?= $label ?></p>
            </div>
        <?php } ?>
    </div>

    <div id="install-error" class="hidden"></div>
</div>
