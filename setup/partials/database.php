<?php
$db = $setup->getDatabaseDetails();
?>
<h1 class="text-3xl font-medium mb-3"><?= lang('text_database_heading'); ?></h1>
<p class="text-gray-500 mb-4"><?= lang('text_database_sub_heading'); ?></p>

<input type="hidden" name="disableLog" value="1">
<div class="grid gap-4 grid-cols-2 mb-3">
    <div class="col">
        <div class="form-floating">
            <label for="input-db-host" class="text-sm block font-medium mb-1"><?= lang('label_hostname'); ?></label>
            <input
                type="text"
                name="host"
                id="input-db-host"
                class="form-input w-full border-gray-300 rounded focus:ring-orange-600 focus:border-orange-600"
                value="<?= $db->host; ?>"
            />
        </div>
        <span class="text-sm text-gray-500"><?= lang('help_hostname'); ?></span>
    </div>
    <div class="col">
        <div class="form-floating">
            <label for="input-db-port" class="text-sm block font-medium mb-1 font-medium"><?= lang('label_port'); ?></label>
            <input
                type="text"
                name="port"
                id="input-db-port"
                class="form-input w-full border-gray-300 rounded focus:ring-orange-600 focus:border-orange-600"
                value="<?= $db->port; ?>"
            />
        </div>
    </div>
    <div class="col">
        <div class="form-floating">
            <label for="input-db-name" class="text-sm block font-medium mb-1"><?= lang('label_database'); ?></label>
            <input
                type="text"
                name="database"
                id="input-db-name"
                class="form-input w-full border-gray-300 rounded focus:ring-orange-600 focus:border-orange-600"
                value="<?= $db->database; ?>"
            />
        </div>
        <span class="text-sm text-gray-500"><?= lang('help_database'); ?></span>
    </div>
    <div class="col">
        <div class="form-floating">
            <label for="input-db-prefix" class="text-sm block font-medium mb-1"><?= lang('label_prefix'); ?></label>
            <input
                type="text"
                name="prefix"
                id="input-db-prefix"
                class="form-input w-full border-gray-300 rounded focus:ring-orange-600 focus:border-orange-600"
                value="<?= $db->prefix; ?>"
            />
        </div>
        <span class="text-sm text-gray-500"><?= lang('help_dbprefix'); ?></span>
    </div>
    <div class="col">
        <div class="form-floating">
            <label for="input-db-user" class="text-sm block font-medium mb-1"><?= lang('label_username'); ?></label>
            <input
                type="text"
                name="username"
                id="input-db-user"
                class="form-input w-full border-gray-300 rounded focus:ring-orange-600 focus:border-orange-600"
                value="<?= $db->username; ?>"
            />
        </div>
        <span class="text-sm text-gray-500"><?= lang('help_username'); ?></span>
    </div>
    <div class="col">
        <div class="form-floating">
            <label for="input-db-pass" class="text-sm block font-medium mb-1"><?= lang('label_password'); ?></label>
            <input
                type="password"
                name="password"
                id="input-db-pass"
                class="form-input w-full border-gray-300 rounded focus:ring-orange-600 focus:border-orange-600"
                value="<?= $db->password; ?>"
            />
        </div>
        <span class="text-sm text-gray-500"><?= lang('help_password'); ?></span>
    </div>
</div>
<div class="mt-4">
    <button
        type="submit"
        class="bg-orange-600 w-full transition duration-150 ease-in-out rounded text-white font-medium px-6 py-2 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-600"
    ><?= lang('text_next_step'); ?> <?= lang('button_admin'); ?></button>
    <a class="hover:underline block mt-3 text-center" href="/setup.php"><?= lang('button_back'); ?></a>
</div>
