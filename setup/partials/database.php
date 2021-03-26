<?php
$db = $setup->getDatabaseDetails();
?>
<h2 class="mb-3"><?= lang('text_database_heading'); ?></h2>
<p class="lead mb-4"><?= lang('text_database_sub_heading'); ?></p>

<input type="hidden" name="disableLog" value="1">
<div class="row mb-3">
    <div class="col">
        <div class="form-floating">
            <input
                type="text"
                name="host"
                id="input-db-host"
                class="form-control"
                value="<?= $db->host; ?>"
            />
            <label for="input-db-host"><?= lang('label_hostname'); ?></label>
        </div>
        <span class="form-text text-muted"><?= lang('help_hostname'); ?></span>
    </div>
    <div class="col">
        <div class="form-floating">
            <input
                type="text"
                name="port"
                id="input-db-port"
                class="form-control"
                value="<?= $db->port; ?>"
            />
            <label for="input-db-port"><?= lang('label_port'); ?></label>
        </div>

    </div>
</div>
<div class="row mb-3">
    <div class="col">
        <div class="form-floating">
            <input
                type="text"
                name="database"
                id="input-db-name"
                class="form-control"
                value="<?= $db->database; ?>"
            />
            <label for="input-db-name"><?= lang('label_database'); ?></label>
        </div>
        <span class="form-text text-muted"><?= lang('help_database'); ?></span>
    </div>
    <div class="col">
        <div class="form-floating">
            <input
                type="text"
                name="prefix"
                id="input-db-prefix"
                class="form-control"
                value="<?= $db->prefix; ?>"
            />
            <label for="input-db-prefix"><?= lang('label_prefix'); ?></label>
        </div>
        <span class="form-text text-muted"><?= lang('help_dbprefix'); ?></span>
    </div>
</div>
<div class="row pb-3">
    <div class="col">
        <div class="form-floating">
            <input
                type="text"
                name="username"
                id="input-db-user"
                class="form-control"
                value="<?= $db->username; ?>"
            />
            <label for="input-db-user"><?= lang('label_username'); ?></label>
        </div>
        <span class="form-text text-muted"><?= lang('help_username'); ?></span>
    </div>
    <div class="col">
        <div class="form-floating">
            <input
                type="password"
                name="password"
                id="input-db-pass"
                class="form-control"
                value="<?= $db->password; ?>"
            />
            <label for="input-db-pass"><?= lang('label_password'); ?></label>
        </div>
        <span class="form-text text-muted"><?= lang('help_password'); ?></span>
    </div>
</div>
<div class="mt-4">
    <button type="submit" class="btn btn-primary w-100"><?= lang('text_next_step'); ?> <?= lang('button_admin'); ?></button>
    <a class="btn btn-link text-muted w-100 mt-3" href="/setup.php"><?= lang('button_back'); ?></a>
</div>
