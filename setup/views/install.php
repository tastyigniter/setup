<div class="panel panel-carte">
    <div class="panel-body">
        <label for="">
            <?= lang('label_site_key'); ?>
        </label>
        <p class="help-block"><?= sprintf(lang('help_site_key'), '//tastyigniter.com/account/sites'); ?></p>
        <input type="text"
               class="form-control"
               name="site_key"
               value=""
               placeholder="Enter your Site (Carté) Key... (Optional)">
    </div>
</div>

<div class="install-progress hide">
    <i class="fa fa-spinner fa-3x fa-spin"></i>
    <p class="message">Fetching themes...</p>
</div>

<div class="row text-center">
    <div data-html="install-type">
        <div class="panel panel-install">
            <button
                type="button"
                class="btn btn-default btn-sm"
                data-install-control="fetch-theme"
            ><?= lang('button_choose_theme') ?></button>
            <p>Choose a theme that fits and you can customize later.</p>
        </div>
        <div class="panel panel-install">
            <button
                class="btn btn-default btn-sm"
                data-install-control="install-core"
            ><?= lang('button_clean_install') ?></button>
            <p>Install TastyIgniter without any extensions or themes.</p>
        </div>
    </div>
</div>