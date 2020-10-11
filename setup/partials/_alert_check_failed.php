<div class="card-footer p-4">
    <div class="d-sm-flex align-items-center">
        <div class="pl-2 pr-sm-3">
            <i class="fa fa-exclamation fa-3x text-danger"></i>
        </div>
        <div class="p-2">
            <h4 class="text-danger">System Requirements Check Failed</h4>
            <p>Your system does not meet the minimum requirements for the installation.</p>
            <p class="mb-0">{{{message}}}</p>
            <p class="small">
                Error message code: {{code}}
            </p>
            <p>
                Please see <a href="//docs.tastyigniter.com" target="_blank">the documentation</a> for more information.
            </p>
        </div>
    </div>
</div>

<div class="card-body p-4 text-right">
    <a
        href=""
        class="btn btn-primary"
        data-install-control="retry-check"
    >Retry Check</a>
</div>
