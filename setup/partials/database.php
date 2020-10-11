<?php
$db = $setup->getDatabaseDetails();
?>
<div class="card-body">
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="input-db-host" class="control-label"><?= lang('label_hostname'); ?></label>
            <input
                type="text"
                name="host"
                id="input-db-host"
                class="form-control"
                value="<?= $db->host; ?>"
            />
            <span class="form-text text-muted"><?= lang('help_hostname'); ?></span>
        </div>
        <div class="form-group col-md-6">
            <label for="input-db-port" class="control-label"><?= lang('label_port'); ?></label>
            <input
                type="text"
                name="port"
                id="input-db-port"
                class="form-control"
                value="<?= $db->port; ?>"
            />
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="input-db-name" class="control-label"><?= lang('label_database'); ?></label>
            <input
                type="text"
                name="database"
                id="input-db-name"
                class="form-control"
                value="<?= $db->database; ?>"
            />
            <span class="form-text text-muted"><?= lang('help_database'); ?></span>
        </div>
        <div class="form-group col-md-6">
            <label for="input-db-prefix" class="control-label"><?= lang('label_prefix'); ?></label>
            <input
                type="text"
                name="prefix"
                id="input-db-prefix"
                class="form-control"
                value="<?= $db->prefix; ?>"
            />
            <span class="form-text text-muted"><?= lang('help_dbprefix'); ?></span>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="input-db-user" class="control-label"><?= lang('label_username'); ?></label>
            <input
                type="text"
                name="username"
                id="input-db-user"
                class="form-control"
                value="<?= $db->username; ?>"
            />
            <span class="form-text text-muted"><?= lang('help_username'); ?></span>
        </div>
        <div class="form-group col-md-6">
            <label for="input-db-pass" class="control-label"><?= lang('label_password'); ?></label>
            <input
                type="password"
                name="password"
                id="input-db-pass"
                class="form-control"
                value="<?= $db->password; ?>"
            />
            <span class="form-text text-muted"><?= lang('help_password'); ?></span>
        </div>
    </div>
    <input type="hidden" name="disableLog" value="1">
</div>
<div class="card-footer p-4 text-right">
    <a class="btn btn-link text-muted" href=""><?= lang('button_back'); ?></a>
    <button type="submit" class="btn btn-primary"><?= lang('text_next_step'); ?> <?= lang('button_admin'); ?></button>
</div>
