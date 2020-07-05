<div class="card-body text-center">
    <div class="mt-4">
        <i class="far fa-check-circle fa-5x text-primary"></i>
    </div>
    
    <div class="buttons py-5">
        <a href="{{frontUrl}}" class="btn btn-light border"><?= lang('text_goto_storefront'); ?></a>
        &nbsp;&nbsp;&nbsp;
        <a href="{{proceedUrl}}" class="btn btn-primary"><?= lang('text_next_step'); ?> <?= lang('text_login_to_admin'); ?></a>
    </div>

    <div class="alert alert-warning mb-0">
        <h5>SECURITY WARNING!</h5>
        <p>
            Delete the setup files to stop someone else from overwriting your site,
            the <strong>setup.php</strong> script and the <strong>setup</strong> directory.
        </p>
    </div>
</div>