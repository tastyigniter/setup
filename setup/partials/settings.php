<?php
$settings = $setup->getSettingsDetails();
?>
<div class="card-body">
    <div class="form-row">
        <div class="form-group col-md-6">
            <input type="hidden" name="site_location_mode" value="0">
            <div class="custom-control custom-switch">
                <input
                    type="checkbox"
                    class="custom-control-input"
                    id="input-site-location-mode"
                    name="site_location_mode"
                    value="1"
                    <?= $settings->site_location_mode == 1 ? 'checked' : ''; ?>
                />
                <label
                    class="custom-control-label"
                    for="input-site-location-mode"
                ><?= lang('label_site_location_mode'); ?></label>
            </div>
            <span class="form-text text-muted"><?= lang('help_site_location_mode'); ?></span>
        </div>
        <div class="form-group col-md-6">
            <input type="hidden" name="demo_data" value="0">
            <div class="custom-control custom-switch">
                <input
                    type="checkbox"
                    class="custom-control-input"
                    id="input-demo-data"
                    name="demo_data"
                    value="1"
                    <?= $settings->demo_data == 1 ? 'checked' : ''; ?>
                />
                <label
                    class="custom-control-label"
                    for="input-demo-data"
                ><?= lang('label_demo_data'); ?></label>
            </div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="input-site-name" class="control-label"><?= lang('label_site_name'); ?></label>
            <input
                type="text"
                name="site_name"
                id="input-site-name"
                class="form-control"
                value="<?= $settings->site_name; ?>"
            />
        </div>
        <div class="form-group col-md-6">
            <label for="input-site-email" class="control-label"><?= lang('label_site_email'); ?></label>
            <input
                type="text"
                name="site_email"
                id="input-site-email"
                class="form-control"
                value="<?= $settings->site_email; ?>"
            />
        </div>
    </div>
</div>

<div class="card-header">
    <h5 class="mb-0 text-muted"><?= lang('text_admin_details'); ?></h5>
</div>

<div class="card-body">
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="input-staff-name" class="control-label"><?= lang('label_staff_name'); ?></label>
            <input
                type="text"
                name="staff_name"
                id="input-staff-name"
                class="form-control"
                value="<?= $settings->staff_name; ?>"
            />
        </div>
        <div class="form-group col-md-6">
            <label for="input-username" class="control-label"><?= lang('label_admin_username'); ?></label>
            <input
                type="text"
                name="username"
                id="input-username"
                class="form-control"
                value="<?= $settings->username; ?>"
            />
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="input-password" class="control-label"><?= lang('label_admin_password'); ?></label>
            <input
                type="password"
                name="password"
                id="input-password"
                class="form-control"
            />
        </div>
        <div class="form-group col-md-6">
            <label
                for="input-confirm-password"
                class="control-label"><?= lang('label_confirm_password'); ?>
            </label>
            <input
                type="password"
                name="confirm_password"
                id="input-confirm-password"
                class="form-control"
                value=""
            />
        </div>
    </div>
    <input type="hidden" name="disableLog" value="1">
</div>

<div class="card-footer p-4 text-right">
    <a class="btn btn-link text-muted" href=""><?= lang('button_back'); ?></a>
    <button type="submit" class="btn btn-primary"><?= lang('text_next_step'); ?> <?= lang('button_install'); ?></button>
</div>
