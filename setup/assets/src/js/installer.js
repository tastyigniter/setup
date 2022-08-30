var Installer = {

    currentStep: null,

    options: {
        page: "#page",
        form: "#setup-form",
        currentStepSelector: "#current-step",
        submitButton: "button[type=\"submit\"]",
        progressBox: "#progress-box",
        flashMessageSelector: "#flash-message"
    },

    Steps: {
        start: {handler: "onLoadLicence"},
        license: {handler: "onCompleteRequirements"},
        requirements: {handler: "onCheckRequirements"},
        database: {handler: "onCheckDatabase"},
        settings: {handler: "onValidateSettings"},
        install: {
            dataCache: {},
            handler: "onInstall",
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
        proceed: {proceedUrl: '/admin/settings', frontUrl: '/'},
        success: {}
    },

    init: function () {
        Installer.$page = $(Installer.options.page)
        Installer.$pageContent = Installer.$page.find('[data-html="content"]')
        Installer.$pageModal = Installer.$page.find('[data-html="modal"]')
        Installer.$progressBox = $(Installer.options.progressBox)
        Installer.currentStep = $(Installer.options.currentStepSelector).val()
        Installer.$form = $(Installer.options.form)

        // render
        Installer.updateWizard(Installer.currentStep)
        Installer.renderView(Installer.currentStep)

        $(document).ready(function () {
            Installer.$submitBtn = $(Installer.options.submitButton)
            Installer.$form.submit(Installer.submitForm)
            Installer.$page.on('click', '[data-install-control]', Installer.onControlClick)
            Installer.$pageModal.on('hidden.bs.modal', Installer.onModalHidden)
        })
    },

    submitForm: function (e) {
        e.preventDefault()
        if (!Installer.$submitBtn.hasClass('disabled')) {
            Installer.currentStep = $(Installer.options.currentStepSelector).val()
            Installer.processForm()
        }
    },

    onControlClick: function (event) {
        var $button = $(event.currentTarget),
            control = $button.data('installControl')

        switch (control) {
            case 'retry-check':
                Installer.checkRetry()
                break
            case 'load-license':
                Installer.processResponse({step: 'license'})
                break
            case 'accept-license':
                Installer.processResponse({step: 'requirements'})
                break
            case 'complete-requirements':
                Installer.sendRequest('onCompleteRequirements', {}).done(function (json) {
                    Installer.processResponse(json)
                })
                break
            case 'install-fresh':
            case 'install-prebuilt':
                Installer.processInstall($button, control)
                break
        }
    },

    onModalHidden: function (event) {
        var $modal = $(event.currentTarget)
        $modal.find('.modal-dialog').remove()
    },

    disableSubmitButton: function (disabled) {
        if (Installer.$submitBtn && Installer.$submitBtn.length) {

            Installer.$submitBtn.prop("disabled", disabled)
            if (disabled) {
                Installer.$submitBtn.addClass("disabled")
            } else {
                Installer.$submitBtn.removeClass("disabled")
            }
        }
    },

    getHandler: function (currentStep) {
        var step = Installer.Steps[currentStep]

        return step.handler
    },

    processForm: function () {
        if (Installer.$form.length) {
            var progressMessage = Installer.getAlert(Installer.currentStep),
                requestHandler = Installer.getHandler(Installer.currentStep)

            Installer.sendRequest(requestHandler, {}, progressMessage).done(function (json) {
                Installer.processResponse(json)
            })
        }
    },

    sendRequest: function (handler, data, message) {
        data.handler = handler
        var postData = (typeof Installer.$form !== "undefined")
            ? Installer.$form.serialize() + (typeof data === "undefined" ? ""
            : "&" + $.param(data)) : []

        Installer.disableSubmitButton(true)

        return $.ajax({
            async: true,
            type: "POST",
            cache: true,
            data: postData,
        }).done(function () {
            Installer.disableSubmitButton(false)
        }).fail(function (xhr) {
            Installer.disableSubmitButton(false)
            Installer.flashMessage("danger", xhr.responseText)
        })
    },

    getAlert: function (step) {
        if (Installer.Steps.hasOwnProperty(step))
            return Installer.Steps[step].msg
    },

    checkRetry: function () {
        Installer.checkRequirements()
    },

    updateWizard: function (step) {
        var steps = [
            "license",
            "requirements",
            "database",
            "settings",
            "install",
            "proceed"
        ]

        $(Installer.options.currentStepSelector).val(step);
        $('body').attr('class', step);
        Installer.currentStep = step;
        //
        // for (var index in steps) {
        //     var $step = Installer.$page.find('[data-wizard="' + steps[index] + '"]')
        //     $step.removeClass('in-progress').addClass('complete')
        //
        //     if (steps[index] === step) {
        //         $step.addClass('in-progress')
        //         break
        //     }
        // }
    },

    checkRequirements: function () {
        var $requirements = Installer.$page.find('[data-requirement]'),
            $checkResult = $('#requirement-check-result').empty(),
            failedAlertTemplate = $('[data-partial="_alert_check_failed"]').clone().html(),
            completeAlertTemplate = $('[data-partial="_alert_check_complete"]').clone().html(),
            requestHandler = Installer.Steps.requirements.handler,
            requestChain = [],
            failCodes = [],
            failMessages = [],
            success = true

        $.each($requirements, function (index, requirement) {

            var $requirement = $(requirement),
                data = $requirement.data(),
                timeout = 1500

            requestChain.push(function () {
                var deferred = $.Deferred(),
                    $requirementStatus = $requirement.find('[role="status"]')

                $requirement.addClass('pulse')
                $requirement.find('[data-label]').removeClass('text-muted')
                $requirement.find('[data-spinner]')
                    .removeClass('fas fa-circle text-muted')
                    .addClass('spinner-grow spinner-grow-sm')

                Installer.sendRequest(requestHandler, {
                    code: data.requirement
                }).done(function (json) {
                    setTimeout(function () {
                        if (json.result) {
                            $requirement.addClass('done')
                            $requirementStatus.addClass('fas fa-circle text-success').removeClass('spinner-grow')
                            deferred.resolve()
                        } else {
                            success = false
                            $requirement.addClass('failed')
                            $requirementStatus.addClass('fas fa-circle text-danger').removeClass('spinner-grow')
                            $requirementStatus.attr('title', data.hint)
                            failCodes.push(data.requirement)
                            failMessages.push(data.hint)
                            deferred.resolve()
                        }
                        deferred.resolve()
                    }, timeout)
                }).fail(function () {
                    setTimeout(function () {
                        success = false
                        failCodes.push(data.requirement)
                        failMessages.push(data.hint)
                        $requirement.addClass('failed')
                        $requirementStatus.attr('title', data.hint)
                        deferred.resolve()
                    }, timeout)
                })

                return deferred
            })
        })

        $.waterfall.apply(this, requestChain).always(function () {
        }).done(function (arr) {
            if (!success) {
                $checkResult.append(Mustache.render(failedAlertTemplate, {
                    code: failCodes.join(', '),
                    message: failMessages.join('<br> ')
                }))
                $checkResult.show().addClass('animated fadeIn')
            } else {
                Installer.$form.append('<input type="hidden" name="requirement" value="complete">')
                $checkResult.append(Mustache.render(completeAlertTemplate))
                $checkResult.show().addClass('animated fadeIn')
            }
        })
    },

    processInstallSteps: function (steps) {
        var success = true,
            requestChain = [],
            failMessages = [],
            proceedUrl = null,
            $progressMessage = Installer.$pageContent.find('.install-progress .message')

        $.each(steps, function (index, stepItems) {

            var step = Installer.Steps.install.steps[index]

            $.each(stepItems, function (itemIndex, item) {
                var timeout = 500

                requestChain.push(function () {
                    var postData,
                        deferred = $.Deferred(),
                        beforeSendMessage = Mustache.render(step.msg, item)

                    postData = {
                        process: item.process,
                        disableLog: true,
                        item: item
                    }

                    $progressMessage.text(beforeSendMessage)

                    Installer.sendRequest('onInstall', postData, beforeSendMessage)
                        .done(function (json) {
                            setTimeout(function () {
                                if (json.result) {
                                    if (index === "install") proceedUrl = json.result
                                    deferred.resolve()
                                } else {
                                    success = false
                                    var errorMessage = Mustache.render(step.error, item)
                                    $progressMessage.text(errorMessage)
                                    failMessages.push(errorMessage)
                                    deferred.resolve()
                                }
                                deferred.resolve()
                            }, timeout)
                        })
                        .fail(function () {
                            setTimeout(function () {
                                success = false
                                deferred.resolve()
                            }, timeout)
                        })

                    return deferred
                })
            })
        })

        $.waterfall.apply(this, requestChain).done(function () {
            if (!success) {
                Installer.$pageContent.html($('[data-partial="_alert__alert_install_failed"]').html())
                $('.install_failed .message').text(failMessages.join('<br />'))
            } else {
                Installer.updateWizard('proceed')
                Installer.renderView('proceed', {proceedUrl: proceedUrl ? proceedUrl : '/admin/login', frontUrl: '/'})
            }
        })
    },

    renderView: function (name, data) {
        var pageData = Installer.Steps[name],
            view = pageData.view

        if (!pageData)
            pageData = {}

        if (name) {
            var viewHtml = Mustache.render($(view).html(), $.extend(pageData, data, {}))
            Installer.$pageContent.html(viewHtml)
        }

        if (Installer.currentStep === 'requirements') {
            Installer.checkRequirements()
        }
    },

    flashMessage: function (type, message) {
        if (!message)
            return

        var $flashMessage = $(Installer.options.flashMessageSelector),
            $alert = $('<div />', {
                class: 'animated bounceIn shadow alert alert-' + type+' alert-dismissible'
            })

        $flashMessage.empty()

        $flashMessage.addClass('show')
        $alert.append(message)
        $alert.append('<button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>')
        $flashMessage.append($alert)

        if (type !== 'danger')
            $alert.delay(5000).fadeOut(400, function () {
                $(this).remove()
            })
    },

    processResponse: function (json) {
        var flashMessage = json.flash,
            settingsInstalled = json.settingsInstalled,
            nextStep = json.step

        if (flashMessage) {
            Installer.flashMessage(flashMessage.type, flashMessage.message)
        }

        switch (nextStep) {
            case 'license':
            case 'requirements':
            case 'database':
            case 'settings':
            case 'install':
                Installer.updateWizard(nextStep)
                Installer.renderView(nextStep)

                if (settingsInstalled)
                    Installer.$pageContent.find('[data-html="existing-database"]').removeClass('d-none')

                break
        }
    },

    processInstall: function ($btn, control) {
        var installData = {
            themeCode: $btn.data('themeCode'),
            process: 'apply',
            disableLog: true
        }

        $btn.attr('disabled', true)

        Installer.$pageContent.html($('[data-partial="_alert_install_progress"]').html())

        Installer.sendRequest('onInstall', installData).done(function (json) {
            Installer.processInstallSteps(json.result)
        }).fail(function () {
                Installer.$pageContent.html($('[data-partial="install"]').html())
            })
            .always(function () {
                $btn.attr('disabled', false)
            })
    },
}
