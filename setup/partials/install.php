<div class="card-body">
    <div class="form-group">
        <label for="input-carte-key"><?= lang('label_site_key'); ?></label>
        <input
            id="input-carte-key"
            type="text"
            class="form-control"
            name="site_key"
            value=""
            placeholder="Enter your Site Carté Key... (Optional)"
        />
        <span class="form-text text-muted"><?= sprintf(lang('help_site_key'), '//tastyigniter.com/account/sites'); ?></span>
    </div>

    <div data-html="install-type">
        <div class="row text-center py-4">
            <div class="col-md-6">
                <div class="card card-body shadow">
                    <p>Without any extensions or theme, you can install them later.</p>
                    <button
                        class="btn btn-light border"
                        data-install-control="install-core"
                    ><?= lang('button_clean_install') ?></button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-body shadow">
                    <p>With a pre-built theme and some recommended extensions.</p>
                    <button
                        type="button"
                        class="btn btn-primary"
                        data-install-control="fetch-theme"
                    ><?= lang('button_choose_theme') ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="row" data-html="themes">
        <div class="install-progress p-4 text-center mx-auto" style="display: none;">
            <i class="fa fa-spinner fa-3x fa-spin"></i>
            <p class="message">Fetching themes...</p>
        </div>
    </div>
</div>
