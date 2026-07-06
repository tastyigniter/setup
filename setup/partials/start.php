<div>
    <h1 class="text-2xl font-bold text-slate-900"><?= lang('text_start_heading'); ?></h1>
    <p class="mt-2 text-slate-600"><?= lang('text_start_sub_heading'); ?></p>

    <div class="mt-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-900"><?= lang('text_requirement_heading'); ?></h2>
                <p class="mt-1 text-sm text-slate-500"><?= lang('text_requirement_sub_heading'); ?></p>
            </div>
        </div>

        <div class="requirement-list mt-4">
            <div class="requirement-list-header">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">System scan</span>
                    <span class="requirement-progress-label" data-requirement-progress>Checking&hellip;</span>
                </div>
                <div class="requirement-progress-track">
                    <div class="requirement-progress-fill" data-requirement-progress-fill style="width: 0%"></div>
                </div>
            </div>

            <div class="requirement-list-body">
                <?php
                $requirementIcons = [
                    'php' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>',
                    'extensions' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>',
                    'pdo' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 7h6M9 11h6M9 15h4"/>',
                    'curl' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.172 13.828a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.102 1.101"/>',
                    'zip' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>',
                    'network' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01M3 12a9 9 0 1118 0"/>',
                    'writable' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>',
                    'memory_limit' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
                    'max_execution_time' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                ];
                foreach (RequirementChecker::definitions() as $code => $requirement) {
                    $iconPath = $requirementIcons[$code] ?? $requirementIcons['extensions'];
                    $isWarning = !empty($requirement['warning']);
                ?>
                    <div class="requirement-row<?= $isWarning ? ' requirement-row-warning' : '' ?>" data-requirement="<?= $code; ?>">
                        <div class="requirement-icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><?= $iconPath ?></svg>
                        </div>
                        <div class="requirement-content">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="requirement-label"><?= lang($requirement['label']); ?></p>
                                <?php if ($isWarning) { ?>
                                    <span class="requirement-badge">Recommended</span>
                                <?php } ?>
                            </div>
                            <p class="requirement-hint hint-links"><?= lang($requirement['hint']); ?></p>
                        </div>
                        <div class="requirement-status" data-status-icon aria-hidden="true">
                            <span class="requirement-status-dot"></span>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div id="requirement-check-result" class="hidden"></div>
    </div>

    <div class="mt-6 setup-panel">
        <h2 class="text-sm font-semibold text-slate-900"><?= lang('text_license_heading'); ?></h2>
        <p class="mt-2 text-sm text-slate-600"><?= lang('text_license_sub_heading'); ?></p>
        <label class="mt-4 flex items-start gap-3 cursor-pointer">
            <input type="checkbox" name="license_agreed" value="accepted" class="mt-1 h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand focus:ring-offset-0" required>
            <span class="text-sm text-slate-700 leading-relaxed"><?= lang('text_license_agreed'); ?></span>
        </label>
        <button type="button" @click="openLicense()" class="mt-3 text-sm text-brand hover:underline cursor-pointer">
            <?= lang('text_view_license'); ?>
        </button>
    </div>

    <div class="mt-8 flex flex-wrap gap-3">
        <button type="submit" id="continue-btn" disabled class="setup-btn setup-btn-primary opacity-50 cursor-not-allowed">
            <?= lang('button_continue'); ?>
        </button>
    </div>
</div>
