window.Installer = {

    currentStep: null,

    hideSidebar: true,

    options: {
        page: "#page",
        form: "#setup-form",
        currentStepSelector: "#current-step",
        submitButton: "button[type=\"submit\"]",
        progressBox: "#progress-box",
        flashMessageSelector: "#flash-message"
    },

    Steps: {
        start: {handler: "onLoadLicence", view: "[data-partial=\"start\"]"},
        requirements: {handler: "onCheckRequirements", view: "[data-partial=\"requirements\"]"},
        license: {handler: "onCompleteRequirements", view: "[data-partial=\"license\"]"},
        database: {handler: "onCheckDatabase", view: "[data-partial=\"database\"]"},
        settings: {handler: "onValidateSettings", view: "[data-partial=\"settings\"]"},
        install: {
            dataCache: {},
            handler: "onInstall",
            view: "[data-partial=\"install\"]",
            steps: {
                download: {
                    msg: "Downloading {{name}} {{type}}...",
                    error: "Downloading {{name}} {{type}} failed. See setup log."
                },
                extract: {
                    msg: "Extracting {{name}} {{type}}...",
                    error: "Extracting {{name}} {{type}} failed. See setup log."
                },
                config: {
                    msg: "Writing site configuration files...",
                    error: "Writing site configuration files failed. See setup log."
                },
                install: {
                    msg: "Finishing site setup...",
                    error: "Finishing site setup failed. See setup log."
                }
            }
        },
        proceed: {proceedUrl: '/admin/settings', frontUrl: '/', view: "[data-partial=\"proceed\"]"},
        success: {}
    },

    init() {
        var self = this
        self.$page = document.querySelector(self.options.page)
        self.$pageContent = self.$page.querySelector('[data-html="content"]')
        self.$progressBox = document.querySelector(self.options.progressBox)
        self.$currentStep = document.querySelector(self.options.currentStepSelector)
        self.currentStep = self.$currentStep.value
        self.$form = document.querySelector(self.options.form)

        render(function () {
            self.updateWizard(self.currentStep)
            self.renderView(self.currentStep)

            self.$submitBtn = document.querySelector(self.options.submitButton)
            document.addEventListener('click', function (event) {
                if (event.target && event.target.hasAttribute('data-install-control')) {
                    self.onControlClick(event.target)
                }
            })

            // self.$pageModal.on('hidden.bs.modal', self.onModalHidden)
        })
    },

    submitForm() {
        if (this.$submitBtn && !this.$submitBtn.classList.contains('disabled')) {
            this.currentStep = this.$currentStep.value
            this.hideSidebar = this.currentStep === 'start' || this.currentStep === 'proceed'
            this.processForm()
        }
    },

    onControlClick($button) {
        const control = $button.getAttribute('data-install-control')

        switch (control) {
            case 'retry-check':
                this.checkRetry()
                break
            case 'load-license':
                this.processResponse({step: 'license'})
                break
            case 'accept-license':
                this.processResponse({step: 'requirements'})
                break
            case 'complete-requirements':
                var self = this
                this.sendRequest('onCompleteRequirements', {}).then(function (json) {
                    self.processResponse(json)
                })
                break
            case 'install-fresh':
            case 'install-prebuilt':
                this.processInstall($button, control)
                break
        }
    },

    onModalHidden(event) {
        var $modal = $(event.currentTarget)
        $modal.find('.modal-dialog').remove()
    },

    disableSubmitButton(disabled) {
        if (this.$submitBtn && this.$submitBtn.length) {

            this.$submitBtn.prop("disabled", disabled)
            if (disabled) {
                this.$submitBtn.addClass("disabled")
            } else {
                this.$submitBtn.removeClass("disabled")
            }
        }
    },

    getHandler(currentStep) {
        var step = this.Steps[currentStep]

        return step.handler
    },

    processForm() {
        if (this.$form.length) {
            var self = this,
                progressMessage = this.getAlert(this.currentStep),
                requestHandler = this.getHandler(this.currentStep)

            self
                .sendRequest(requestHandler, {}, progressMessage)
                .then(function (json) {
                    self.processResponse(json)
                })
                .catch(function (response) {
                    response.then(function (text) {
                        self.flashMessage("danger", text)
                    })
                })
        }
    },

    sendRequest(handler, data, message) {
        var self = this,
            postData = new FormData(self.$form)

        postData.append('handler', handler)
        for (const key in data) {
            postData.set(key, data[key])
        }

        self.disableSubmitButton(true)
        return fetch('setup.php', {method: "POST", body: postData})
            .then(function (response) {
                self.disableSubmitButton(false)
                if (response.ok) {
                    return Promise.resolve(response.json())
                } else {
                    return Promise.reject(response.text())
                }
            })
    },

    getAlert(step) {
        if (this.Steps.hasOwnProperty(step))
            return this.Steps[step].msg
    },

    checkRetry() {
        this.renderView('requirements')
        this.checkRequirements()
    },

    updateWizard(step) {
        this.$currentStep.value = step;
        document.querySelector('body').classList.add(step);
        this.currentStep = step;
        this.hideSidebar = step === 'start' || step === 'proceed'
    },

    checkRequirements() {
        var self = this,
            $requirements = this.$page.querySelectorAll('[data-requirement]'),
            $checkResult = document.querySelector('#requirement-check-result'),
            failedAlertTemplate = document.querySelector('[data-partial="_alert_check_failed"]').cloneNode(true).innerHTML,
            completeAlertTemplate = document.querySelector('[data-partial="_alert_check_complete"]').cloneNode(true).innerHTML,
            requestHandler = this.Steps.requirements.handler,
            requestChain = [],
            failCodes = [],
            failMessages = [],
            success = true

        $requirements.forEach((requirement, index) => {
            requestChain.push(function () {
                requirement.classList.remove('opacity-25')
                const $failureStatus = requirement.querySelector('.failure')

                return self
                    .sendRequest(requestHandler, {
                        code: requirement.getAttribute('data-requirement')
                    })
                    .then(function (json) {
                        requirement.querySelector('.loading').classList.add('hidden')
                        if (json && json.result) {
                            requirement.querySelector('.success').classList.remove('hidden')
                        } else {
                            success = false
                            $failureStatus.classList.remove('hidden')
                            $failureStatus.setAttribute('title', requirement.getAttribute('data-hint'))
                            failCodes.push(requirement.getAttribute('data-requirement'))
                            failMessages.push(requirement.getAttribute('data-hint'))
                        }
                    })
            })
        })

        requestChain
            .reduce(function (x, y) {
                return x.then(y);
            }, Promise.resolve())
            .then(function (x) {
                if (!success) {
                    $checkResult.innerHTML = Mustache.render(failedAlertTemplate, {
                        code: failCodes.join(', '),
                        message: failMessages.join('<br> ')
                    })
                } else {
                    Installer.$form.querySelector('[name="requirement"]').value = 'complete'
                    $checkResult.innerHTML = Mustache.render(completeAlertTemplate)
                }
            })
    },

    processInstallSteps(steps) {
        let self = this,
            success = true,
            requestChain = [],
            failMessages = [],
            proceedUrl = null,
            $progressMessage = this.$pageContent.querySelector('.install-progress .message')

        for (const index in steps) {
            let stepItems = steps[index],
                step = self.Steps.install.steps[index]

            stepItems.forEach((item, itemIndex) => {
                requestChain.push(() => {
                    const postData = {
                            process: item.process,
                            disableLog: true,
                            item: item
                        },
                        beforeSendMessage = Mustache.render(step.msg, item)

                    $progressMessage.textContent = beforeSendMessage

                    return self
                        .sendRequest('onInstall', postData, beforeSendMessage)
                        .then(function (json) {
                            if (json.result) {
                                if (index === "install") proceedUrl = json.result
                            } else {
                                success = false
                                var errorMessage = Mustache.render(step.error, item)
                                $progressMessage.text(errorMessage)
                                failMessages.push(errorMessage)
                            }
                        })
                        .catch(function () {
                            success = false
                        })
                })
            })
        }

        requestChain
            .reduce(function (x, y) {
                return x.then(y);
            }, Promise.resolve())
            .then(function (x) {
                if (!success) {
                    self.$pageContent.innerHTML = Mustache.render(document.querySelector(
                        '[data-partial="_alert_install_failed"]'
                    ).innerHTML)
                    document.querySelector('.install_failed .message').textContent = failMessages.join('<br />')
                } else {
                    self.updateWizard('proceed')
                    self.renderView('proceed', {proceedUrl: proceedUrl ? proceedUrl : '/admin/login', frontUrl: '/'})
                }
            })
    },

    renderView(name, data) {
        var pageData = this.Steps[name],
            view = pageData.view

        if (!pageData)
            pageData = {}

        if (name) {
            this.$pageContent.innerHTML = Mustache.render(
                document.querySelector(view).innerHTML, {...pageData, ...data}
            )
        }

        if (this.currentStep === 'requirements') {
            this.checkRequirements()
        }

        // this.$pageModal.modal('hide')
    },

    flashMessage(type, message) {
        if (!message)
            return

        var $flashMessage = document.querySelector(this.options.flashMessageSelector),
            $alert = document.createElement('div'),
            alertTemplate = document.querySelector('[data-partial="alert-'+type+'"]').innerHTML

        $alert.innerHTML = Mustache.render(alertTemplate, {message: message})

        $flashMessage.classList.remove('hidden')
        $flashMessage.appendChild($alert)

        if (type == 'danger') {
            $alert.addEventListener('click', function (event) {
                event.currentTarget.remove()
            })
        } else {
            setTimeout(function () {
                $alert.remove()
            }, 5000)
        }
    },

    processResponse(json) {
        var self = this,
            flashMessage = json.flash,
            showModal = json.modal,
            nextStep = json.step

        if (flashMessage) {
            self.flashMessage(flashMessage.type, flashMessage.message)
        }

        if (showModal) {
            window.Swal.fire({
                icon: 'warning',
                html: Mustache.render(document.querySelector('[data-partial="'+showModal+'"]').innerHTML),
                showCancelButton: true,
                customClass: {
                    confirmButton: '!bg-orange-600 !shadow-orange-600/50',
                    cancelButton: '!bg-white !border !border-solid !border-gray-800 !text-gray-800',
                },
                confirmButtonText: 'Yes, proceed!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const $el = document.createElement('input')
                    $el.setAttribute('type', 'hidden')
                    $el.setAttribute('name', 'upgrade')
                    $el.setAttribute('value', '1')
                    self.$form.appendChild($el)
                    self.submitForm()
                }
            })
        }

        switch (nextStep) {
            case 'license':
            case 'requirements':
            case 'database':
            case 'settings':
            case 'install':
                this.updateWizard(nextStep)
                this.renderView(nextStep)
                break
        }
    },

    processInstall($btn, control) {
        var self = this,
            installData = {
                themeCode: $btn.dataset.themeCode,
                process: 'apply',
                disableLog: true
            }

        $btn.setAttribute('disabled', true)
        self.$pageContent.innerHTML = document.querySelector('[data-partial="_alert_install_progress"]').innerHTML

        self
            .sendRequest('onInstall', installData)
            .then(function (json) {
                $btn.setAttribute('disabled', false)
                self.processInstallSteps(json.result)
            })
            .catch(function () {
                $btn.setAttribute('disabled', false)
                self.$pageContent.innerHTML = document.querySelector('[data-partial="install"]').innerHTML
            })
    },
}
