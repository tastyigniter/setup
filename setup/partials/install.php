<h2 class="mb-3"><?= lang('text_complete_heading'); ?></h2>
<p class="lead mb-4"><?= lang('text_complete_sub_heading'); ?></p>

<div data-html="install-type">
    <div class="row text-center">
        <div class="col-md-6">
            <div class="card card-body shadow">
                <p>Without any extensions or theme, you can install them later.</p>
                <button
                    class="btn btn-outline-dark"
                    data-install-control="install-fresh"
                ><?= lang('button_clean_install') ?></button>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-body shadow">
                <p>With a pre-built theme and some recommended extensions.</p>
                <button
                    type="button"
                    class="btn btn-primary"
                    data-install-control="install-prebuilt"
                    data-theme-code="tastyigniter-orange"
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
