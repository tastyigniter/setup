<div class="animated pulse">
    <h2 class="mb-3"><?= lang('text_license_sub_heading'); ?></h2>
    <p class="lead mb-4">
        By proceeding with this installation you agree to the terms of
        TastyIgniter End User Licence Agreement (EULA)
    </p>

    <textarea
        class="form-control mb-3"
        rows="20"
        readonly
    ><?= file_get_contents(BASEPATH.'/LICENSE.txt'); ?></textarea>

    <button
        type="button"
        class="btn btn-primary w-100 mt-4"
        data-install-control="accept-license"
    ><?= lang('button_accept') ?></button>
</div>
