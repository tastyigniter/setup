/*!
 * jquery.waterfall.js
 * https://github.com/dio-el-claire/jquery.waterfall
 */
(function(e){e.waterfall=function(){var t=[],n=e.Deferred(),r=0;e.each(arguments,function(i,s){t.push(function(){var i=[].slice.apply(arguments),o;if(typeof s=="function"){if(!((o=s.apply(null,i))&&o.promise)){o=e.Deferred()[o===false?"reject":"resolve"](o)}}else if(s&&s.promise){o=s}else{o=e.Deferred()[s===false?"reject":"resolve"](s)}o.fail(function(){n.reject.apply(n,[].slice.apply(arguments))}).done(function(e){r++;i.push(e);r==t.length?n.resolve.apply(n,i):t[r].apply(null,i)})})});t.length?t[0]():n.resolve();return n}})(jQuery);

/*!
 * mustache.js - Logic-less {{mustache}} templates with JavaScript
 * http://github.com/janl/mustache.js
 */

/*global define: false Mustache: true*/

(function defineMustache (global, factory) {
  if (typeof exports === 'object' && exports && typeof exports.nodeName !== 'string') {
    factory(exports); // CommonJS
  } else if (typeof define === 'function' && define.amd) {
    define(['exports'], factory); // AMD
  } else {
    global.Mustache = {};
    factory(global.Mustache); // script, wsh, asp
  }
}(this, function mustacheFactory (mustache) {

  var objectToString = Object.prototype.toString;
  var isArray = Array.isArray || function isArrayPolyfill (object) {
    return objectToString.call(object) === '[object Array]';
  };

  function isFunction (object) {
    return typeof object === 'function';
  }

  /**
   * More correct typeof string handling array
   * which normally returns typeof 'object'
   */
  function typeStr (obj) {
    return isArray(obj) ? 'array' : typeof obj;
  }

  function escapeRegExp (string) {
    return string.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
  }

  /**
   * Null safe way of checking whether or not an object,
   * including its prototype, has a given property
   */
  function hasProperty (obj, propName) {
    return obj != null && typeof obj === 'object' && (propName in obj);
  }

  // Workaround for https://issues.apache.org/jira/browse/COUCHDB-577
  // See https://github.com/janl/mustache.js/issues/189
  var regExpTest = RegExp.prototype.test;
  function testRegExp (re, string) {
    return regExpTest.call(re, string);
  }

  var nonSpaceRe = /\S/;
  function isWhitespace (string) {
    return !testRegExp(nonSpaceRe, string);
  }

  var entityMap = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
    '/': '&#x2F;',
    '`': '&#x60;',
    '=': '&#x3D;'
  };

  function escapeHtml (string) {
    return String(string).replace(/[&<>"'`=\/]/g, function fromEntityMap (s) {
      return entityMap[s];
    });
  }

  var whiteRe = /\s*/;
  var spaceRe = /\s+/;
  var equalsRe = /\s*=/;
  var curlyRe = /\s*\}/;
  var tagRe = /#|\^|\/|>|\{|&|=|!/;

  /**
   * Breaks up the given `template` string into a tree of tokens. If the `tags`
   * argument is given here it must be an array with two string values: the
   * opening and closing tags used in the template (e.g. [ "<%", "%>" ]). Of
   * course, the default is to use mustaches (i.e. mustache.tags).
   *
   * A token is an array with at least 4 elements. The first element is the
   * mustache symbol that was used inside the tag, e.g. "#" or "&". If the tag
   * did not contain a symbol (i.e. {{myValue}}) this element is "name". For
   * all text that appears outside a symbol this element is "text".
   *
   * The second element of a token is its "value". For mustache tags this is
   * whatever else was inside the tag besides the opening symbol. For text tokens
   * this is the text itself.
   *
   * The third and fourth elements of the token are the start and end indices,
   * respectively, of the token in the original template.
   *
   * Tokens that are the root node of a subtree contain two more elements: 1) an
   * array of tokens in the subtree and 2) the index in the original template at
   * which the closing tag for that section begins.
   */
  function parseTemplate (template, tags) {
    if (!template)
      return [];

    var sections = [];     // Stack to hold section tokens
    var tokens = [];       // Buffer to hold the tokens
    var spaces = [];       // Indices of whitespace tokens on the current line
    var hasTag = false;    // Is there a {{tag}} on the current line?
    var nonSpace = false;  // Is there a non-space char on the current line?

    // Strips all whitespace tokens array for the current line
    // if there was a {{#tag}} on it and otherwise only space.
    function stripSpace () {
      if (hasTag && !nonSpace) {
        while (spaces.length)
          delete tokens[spaces.pop()];
      } else {
        spaces = [];
      }

      hasTag = false;
      nonSpace = false;
    }

    var openingTagRe, closingTagRe, closingCurlyRe;
    function compileTags (tagsToCompile) {
      if (typeof tagsToCompile === 'string')
        tagsToCompile = tagsToCompile.split(spaceRe, 2);

      if (!isArray(tagsToCompile) || tagsToCompile.length !== 2)
        throw new Error('Invalid tags: ' + tagsToCompile);

      openingTagRe = new RegExp(escapeRegExp(tagsToCompile[0]) + '\\s*');
      closingTagRe = new RegExp('\\s*' + escapeRegExp(tagsToCompile[1]));
      closingCurlyRe = new RegExp('\\s*' + escapeRegExp('}' + tagsToCompile[1]));
    }

    compileTags(tags || mustache.tags);

    var scanner = new Scanner(template);

    var start, type, value, chr, token, openSection;
    while (!scanner.eos()) {
      start = scanner.pos;

      // Match any text between tags.
      value = scanner.scanUntil(openingTagRe);

      if (value) {
        for (var i = 0, valueLength = value.length; i < valueLength; ++i) {
          chr = value.charAt(i);

          if (isWhitespace(chr)) {
            spaces.push(tokens.length);
          } else {
            nonSpace = true;
          }

          tokens.push([ 'text', chr, start, start + 1 ]);
          start += 1;

          // Check for whitespace on the current line.
          if (chr === '\n')
            stripSpace();
        }
      }

      // Match the opening tag.
      if (!scanner.scan(openingTagRe))
        break;

      hasTag = true;

      // Get the tag type.
      type = scanner.scan(tagRe) || 'name';
      scanner.scan(whiteRe);

      // Get the tag value.
      if (type === '=') {
        value = scanner.scanUntil(equalsRe);
        scanner.scan(equalsRe);
        scanner.scanUntil(closingTagRe);
      } else if (type === '{') {
        value = scanner.scanUntil(closingCurlyRe);
        scanner.scan(curlyRe);
        scanner.scanUntil(closingTagRe);
        type = '&';
      } else {
        value = scanner.scanUntil(closingTagRe);
      }

      // Match the closing tag.
      if (!scanner.scan(closingTagRe))
        throw new Error('Unclosed tag at ' + scanner.pos);

      token = [ type, value, start, scanner.pos ];
      tokens.push(token);

      if (type === '#' || type === '^') {
        sections.push(token);
      } else if (type === '/') {
        // Check section nesting.
        openSection = sections.pop();

        if (!openSection)
          throw new Error('Unopened section "' + value + '" at ' + start);

        if (openSection[1] !== value)
          throw new Error('Unclosed section "' + openSection[1] + '" at ' + start);
      } else if (type === 'name' || type === '{' || type === '&') {
        nonSpace = true;
      } else if (type === '=') {
        // Set the tags for the next time around.
        compileTags(value);
      }
    }

    // Make sure there are no open sections when we're done.
    openSection = sections.pop();

    if (openSection)
      throw new Error('Unclosed section "' + openSection[1] + '" at ' + scanner.pos);

    return nestTokens(squashTokens(tokens));
  }

  /**
   * Combines the values of consecutive text tokens in the given `tokens` array
   * to a single token.
   */
  function squashTokens (tokens) {
    var squashedTokens = [];

    var token, lastToken;
    for (var i = 0, numTokens = tokens.length; i < numTokens; ++i) {
      token = tokens[i];

      if (token) {
        if (token[0] === 'text' && lastToken && lastToken[0] === 'text') {
          lastToken[1] += token[1];
          lastToken[3] = token[3];
        } else {
          squashedTokens.push(token);
          lastToken = token;
        }
      }
    }

    return squashedTokens;
  }

  /**
   * Forms the given array of `tokens` into a nested tree structure where
   * tokens that represent a section have two additional items: 1) an array of
   * all tokens that appear in that section and 2) the index in the original
   * template that represents the end of that section.
   */
  function nestTokens (tokens) {
    var nestedTokens = [];
    var collector = nestedTokens;
    var sections = [];

    var token, section;
    for (var i = 0, numTokens = tokens.length; i < numTokens; ++i) {
      token = tokens[i];

      switch (token[0]) {
        case '#':
        case '^':
          collector.push(token);
          sections.push(token);
          collector = token[4] = [];
          break;
        case '/':
          section = sections.pop();
          section[5] = token[2];
          collector = sections.length > 0 ? sections[sections.length - 1][4] : nestedTokens;
          break;
        default:
          collector.push(token);
      }
    }

    return nestedTokens;
  }

  /**
   * A simple string scanner that is used by the template parser to find
   * tokens in template strings.
   */
  function Scanner (string) {
    this.string = string;
    this.tail = string;
    this.pos = 0;
  }

  /**
   * Returns `true` if the tail is empty (end of string).
   */
  Scanner.prototype.eos = function eos () {
    return this.tail === '';
  };

  /**
   * Tries to match the given regular expression at the current position.
   * Returns the matched text if it can match, the empty string otherwise.
   */
  Scanner.prototype.scan = function scan (re) {
    var match = this.tail.match(re);

    if (!match || match.index !== 0)
      return '';

    var string = match[0];

    this.tail = this.tail.substring(string.length);
    this.pos += string.length;

    return string;
  };

  /**
   * Skips all text until the given regular expression can be matched. Returns
   * the skipped string, which is the entire tail if no match can be made.
   */
  Scanner.prototype.scanUntil = function scanUntil (re) {
    var index = this.tail.search(re), match;

    switch (index) {
      case -1:
        match = this.tail;
        this.tail = '';
        break;
      case 0:
        match = '';
        break;
      default:
        match = this.tail.substring(0, index);
        this.tail = this.tail.substring(index);
    }

    this.pos += match.length;

    return match;
  };

  /**
   * Represents a rendering context by wrapping a view object and
   * maintaining a reference to the parent context.
   */
  function Context (view, parentContext) {
    this.view = view;
    this.cache = { '.': this.view };
    this.parent = parentContext;
  }

  /**
   * Creates a new context using the given view with this context
   * as the parent.
   */
  Context.prototype.push = function push (view) {
    return new Context(view, this);
  };

  /**
   * Returns the value of the given name in this context, traversing
   * up the context hierarchy if the value is absent in this context's view.
   */
  Context.prototype.lookup = function lookup (name) {
    var cache = this.cache;

    var value;
    if (cache.hasOwnProperty(name)) {
      value = cache[name];
    } else {
      var context = this, names, index, lookupHit = false;

      while (context) {
        if (name.indexOf('.') > 0) {
          value = context.view;
          names = name.split('.');
          index = 0;

          /**
           * Using the dot notion path in `name`, we descend through the
           * nested objects.
           *
           * To be certain that the lookup has been successful, we have to
           * check if the last object in the path actually has the property
           * we are looking for. We store the result in `lookupHit`.
           *
           * This is specially necessary for when the value has been set to
           * `undefined` and we want to avoid looking up parent contexts.
           **/
          while (value != null && index < names.length) {
            if (index === names.length - 1)
              lookupHit = hasProperty(value, names[index]);

            value = value[names[index++]];
          }
        } else {
          value = context.view[name];
          lookupHit = hasProperty(context.view, name);
        }

        if (lookupHit)
          break;

        context = context.parent;
      }

      cache[name] = value;
    }

    if (isFunction(value))
      value = value.call(this.view);

    return value;
  };

  /**
   * A Writer knows how to take a stream of tokens and render them to a
   * string, given a context. It also maintains a cache of templates to
   * avoid the need to parse the same template twice.
   */
  function Writer () {
    this.cache = {};
  }

  /**
   * Clears all cached templates in this writer.
   */
  Writer.prototype.clearCache = function clearCache () {
    this.cache = {};
  };

  /**
   * Parses and caches the given `template` and returns the array of tokens
   * that is generated from the parse.
   */
  Writer.prototype.parse = function parse (template, tags) {
    var cache = this.cache;
    var tokens = cache[template];

    if (tokens == null)
      tokens = cache[template] = parseTemplate(template, tags);

    return tokens;
  };

  /**
   * High-level method that is used to render the given `template` with
   * the given `view`.
   *
   * The optional `partials` argument may be an object that contains the
   * names and templates of partials that are used in the template. It may
   * also be a function that is used to load partial templates on the fly
   * that takes a single argument: the name of the partial.
   */
  Writer.prototype.render = function render (template, view, partials) {
    var tokens = this.parse(template);
    var context = (view instanceof Context) ? view : new Context(view);
    return this.renderTokens(tokens, context, partials, template);
  };

  /**
   * Low-level method that renders the given array of `tokens` using
   * the given `context` and `partials`.
   *
   * Note: The `originalTemplate` is only ever used to extract the portion
   * of the original template that was contained in a higher-order section.
   * If the template doesn't use higher-order sections, this argument may
   * be omitted.
   */
  Writer.prototype.renderTokens = function renderTokens (tokens, context, partials, originalTemplate) {
    var buffer = '';

    var token, symbol, value;
    for (var i = 0, numTokens = tokens.length; i < numTokens; ++i) {
      value = undefined;
      token = tokens[i];
      symbol = token[0];

      if (symbol === '#') value = this.renderSection(token, context, partials, originalTemplate);
      else if (symbol === '^') value = this.renderInverted(token, context, partials, originalTemplate);
      else if (symbol === '>') value = this.renderPartial(token, context, partials, originalTemplate);
      else if (symbol === '&') value = this.unescapedValue(token, context);
      else if (symbol === 'name') value = this.escapedValue(token, context);
      else if (symbol === 'text') value = this.rawValue(token);

      if (value !== undefined)
        buffer += value;
    }

    return buffer;
  };

  Writer.prototype.renderSection = function renderSection (token, context, partials, originalTemplate) {
    var self = this;
    var buffer = '';
    var value = context.lookup(token[1]);

    // This function is used to render an arbitrary template
    // in the current context by higher-order sections.
    function subRender (template) {
      return self.render(template, context, partials);
    }

    if (!value) return;

    if (isArray(value)) {
      for (var j = 0, valueLength = value.length; j < valueLength; ++j) {
        buffer += this.renderTokens(token[4], context.push(value[j]), partials, originalTemplate);
      }
    } else if (typeof value === 'object' || typeof value === 'string' || typeof value === 'number') {
      buffer += this.renderTokens(token[4], context.push(value), partials, originalTemplate);
    } else if (isFunction(value)) {
      if (typeof originalTemplate !== 'string')
        throw new Error('Cannot use higher-order sections without the original template');

      // Extract the portion of the original template that the section contains.
      value = value.call(context.view, originalTemplate.slice(token[3], token[5]), subRender);

      if (value != null)
        buffer += value;
    } else {
      buffer += this.renderTokens(token[4], context, partials, originalTemplate);
    }
    return buffer;
  };

  Writer.prototype.renderInverted = function renderInverted (token, context, partials, originalTemplate) {
    var value = context.lookup(token[1]);

    // Use JavaScript's definition of falsy. Include empty arrays.
    // See https://github.com/janl/mustache.js/issues/186
    if (!value || (isArray(value) && value.length === 0))
      return this.renderTokens(token[4], context, partials, originalTemplate);
  };

  Writer.prototype.renderPartial = function renderPartial (token, context, partials) {
    if (!partials) return;

    var value = isFunction(partials) ? partials(token[1]) : partials[token[1]];
    if (value != null)
      return this.renderTokens(this.parse(value), context, partials, value);
  };

  Writer.prototype.unescapedValue = function unescapedValue (token, context) {
    var value = context.lookup(token[1]);
    if (value != null)
      return value;
  };

  Writer.prototype.escapedValue = function escapedValue (token, context) {
    var value = context.lookup(token[1]);
    if (value != null)
      return mustache.escape(value);
  };

  Writer.prototype.rawValue = function rawValue (token) {
    return token[1];
  };

  mustache.name = 'mustache.js';
  mustache.version = '2.3.0';
  mustache.tags = [ '{{', '}}' ];

  // All high-level mustache.* functions use this writer.
  var defaultWriter = new Writer();

  /**
   * Clears all cached templates in the default writer.
   */
  mustache.clearCache = function clearCache () {
    return defaultWriter.clearCache();
  };

  /**
   * Parses and caches the given template in the default writer and returns the
   * array of tokens it contains. Doing this ahead of time avoids the need to
   * parse templates on the fly as they are rendered.
   */
  mustache.parse = function parse (template, tags) {
    return defaultWriter.parse(template, tags);
  };

  /**
   * Renders the `template` with the given `view` and `partials` using the
   * default writer.
   */
  mustache.render = function render (template, view, partials) {
    if (typeof template !== 'string') {
      throw new TypeError('Invalid template! Template should be a "string" ' +
                          'but "' + typeStr(template) + '" was given as the first ' +
                          'argument for mustache#render(template, view, partials)');
    }

    return defaultWriter.render(template, view, partials);
  };

  // This is here for backwards compatibility with 0.4.x.,
  /*eslint-disable */ // eslint wants camel cased function name
  mustache.to_html = function to_html (template, view, partials, send) {
    /*eslint-enable*/

    var result = mustache.render(template, view, partials);

    if (isFunction(send)) {
      send(result);
    } else {
      return result;
    }
  };

  // Export the escaping function so that the user may override it.
  // See https://github.com/janl/mustache.js/issues/244
  mustache.escape = escapeHtml;

  // Export these mainly for testing, but also for advanced usage.
  mustache.Scanner = Scanner;
  mustache.Context = Context;
  mustache.Writer = Writer;

  return mustache;
}));

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

        // if (pageData.title) {
        //     Installer.$page.find("[data-html=\"title\"]").html(pageData.title)
        // }
        //
        // if (pageData.subTitle) {
        //     Installer.$page.find("[data-html=\"subTitle\"]").html(pageData.subTitle)
        // }

        if (name) {
            var viewHtml = Mustache.render($(view).html(), $.extend(pageData, data, {}))
            Installer.$pageContent.html(viewHtml)
        }

        if (Installer.currentStep === 'requirements') {
            Installer.checkRequirements()
        }

        Installer.$pageModal.modal('hide')
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
            showModal = json.modal,
            nextStep = json.step

        if (flashMessage) {
            Installer.flashMessage(flashMessage.type, flashMessage.message)
        }

        if (showModal) {
            var modalTemplate = $('[data-partial="' + showModal + '"]').clone().html();
            Installer.$pageModal.html(Mustache.render(modalTemplate))
            Installer.$pageModal.modal()
        }

        switch (nextStep) {
            case 'license':
            case 'requirements':
            case 'database':
            case 'settings':
            case 'install':
                Installer.updateWizard(nextStep)
                Installer.renderView(nextStep)
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
