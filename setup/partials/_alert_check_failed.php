<div class="bg-white p-3 rounded-lg shadow-sm animated pulse">
    <div class="flex items-center">
        <div class="grow pl-2 pr-sm-3">
            <h4 class="text-red-700 text-xl font-medium mb-3">System Requirements Check Failed</h4>
            <p class="mb-3 text-sm">Your system does not meet the minimum requirements for the installation.</p>
            <p class="mb-2">{{{message}}}</p>
            <p class="text-xs mb-3">
                Error message code: {{code}}
            </p>
            <p class="text-sm">
                Please see
                <a class="text-orange-600" href="//docs.tastyigniter.com" target="_blank">the documentation</a> for more information.
            </p>
        </div>
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" viewBox="0 0 20 20">
                <path class="fill-red-600 opacity-50" fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
            </svg>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end mt-4">
    <button
        type="submit"
        class="w-full bg-orange-600 transition duration-150 ease-in-out rounded text-white font-medium px-6 py-2 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-600"
        data-install-control="retry-check"
    >Retry Check</button>
</div>
