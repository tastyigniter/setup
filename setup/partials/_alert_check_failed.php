<div class="bg-white rounded p-4 animated pulse">
    <div class="d-sm-flex align-items-center">
        <div class="flex-grow-1 pl-2 pr-sm-3">
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
        <div>
            <i class="fa fa-exclamation fa-3x text-danger"></i>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end mt-4">
    <button
        type="button"
        class="btn btn-primary w-100"
        data-install-control="retry-check"
    >Retry Check</button>
</div>
