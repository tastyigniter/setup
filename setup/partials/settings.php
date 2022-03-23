<?php
$settings = $setup->getSettingsDetails();
?>
<h1 class="text-3xl font-medium mb-3"><?= lang('text_settings_heading'); ?></h1>
<p class="text-gray-500 mb-5"><?= lang('text_settings_sub_heading'); ?></p>

<div class="grid gap-4 grid-cols-2 mb-5">
    <div>
        <label for="input-staff-name" class="text-sm block font-medium mb-1"><?= lang('label_staff_name'); ?></label>
        <input
            type="text"
            name="staff_name"
            id="input-staff-name"
            class="form-input w-full border-gray-300 rounded-md focus:ring-orange-600 focus:border-orange-600"
            value="<?= $settings->staff_name; ?>"
        />
    </div>
    <div>
        <label for="input-username" class="text-sm block font-medium mb-1"><?= lang('label_admin_username'); ?></label>
        <input
            type="text"
            name="username"
            id="input-username"
            class="form-input w-full border-gray-300 rounded-md focus:ring-orange-600 focus:border-orange-600"
            value="<?= $settings->username; ?>"
        />
    </div>
    <div>
        <label for="input-password" class="text-sm block font-medium mb-1"><?= lang('label_admin_password'); ?></label>
        <input
            type="password"
            name="password"
            id="input-password"
            class="form-input w-full border-gray-300 rounded-md focus:ring-orange-600 focus:border-orange-600"
        />
    </div>
    <div>
        <label for="input-confirm-password" class="text-sm block font-medium mb-1"><?= lang('label_confirm_password'); ?></label>
        <input
            type="password"
            name="confirm_password"
            id="input-confirm-password"
            class="form-input w-full border-gray-300 rounded-md focus:ring-orange-600 focus:border-orange-600"
        />
    </div>
    <div>
        <label for="input-site-name" class="text-sm block font-medium mb-1"><?= lang('label_site_name'); ?></label>
        <input
            type="text"
            name="site_name"
            id="input-site-name"
            class="form-input w-full border-gray-300 rounded-md focus:ring-orange-600 focus:border-orange-600"
            value="<?= $settings->site_name; ?>"
        />
    </div>
    <div>
        <label for="input-site-email" class="text-sm block font-medium mb-1"><?= lang('label_site_email'); ?></label>
        <input
            type="text"
            name="site_email"
            id="input-site-email"
            class="form-input w-full border-gray-300 rounded-md focus:ring-orange-600 focus:border-orange-600"
            value="<?= $settings->site_email; ?>"
        />
    </div>
</div>
<div class="flex items-center p-3 rounded-md bg-white border border-gray-200 shadow-inner mb-3">
    <input type="hidden" name="demo_data" value="0">
    <input
        class="form-checkbox rounded-md text-orange-600 border-gray-300 focus:ring-orange-600 focus:border-orange-600 w-6 h-6 mr-3"
        type="checkbox"
        id="input-demo-data"
        name="demo_data"
        value="1"
        <?= $settings->demo_data == 1 ? 'checked' : ''; ?>
    />
    <label
        class="form-check-label"
        for="input-demo-data"
    >
        <span class="font-medium"><?= lang('label_demo_data'); ?></span>
        <span class="block text-sm text-gray-500"><?= lang('help_demo_data'); ?></span>
    </label>
</div>
<div class="flex items-center p-3 rounded-md bg-white border border-gray-200 shadow-inner">
    <input type="hidden" name="site_location_mode" value="single">
    <input
        class="form-checkbox rounded-md text-orange-600 border-gray-300 focus:ring-orange-600 focus:border-orange-600 w-6 h-6 mr-3"
        type="checkbox"
        id="input-site-location-mode"
        name="site_location_mode"
        value="multiple"
        <?= $settings->site_location_mode == 1 ? 'checked' : ''; ?>
    />
    <label
        class="form-check-label"
        for="input-site-location-mode"
    >
        <span class="font-medium"><?= lang('label_site_location_mode'); ?></span>
        <span class="block text-sm text-gray-500"><?= lang('help_site_location_mode'); ?></span>
    </label>
</div>
<div class="p-3 bg-gray-200 rounded-md my-5">
    <label for="input-carte-key" class="text-sm block font-medium mb-1"><?= lang('label_site_key'); ?></label>
    <input
        id="input-carte-key"
        type="text"
        class="form-input w-full border-gray-300 rounded-md focus:ring-orange-600 focus:border-orange-600 mb-1"
        name="site_key"
    />
    <div class="text-sm text-gray-500"><?= sprintf(lang('help_site_key'), 'https://tastyigniter.com/signin', 'https://tastyigniter.com/support/articles/carte-key'); ?></div>
</div>

<input type="hidden" name="disableLog" value="1">

<div class="pt-4">
    <button
        type="submit"
        class="bg-red bg-orange-600 w-full transition duration-150 ease-in-out rounded-md text-white font-medium px-6 py-2 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-600"
    ><?= lang('text_next_step'); ?> <?= lang('button_install'); ?></button>
    <a
        class="hover:underline block mt-3 text-center"
        href="/setup.php"
    ><?= lang('button_back'); ?></a>
</div>
