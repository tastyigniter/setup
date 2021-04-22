<?php
$settings = $setup->getSettingsDetails();
?>
<h2 class="mb-3"><?= lang('text_settings_heading'); ?></h2>
<p class="lead mb-4"><?= lang('text_settings_sub_heading'); ?></p>

<div class="row mb-3">
    <div class="col">
        <div class="form-floating">
            <input
                type="text"
                name="staff_name"
                id="input-staff-name"
                class="form-control"
                value="<?= $settings->staff_name; ?>"
            />
            <label for="input-staff-name"><?= lang('label_staff_name'); ?></label>
        </div>
    </div>
    <div class="col">
        <div class="form-floating">
            <input
                type="text"
                name="username"
                id="input-username"
                class="form-control"
                value="<?= $settings->username; ?>"
            />
            <label for="input-username"><?= lang('label_admin_username'); ?></label>
        </div>
    </div>
</div>
<div class="row mb-3">
    <div class="col">
        <div class="form-floating">
            <input
                type="password"
                name="password"
                id="input-password"
                class="form-control"
            />
            <label for="input-password"><?= lang('label_admin_password'); ?></label>
        </div>
    </div>
    <div class="col">
        <div class="form-floating">
            <input
                type="password"
                name="confirm_password"
                id="input-confirm-password"
                class="form-control"
            />
            <label for="input-confirm-password"><?= lang('label_confirm_password'); ?></label>
        </div>
    </div>
</div>
<div class="row mb-3">
    <div class="col">
        <div class="form-floating">
            <input
                type="text"
                name="site_name"
                id="input-site-name"
                class="form-control"
                value="<?= $settings->site_name; ?>"
            />
            <label for="input-site-name"><?= lang('label_site_name'); ?></label>
        </div>
    </div>
    <div class="col">
        <div class="form-floating">
            <input
                type="text"
                name="site_email"
                id="input-site-email"
                class="form-control"
                value="<?= $settings->site_email; ?>"
            />
            <label for="input-site-email"><?= lang('label_site_email'); ?></label>
        </div>
    </div>
</div>
<div class="form-group mb-3">
    <div class="form-floating">
        <input
            id="input-carte-key"
            type="text"
            class="form-control"
            name="site_key"
        />
        <label for="input-carte-key"><?= lang('label_site_key'); ?></label>
    </div>
    <div class="form-text text-muted"><?= sprintf(lang('help_site_key'), '//tastyigniter.com/account/sites'); ?></div>
</div>
<div class="border-top pt-3 mt-4">
    <div class="row">
        <div class="form-group col">
            <input type="hidden" name="site_location_mode" value="single">
            <div class="form-check form-switch">
                <input
                    class="form-check-input"
                    type="checkbox"
                    id="input-site-location-mode"
                    name="site_location_mode"
                    value="multiple"
                    <?= $settings->site_location_mode == 1 ? 'checked' : ''; ?>
                />
                <label
                    class="form-check-label"
                    for="input-site-location-mode"
                ><?= lang('label_site_location_mode'); ?></label>
            </div>
            <span class="form-text text-muted"><?= lang('help_site_location_mode'); ?></span>
        </div>
        <div class="form-group col">
            <input type="hidden" name="demo_data" value="0">
            <div class="form-check form-switch">
                <input
                    class="form-check-input"
                    type="checkbox"
                    id="input-demo-data"
                    name="demo_data"
                    value="1"
                    <?= $settings->demo_data == 1 ? 'checked' : ''; ?>
                />
                <label
                    class="form-check-label"
                    for="input-demo-data"
                ><?= lang('label_demo_data'); ?></label>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="disableLog" value="1">

<div class="pt-4">
    <button
        type="submit"
        class="btn btn-primary w-100"
    ><?= lang('text_next_step'); ?> <?= lang('button_install'); ?></button>
    <a
        class="btn btn-link mt-3 text-muted w-100"
        href="/setup.php"
    ><?= lang('button_back'); ?></a>
</div>
