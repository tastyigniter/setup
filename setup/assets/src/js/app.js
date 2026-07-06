import '../css/app.css';
import Alpine from 'alpinejs';
import { Installer } from './installer.js';

window.Installer = Installer;
window.Alpine = Alpine;

Alpine.data('setupWizard', () => ({
    step: document.getElementById('current-step')?.value || 'start',
    loading: false,
    error: '',
    licenseOpen: false,

    init() {
        Installer.init(this);
    },

    openLicense() {
        this.licenseOpen = true;
    },

    closeLicense() {
        this.licenseOpen = false;
    },

    setStep(step) {
        this.step = step;
        const input = document.getElementById('current-step');
        if (input) input.value = step;
        document.body.dataset.step = step;
        Installer.updateSidebar(step);
    },
}));

Alpine.start();
