<aside class="setup-sidebar animate-fade-in">
    <div class="flex items-center gap-3">
        <div class="h-10 w-10 rounded-xl bg-brand/10 flex items-center justify-center">
            <img src="setup/assets/images/logo.svg" alt="" class="h-6 w-6" width="24" height="24">
        </div>
        <div>
            <p class="font-semibold text-slate-900"><?= lang('text_title'); ?></p>
            <p class="text-sm text-slate-500"><?= lang('text_installation'); ?></p>
        </div>
    </div>

    <div class="progress-track">
        <div class="progress-fill" data-progress-fill style="width: 0%"></div>
    </div>

    <nav class="space-y-5">
        <?php
        $steps = [
            'start' => lang('step_start'),
            'database' => lang('step_database'),
            'install' => lang('step_install'),
            'complete' => lang('step_complete'),
        ];
        foreach ($steps as $key => $label) { ?>
            <div class="step-item" data-sidebar-step="<?= $key ?>">
                <span class="step-dot"></span>
                <div>
                    <p class="text-sm font-medium text-slate-900"><?= $label ?></p>
                </div>
            </div>
        <?php } ?>
    </nav>

    <div class="mt-auto pt-6 border-t border-slate-200">
        <a href="<?= DOCS_INSTALLATION_URL ?>" target="_blank" rel="noopener" class="text-sm text-brand hover:underline">
            <?= lang('text_docs_link'); ?> &rarr;
        </a>
    </div>
</aside>
