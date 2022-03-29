<h1 class="text-3xl font-medium mb-3"><?= lang('text_license_sub_heading'); ?></h1>
<p class="text-gray-500 mb-5">
    By proceeding with this installation you agree to the terms of
    TastyIgniter End User Licence Agreement (EULA)
</p>

<textarea
    class="form-textarea w-full mb-3 rounded-md bg-white border-gray-200 text-gray-500"
    rows="12"
    readonly
><?= file_get_contents(BASEPATH.'/LICENSE.txt'); ?></textarea>

<div class="">
    <button
        type="button"
        class="w-full bg-orange-600 transition duration-150 ease-in-out rounded-md text-white font-medium px-6 py-2 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-600"
        data-install-control="accept-license"
    ><?= lang('button_accept') ?></button>
</div>
