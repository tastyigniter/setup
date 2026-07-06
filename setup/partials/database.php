<div data-loading-target>
    <h1 class="text-2xl font-bold text-slate-900"><?= lang('text_database_heading'); ?></h1>
    <p class="mt-2 text-slate-600"><?= lang('text_database_sub_heading'); ?></p>

    <div class="mt-8 grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="setup-label" for="host"><?= lang('label_hostname'); ?></label>
            <input class="setup-input" type="text" id="host" name="host" value="<?= htmlspecialchars($setup->getDatabaseDetails()->host ?? '127.0.0.1') ?>" required>
            <p class="mt-1 text-xs text-slate-500"><?= lang('help_hostname'); ?></p>
        </div>

        <div>
            <label class="setup-label" for="port"><?= lang('label_port'); ?></label>
            <input class="setup-input" type="number" id="port" name="port" value="<?= htmlspecialchars((string)($setup->getDatabaseDetails()->port ?? 3306)) ?>" required>
        </div>

        <div>
            <label class="setup-label" for="database"><?= lang('label_database'); ?></label>
            <input class="setup-input" type="text" id="database" name="database" value="<?= htmlspecialchars($setup->getDatabaseDetails()->database ?? '') ?>" required>
        </div>

        <div>
            <label class="setup-label" for="username"><?= lang('label_username'); ?></label>
            <input class="setup-input" type="text" id="username" name="username" value="<?= htmlspecialchars($setup->getDatabaseDetails()->username ?? '') ?>" required autocomplete="off">
            <p class="mt-1 text-xs text-slate-500"><?= lang('help_username'); ?></p>
        </div>

        <div>
            <label class="setup-label" for="password"><?= lang('label_password'); ?></label>
            <input class="setup-input" type="password" id="password" name="password" value="" autocomplete="new-password">
            <p class="mt-1 text-xs text-slate-500"><?= lang('help_password'); ?></p>
        </div>

        <div class="sm:col-span-2">
            <label class="setup-label" for="prefix"><?= lang('label_prefix'); ?></label>
            <input class="setup-input" type="text" id="prefix" name="prefix" value="<?= htmlspecialchars($setup->getDatabaseDetails()->prefix ?? 'ti_') ?>" required>
            <p class="mt-1 text-xs text-slate-500"><?= lang('help_dbprefix'); ?></p>
        </div>
    </div>

    <div class="mt-8 flex flex-wrap gap-3">
        <button type="submit" class="setup-btn setup-btn-primary">
            <?= lang('button_install'); ?>
        </button>
    </div>
</div>
