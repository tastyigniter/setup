<div class="text-center sm:text-left">
    <div class="mx-auto sm:mx-0 h-16 w-16 rounded-2xl bg-brand/10 flex items-center justify-center animate-bounce-in">
        <img src="setup/assets/images/logo.svg" alt="" class="h-9 w-9" width="36" height="36">
    </div>

    <h1 class="mt-6 text-2xl font-bold text-slate-900 animate-slide-up"><?= lang('text_complete_heading'); ?></h1>
    <p class="mt-2 text-slate-600 animate-slide-up"><?= lang('text_complete_sub_heading'); ?></p>

    <div class="mt-8 flex flex-wrap gap-3 justify-center sm:justify-start">
        <a href="/admin" data-admin-url class="setup-btn setup-btn-primary"><?= lang('text_login_to_admin'); ?></a>
        <a href="/" data-front-url class="setup-btn setup-btn-secondary"><?= lang('text_goto_storefront'); ?></a>
    </div>

    <ul class="mt-8 space-y-3 text-left text-sm text-slate-700">
        <li class="flex gap-3 animate-slide-up" style="animation-delay: 80ms">
            <span class="text-emerald-500">&#10003;</span>
            <span><?= lang('text_checklist_delete'); ?></span>
        </li>
        <li class="flex gap-3 animate-slide-up" style="animation-delay: 140ms">
            <span class="text-emerald-500">&#10003;</span>
            <span><?= lang('text_checklist_permissions'); ?></span>
        </li>
        <li class="flex gap-3 animate-slide-up" style="animation-delay: 200ms">
            <span class="text-emerald-500">&#10003;</span>
            <span><?= lang('text_checklist_cron'); ?></span>
        </li>
        <li class="flex gap-3 animate-slide-up" style="animation-delay: 260ms">
            <span class="text-emerald-500">&#10003;</span>
            <span><?= lang('text_checklist_admin'); ?></span>
        </li>
        <li class="flex gap-3 animate-slide-up" style="animation-delay: 320ms">
            <span class="text-emerald-500">&#10003;</span>
            <span><?= lang('text_checklist_docs'); ?> <a href="<?= DOCS_POST_INSTALL_URL ?>" class="text-brand hover:underline" target="_blank" rel="noopener"><?= lang('text_docs_link'); ?></a></span>
        </li>
    </ul>

    <div class="mt-8 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900 animate-slide-up">
        <p class="font-semibold">Security reminder</p>
        <p class="mt-1"><?= lang('text_security_warning'); ?></p>
    </div>
</div>
