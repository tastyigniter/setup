<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5><?= lang('text_license_sub_heading'); ?></h5>
        </div>
        <div class="modal-body panel-license">
            <p class="font-weight-bold">
                By proceeding with this installation you agree to the terms of
                TastyIgniter End User Licence Agreement (EULA)
            </p>

            <input type="hidden" name="license_agreed" value="1">
            <textarea
                class="form-control"
                rows="20"
                readonly
            ><?= file_get_contents(BASEPATH.'/LICENSE.txt'); ?></textarea>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-link" data-dismiss="modal"><?= lang('button_cancel'); ?></button>
            <button
                type="button"
                class="btn btn-primary"
                data-install-control="accept-license"
            ><?= lang('button_accept') ?></button>
        </div>
    </div>
</div>
