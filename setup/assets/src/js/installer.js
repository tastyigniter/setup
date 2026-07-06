import { clearToast, showToast } from './toast.js';

const Installer = {
    wizard: null,
    currentStep: 'start',

    Steps: {
        start: { handler: 'onAcceptLicense' },
        database: { handler: 'onValidateDatabase' },
        install: {
            handler: 'onInstall',
            steps: [
                { process: 'prepare', labelKey: 'install_prepare' },
                { process: 'download', labelKey: 'install_download' },
                { process: 'extract', labelKey: 'install_extract' },
                { process: 'config', labelKey: 'install_config' },
                { process: 'install', labelKey: 'install_migrate' },
                { process: 'finalize', labelKey: 'install_finalize' },
            ],
        },
        complete: {},
    },

    init(wizard) {
        this.wizard = wizard;
        this.currentStep = document.getElementById('current-step')?.value || 'start';
        this.$content = document.querySelector('[data-html="content"]');
        this.$form = document.getElementById('setup-form');

        this.renderView(this.currentStep);
        this.updateSidebar(this.currentStep);
        document.body.dataset.step = this.currentStep;

        this.$form?.addEventListener('submit', (event) => this.submitForm(event));
        document.addEventListener('click', (event) => this.onControlClick(event));

        if (this.currentStep === 'start') {
            this.checkRequirements();
        }
    },

    async submitForm(event) {
        event.preventDefault();
        if (this.wizard.loading) return;

        const handler = this.Steps[this.currentStep]?.handler;
        if (!handler) return;

        if (this.currentStep === 'database') {
            await this.runHandler(handler);
            return;
        }

        if (this.currentStep === 'start') {
            await this.runHandler(handler);
        }
    },

    onControlClick(event) {
        const button = event.target.closest('[data-install-control]');
        if (!button) return;

        event.preventDefault();
        const control = button.dataset.installControl;

        switch (control) {
            case 'retry-check':
                this.checkRequirements();
                break;
            case 'retry-install':
                this.startInstall();
                break;
            case 'start-install':
                this.startInstall();
                break;
        }
    },

    async runHandler(handler, extra = {}) {
        this.setLoading(true);
        this.clearError();

        try {
            const json = await this.sendRequest(handler, extra);
            await this.processResponse(json);
        } catch (error) {
            this.showError(error.message);
        } finally {
            this.setLoading(false);
        }
    },

    async sendRequest(handler, data = {}) {
        const formData = new FormData(this.$form);
        formData.set('handler', handler);

        Object.entries(data).forEach(([key, value]) => {
            formData.set(key, value);
        });

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData,
        });

        const text = await response.text();

        if (!response.ok) {
            throw new Error(text || 'Request failed');
        }

        return JSON.parse(text);
    },

    async processResponse(json) {
        if (json.step === 'install') {
            this.goToStep('install');
            await this.startInstall();
            return;
        }

        if (json.step) {
            this.goToStep(json.step);
        }
    },

    goToStep(step) {
        this.currentStep = step;
        this.wizard.setStep(step);
        this.renderView(step);

        if (step === 'start') {
            this.checkRequirements();
        }
    },

    renderView(step) {
        const template = document.querySelector(`[data-partial="${step}"]`);
        if (!template || !this.$content) return;

        this.$content.innerHTML = template.innerHTML;
        this.$content.classList.remove('animate-slide-up');
        void this.$content.offsetWidth;
        this.$content.classList.add('animate-slide-up');

        if (window.Alpine) {
            window.Alpine.initTree(this.$content);
        }
    },

    updateSidebar(step) {
        const order = ['start', 'database', 'install', 'complete'];
        const currentIndex = order.indexOf(step);

        document.querySelectorAll('[data-sidebar-step]').forEach((item) => {
            const itemStep = item.dataset.sidebarStep;
            const itemIndex = order.indexOf(itemStep);

            item.classList.remove('active', 'complete');
            if (itemIndex < currentIndex) item.classList.add('complete');
            if (itemStep === step) item.classList.add('active');
        });

        const progress = document.querySelector('[data-progress-fill]');
        if (progress) {
            const percent = currentIndex <= 0 ? 0 : (currentIndex / (order.length - 1)) * 100;
            progress.style.width = `${percent}%`;
        }
    },

    async checkRequirements() {
        const rows = document.querySelectorAll('[data-requirement]');
        const resultBox = document.getElementById('requirement-check-result');
        const continueBtn = document.getElementById('continue-btn');
        const progressFill = document.querySelector('[data-requirement-progress-fill]');
        const progressLabel = document.querySelector('[data-requirement-progress]');
        let blockingFailed = false;
        let passedCount = 0;
        const total = rows.length;

        if (resultBox) {
            resultBox.innerHTML = '';
            resultBox.classList.add('hidden');
        }

        if (progressLabel) {
            progressLabel.textContent = 'Checking…';
            progressLabel.classList.remove('text-slate-700', 'font-semibold');
        }
        if (progressFill) {
            progressFill.style.width = '0%';
            progressFill.classList.remove('is-pass', 'is-fail');
        }

        for (const [index, row] of [...rows].entries()) {
            row.style.animationDelay = `${index * 40}ms`;
            row.classList.add('animate-slide-up');
            row.classList.remove('pass', 'fail', 'warn', 'checking');

            const icon = row.querySelector('[data-status-icon]');
            if (icon) {
                icon.innerHTML = '<span class="requirement-status-dot"></span>';
            }

            row.classList.add('checking');

            const code = row.dataset.requirement;

            try {
                const json = await this.sendRequest('onCheckRequirements', { code });
                const passed = !!json.result;
                const blocking = json.blocking !== false;

                row.classList.remove('checking');

                if (passed) {
                    row.classList.add('pass');
                    passedCount++;
                } else if (!blocking) {
                    row.classList.add('warn');
                } else {
                    row.classList.add('fail');
                }

                if (icon) {
                    icon.innerHTML = passed
                        ? '<svg class="h-5 w-5 text-emerald-600 animate-bounce-in" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>'
                        : !blocking
                            ? '<svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                            : '<svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
                }

                if (!passed && blocking) {
                    blockingFailed = true;
                }
            } catch (error) {
                row.classList.remove('checking');
                row.classList.add('fail');
                blockingFailed = true;
                if (icon) {
                    icon.innerHTML = '<svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
                }
            }

            if (progressFill) {
                progressFill.style.width = `${((index + 1) / total) * 100}%`;
            }

            if (progressLabel) {
                progressLabel.textContent = `${passedCount} of ${total} passed`;
            }
        }

        if (continueBtn) {
            continueBtn.disabled = blockingFailed;
            continueBtn.classList.toggle('opacity-50', blockingFailed);
            continueBtn.classList.toggle('cursor-not-allowed', blockingFailed);
        }

        if (progressFill) {
            progressFill.classList.remove('is-pass', 'is-fail');
            progressFill.classList.add(passedCount === total ? 'is-pass' : 'is-fail');
        }

        if (blockingFailed && resultBox) {
            resultBox.innerHTML = `<div class="mt-4 requirement-alert animate-slide-up">${window.setupLang.alert_requirement_error} <a href="${window.setupLang.docs_troubleshooting}" class="font-medium underline underline-offset-2" target="_blank" rel="noopener">${window.setupLang.text_docs_link}</a></div>`;
            resultBox.classList.remove('hidden');
        } else if (progressLabel) {
            progressLabel.textContent = blockingFailed ? `${passedCount} of ${total} passed` : 'All checks passed';
            progressLabel.classList.toggle('text-slate-700', !blockingFailed);
            progressLabel.classList.toggle('font-semibold', !blockingFailed);
        }
    },

    async startInstall() {
        this.renderView('install');
        this.goToStep('install');

        const steps = this.Steps.install.steps;
        let downloadOffset = 0;
        let success = true;

        for (const [index, step] of steps.entries()) {
            const row = document.querySelector(`[data-timeline="${step.process}"]`);
            row?.classList.add('active');

            const label = row?.querySelector('[data-timeline-label]');
            if (label) label.classList.add('font-semibold', 'text-brand');

            try {
                if (step.process === 'download') {
                    let complete = false;
                    while (!complete) {
                        const json = await this.sendRequest('onInstall', {
                            process: 'download',
                            offset: downloadOffset,
                        });

                        complete = json.result?.complete ?? true;
                        downloadOffset = json.result?.downloaded ?? 0;

                        const bar = document.querySelector('[data-download-progress]');
                        if (bar && json.result?.total) {
                            bar.style.width = `${Math.min(100, (json.result.downloaded / json.result.total) * 100)}%`;
                        }
                    }
                } else {
                    const json = await this.sendRequest('onInstall', { process: step.process });

                    if (step.process === 'finalize') {
                        window.installResult = json.result;
                    }
                }

                row?.classList.remove('active');
                row?.classList.add('done');
                row?.querySelector('[data-timeline-icon]')?.replaceChildren(this.iconCheck());
            } catch (error) {
                success = false;
                row?.classList.remove('active');
                row?.classList.add('fail');
                this.showInstallError(error.message);
                break;
            }
        }

        if (success) {
            setTimeout(() => {
                this.goToStep('complete');
                this.applyInstallUrls(window.installResult);
            }, 400);
        }
    },

    applyInstallUrls(result) {
        if (!result) return;
        const admin = document.querySelector('[data-admin-url]');
        const front = document.querySelector('[data-front-url]');
        if (admin && result.adminUrl) admin.href = result.adminUrl;
        if (front && result.frontUrl) front.href = result.frontUrl;
    },

    showInstallError(message) {
        const box = document.getElementById('install-error');
        if (!box) return;

        box.innerHTML = `
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700 animate-slide-up">
                <p class="font-semibold">${window.setupLang.alert_install_failed}</p>
                <p class="mt-2">${message}</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <button type="button" data-install-control="retry-install" class="setup-btn setup-btn-primary">${window.setupLang.button_retry}</button>
                    <a href="${window.setupLang.docs_troubleshooting}" target="_blank" rel="noopener" class="setup-btn setup-btn-secondary">${window.setupLang.text_docs_link}</a>
                </div>
            </div>`;
        box.classList.remove('hidden');
    },

    iconCheck() {
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('class', 'h-5 w-5 text-emerald-500 animate-bounce-in');
        svg.setAttribute('fill', 'none');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('stroke', 'currentColor');
        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>';
        return svg;
    },

    setLoading(loading) {
        this.wizard.loading = loading;
        document.querySelectorAll('[data-loading-target]').forEach((el) => {
            el.classList.toggle('opacity-60', loading);
        });
    },

    showError(message) {
        showToast(message, 'error');
    },

    clearError() {
        clearToast();
    },
};

export { Installer };
