<div class="bg-white p-4 rounded animated pulse">
    <div class="d-sm-flex align-items-center">
        <div class="flex-grow-1 pl-2 pr-sm-3">
            <h4>System requirements check complete</h4>
            <p class="mb-0">
                Your system meets the minimum requirements.
            </p>
        </div>
        <div>
            <i class="fa fa-check fa-3x text-success"></i>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end mt-4">
    <button
        type="submit"
        class="btn btn-primary w-100"
        data-install-control="complete-requirements"
    ><?= lang('text_next_step'); ?> <?= lang('button_database') ?></button>
</div>
