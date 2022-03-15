<h1 class="text-3xl font-medium mb-3"><?= lang('text_complete_heading'); ?></h1>
<p class="text-gray-500 mb-4"><?= lang('text_complete_sub_heading'); ?></p>

<div data-html="install-type">
    <div class="grid md:grid-cols-2 gap-4 text-center">
        <div class="p-5 bg-white border shadow rounded-md">
            <p class="mb-7">With a pre-built theme and some recommended extensions.</p>
            <button
                type="button"
                class="bg-orange-600 transition duration-150 ease-in-out rounded-md text-white font-medium px-6 py-2 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-600"
                data-install-control="install-prebuilt"
                data-theme-code="tastyigniter-orange"
            ><?= lang('button_choose_theme') ?></button>
        </div>
        <div class="p-5 bg-white border shadow rounded-md">
            <p class="mb-7">Without any extensions or theme, you can install them later.</p>
            <button
                class="border border-gray-800 transition duration-150 ease-in-out rounded-md hover:text-white focus:text-white font-medium px-6 py-2 hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-700"
                data-install-control="install-fresh"
            ><?= lang('button_clean_install') ?></button>
        </div>
    </div>
</div>

<div class="row" data-html="themes">
    <div class="install-progress p-4 text-center mx-auto" style="display: none;">
        <i class="fa fa-spinner fa-3x fa-spin"></i>
        <p class="message">Fetching themes...</p>
    </div>
</div>
