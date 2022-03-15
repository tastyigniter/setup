<ol class="list-decimal space-y-2 text-gray-400 font-medium pl-4">
    <li :class="{'text-gray-800': currentStep === 'license'}" data-wizard="license">EULA</li>
    <li :class="{'text-gray-800': currentStep === 'requirements'}" data-wizard="requirements">Requirements</li>
    <li :class="{'text-gray-700': currentStep === 'database'}" data-wizard="database">Database</li>
    <li :class="{'text-gray-600': currentStep === 'settings'}" data-wizard="settings">Settings</li>
    <li :class="{'text-gray-600': currentStep === 'install'}" data-wizard="install">Complete Setup</li>
</ol>
