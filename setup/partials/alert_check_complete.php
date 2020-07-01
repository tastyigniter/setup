<div class="card-footer p-4">
    <h4><i class="far fa-check-circle text-success"></i>&nbsp;&nbsp;System requirements check complete</h4>
    <p>
        Your system meets the minimum requirements, by proceeding with this installation you accept the
        <a
            href="#"
            data-toggle="modal"
            data-target="#license-modal"
        >TastyIgniter End User Licence agreement(EULA)</a>.
    </p>
    <a
        class="btn btn-primary"
        href="#"
        data-install-control="accept-license"
    >Accept license and proceed</a>
</div>

<div
    id="license-modal"
    class="modal"
    tabindex="-1"
    role="dialog"
>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5><?= lang('text_license_sub_heading'); ?></h5>
            </div>
            <div class="modal-body panel-license">
                <input type="hidden" name="license_agreed" value="1">
                <?= nl2br(file_get_contents(BASEPATH.'/LICENSE.txt')); ?>
            </div>

            <div class="modal-footer">
                <a
                    class="btn btn-success pull-right"
                    data-install-control="accept-license"
                ><?= lang('button_accept') ?></a>
            </div>
        </div>
    </div>
</div>