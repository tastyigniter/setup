<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header border-0">
            <button type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button>
        </div>
        <div class="modal-body">
            <div class="text-center">
                <i class="fa fa-exclamation fa-3x text-danger"></i>
                <h4 class="modal-title mt-4">Out-of-date TastyIgniter v2 found.</h4>
                <p>An older version (v2) of TastyIgniter was found in the specified database.</p>
                <p>By clicking <b>continue</b>, your existing database will be updated.</p>
            </div>
            <input type="hidden" name="upgrade" value="1">
        </div>

        <div class="modal-footer border-0">
            <button type="button" class="btn btn-link" data-dismiss="modal"><?= lang('button_cancel'); ?></button>
            <button type="submit" class="btn btn-primary"><?= lang('button_continue'); ?></button>
        </div>
    </div>
</div>
