(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
;(function () {
    'use strict';

    // window.$ = window.jQuery = require('jquery');
    var ajaxApi = require('./widgets/ajax-api');

    function RkwAjax() {
        var self = this;

        jQuery(window).on('rkw-ajax-api-content-changed', this.onAjaxContentChanged.bind(this));
        this.parseContent(document.body);
    }


    /* Everything in here will be executed as soon as new content is added via AJAX*/
    RkwAjax.prototype.parseContent = function(root) {
        function find(selector) {
            return jQuery(root).is(selector) ? jQuery(root) : jQuery(root).find('*').filter(selector);
        }

        var self = this;

        /* AJAX functionality for new elements */
        find('.ajax, .ajax-feedback, .ajax .ajax-override, .ajax .ajax-send, .ajax .ajax-override-submit, .ajax input, .ajax textarea, .ajax select').ajaxApi();

        find('.next-page[href]').each(function() {
            var link = jQuery(this);
            var boxesContainer = link.parent().parent();
            boxesContainer.find('.box-loading').remove();

            if (link.hasClass('autoload'))
                jQuery(window).scroll(checkBottom);

            link.click(function() {
                if (!jQuery(this).hasClass('disabled')) {

                    link.removeAttr('href').addClass('disabled');
                    boxesContainer.append('<div class="box-loading"/>');
                    jQuery(window).scrollTop(jQuery(window).scrollTop() - 2);
                }
            });

            function checkBottom() {
                if (jQuery(window).scrollTop() + jQuery(window).height() >= jQuery(document).height() - 100) {
                    if (!link.hasClass('disabled')) {
                        link.click();
                    }
                }
            }
        });

        /* delete disabled more box if this is the only entry in the box list */
        find('div.box-wrapper .box.disabled:first-child').parent().remove();
        find('div .box.publication.disabled:first-child').parent().remove();

        /* functionality for search filters */
        var filters = find('input,select');
        filters.on('change', this.sendSearchForm);

    };
    

    RkwAjax.prototype.onAjaxContentChanged = function(event, content) {
        this.parseContent(content);

        /* trigger for external stuff */
        jQuery(document).trigger('rkw-ajax-api-content-loaded', content);

    };


    RkwAjax.prototype.sendSearchForm  = function(event) {
        var searchForm = $(this).closest('form.ajax');
        if (searchForm.length) {
            var searchResult = searchForm.siblings('.search-result-section').first();
            if (searchResult.find('.loading-indicator').length === 0) {
                searchResult.append('<div class="loading-indicator"/>');
            }

            /* trigger for external stuff */
            jQuery(document).trigger('rkw-ajax-api-search-form', searchForm);

            searchForm.submit();
        }
    };

    new RkwAjax();

}());

},{"./widgets/ajax-api":2}],2:[function(require,module,exports){
function AjaxApi(element) {
    var self = this;
    this.element = element;
    this.elementType = element.prop('tagName');

    if (this.elementType === 'FORM' && this.element.hasClass('ajax')) {
        this.sendForm(this.element);
    } else if (this.elementType === 'A' && this.element.hasClass('ajax')) {
        this.sendLink(this.element);
    }
    if (this.element.hasClass('ajax-feedback') && this.element.hasClass('ajax')) {
        this.element.find('input, textarea, select').each(function() {
            self.sendField(jQuery(this));
        });
    } else if (this.element.hasClass('ajax-feedback')) {
        this.sendField(this.element);
    } else if ((this.element.closest('form.ajax.ajax-feedback').length > 0) && (this.element.is('input') || this.element.is('select') || this.element.is('textarea'))) {
        this.sendField(this.element);
    }
    if (this.element.hasClass('ajax-override')) {
        this.overrideForm(this.element);
    }
    if (this.element.hasClass('ajax-send')) {
        this.sendFormByLink(this.element);
    }
    if (this.element.hasClass('ajax-override-submit')) {
        this.sendNormalFormByLink(this.element);
    }
}

AjaxApi.prototype.sendForm = function(form) {
    var self = this;
    form.on('submit', function(event) {
        if (!form.hasClass('override-submit')) {
            event.preventDefault();
            var url = form.attr('action');
            var data = form.serializeArray();
            var type = form.data('page-type');

            // set special param if auto-complete is active
            if (form.find('input[type=search]').is(':focus')) {
                data.unshift({'name': 'searchautocomplete', 'value': 1});
            }
            self.ajaxRequest(url, data, type);
        }
    });
};

AjaxApi.prototype.sendLink = function(link) {
    var self = this;
    link.on('click', function(event) {
        event.preventDefault();
        var url = link.attr('href');
        var data = [];
        var type = link.data('page-type');
        if (! link.hasClass('disabled')) {
            self.ajaxRequest(url, data, type);

            if (link.hasClass('next-page')) {
                jQuery(document).trigger('next-page', link);
            }
        }
    });
};

AjaxApi.prototype.sendField = function(field) {
    if (!field.hasClass('feedback-field')) {
        field.addClass('feedback-field');

        var self = this;
        var url;
        var data;
        var type;

        if (typeof field.attr('data-feedback-url') !== 'undefined') {
            url = field.data('feedback-url');
            type = field.data('page-type');
        } else {
            url = field.closest('form.ajax').data('feedback-url');
            type = field.closest('form.ajax').data('page-type');
        }
        if (field.is(':radio') || field.is(':checkbox') || field.is('select')) {
            field.on('change', function() {
                data = field.serializeArray();
                self.ajaxRequest(url, data, type);
            });
        } else {
            field.on('blur', function() {
                data = field.serializeArray();
                self.ajaxRequest(url, data, type);
            });
        }
    }
};

AjaxApi.prototype.overrideForm = function(element) {
    var self = this;
    var data;
    var type;
    if (element.prop('tagName') === 'A') {
        element.on('click', function(event) {
            event.preventDefault();
            var form = element.closest('form.ajax');
            var url = element.attr('href');
            type = form.data('page-type');
            data = form.serializeArray();
            self.ajaxRequest(url, data, type);
        });
    } else if (element.prop('tagName') === 'SELECT') {
        element.on('change', function() {
            var select = jQuery(this);
            if (select.val()) {
                var form = select.closest('form.ajax');
                var url = select.val();
                type = form.data('page-type');
                data = form.serializeArray();
                self.ajaxRequest(url, data, type);
            }
        });
    }
};

AjaxApi.prototype.sendFormByLink = function(link) {
    var self = this;
    link.on('click', function(event) {
        event.preventDefault();
        var form = link.closest('form.ajax');
        var url = form.attr('action');
        var data = form.serializeArray();
        var type = form.data('page-type');

        self.ajaxRequest(url, data, type);
    });
};

AjaxApi.prototype.sendNormalFormByLink = function(link) {
    var self = this;
    link.on('click', function(event) {
        event.preventDefault();
        var form = link.closest('form.ajax');
        form.attr('action', link.attr('href')).addClass('override-submit');
        form.submit();
    });
};

AjaxApi.prototype.ajaxRequest = function(url, data, type) {
    var self = this;
    if (! type)
        type = 1433770902;
    data.unshift({'name': 'type', 'value': type});
    jQuery.ajax({
        'method': 'get',
        'url': url,
        'data': jQuery.param(data),
        'dataType': 'json',
        //'username': 'rkw-kompetenz',
        //'password': 'nvFHKGG6578zfasfF',
        'complete': function (response) {
            // console.log(response);
            try {
                response = JSON.parse(response.responseText);
                self.parseContent(response);
            } catch (error) {
                console.log(error.message);
            }
        }
    });
};

AjaxApi.prototype.parseContent = function(json) {
    var self = this;
    var parent;
    for (var property in json) {
        if (property === 'message') {
            var messageObject = json[property];
            for (parent in messageObject) {
                var messageContent = self.getMessageBox(messageObject[parent].message, messageObject[parent].type, parent);
                self.appendContent(parent, messageContent);
            }
        } else if (property === 'data') {
            if (this.element.prop('tagName') === 'FORM') {
                jQuery.data(this.element[0], 'dataObject', json[property]);
                this.element.trigger('ajax-data-object');
            } else if (this.element.closest('form.ajax').length) {
                jQuery.data(this.element.closest('form.ajax')[0], 'dataObject', json[property]);
                this.element.closest('form.ajax').trigger('ajax-data-object');
            }
        } else if (property === 'html') {
            var htmlObject = json[property];
            for (parent in htmlObject) {
                for (var method in htmlObject[parent]) {
                    if (method === 'append') {
                        self.appendContent(parent, htmlObject[parent][method]);
                    } else if (method === 'prepend') {
                        self.prependContent(parent, htmlObject[parent][method]);
                    } else if (method === 'replace') {
                        self.replaceContent(parent, htmlObject[parent][method]);
                    }
                }
            }
        } else if (
            (property === 'javaScriptBefore')
            || (property === 'javaScriptAfter')
        ) {
            try {
                eval(json[property]);
            } catch (error) {
                console.log(error.message);
            }
        }
    }

    jQuery(document).on('next-page', function(event, element) {
        jQuery.data(element, 'json-ajax', json);
    });
};

AjaxApi.prototype.getMessageBox = function(text, type, parent) {
    var box = jQuery('<div class="message-box" data-for="#' + parent + '">' + text + '</div>');
    if (type === 1) {
        box.addClass('success');
    } else if (type === 2) {
        box.addClass('hint');
    } else if (type === 99) {
        box.addClass('error');
    }

    return box;
};

AjaxApi.prototype.appendContent = function(element, content) {
    try {
        var newContent = jQuery(content).appendTo(jQuery('#' + element));
        jQuery('#' + element).find('.box-loading').remove();
        jQuery(document).trigger('rkw-ajax-api-content-changed', newContent.parent());
    } catch (error) {}
};

AjaxApi.prototype.prependContent = function(element, content) {
    try {
        var newContent = jQuery(content).prependTo(jQuery('#' + element));
        jQuery('#' + element).find('.box-loading').remove();
        jQuery(document).trigger('rkw-ajax-api-content-changed', newContent.parent());

    } catch (error) {}
};

AjaxApi.prototype.replaceContent = function(element, content) {
    try {
        if (jQuery(content).length > 0) {
            var newContent = jQuery(content).appendTo(jQuery('#' + element).empty());
            jQuery(document).trigger('rkw-ajax-api-content-changed', newContent);
        } else {
            jQuery('#' + element).empty().append(content);
        }
    } catch (error) {}
};

jQuery.fn.ajaxApi = function() {
    jQuery(this).each(function() {
        new AjaxApi(jQuery(this));
    });
};

module.exports = AjaxApi;

},{}]},{},[1])
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJzY3JpcHRzL2FqYXhBcGkuanMiLCJzY3JpcHRzL3dpZGdldHMvYWpheC1hcGkuanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7QUNBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDekZBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbiBlKHQsbixyKXtmdW5jdGlvbiBzKG8sdSl7aWYoIW5bb10pe2lmKCF0W29dKXt2YXIgYT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2lmKCF1JiZhKXJldHVybiBhKG8sITApO2lmKGkpcmV0dXJuIGkobywhMCk7dmFyIGY9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitvK1wiJ1wiKTt0aHJvdyBmLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsZn12YXIgbD1uW29dPXtleHBvcnRzOnt9fTt0W29dWzBdLmNhbGwobC5leHBvcnRzLGZ1bmN0aW9uKGUpe3ZhciBuPXRbb11bMV1bZV07cmV0dXJuIHMobj9uOmUpfSxsLGwuZXhwb3J0cyxlLHQsbixyKX1yZXR1cm4gbltvXS5leHBvcnRzfXZhciBpPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7Zm9yKHZhciBvPTA7bzxyLmxlbmd0aDtvKyspcyhyW29dKTtyZXR1cm4gc30pIiwiOyhmdW5jdGlvbiAoKSB7XG4gICAgJ3VzZSBzdHJpY3QnO1xuXG4gICAgLy8gd2luZG93LiQgPSB3aW5kb3cualF1ZXJ5ID0gcmVxdWlyZSgnanF1ZXJ5Jyk7XG4gICAgdmFyIGFqYXhBcGkgPSByZXF1aXJlKCcuL3dpZGdldHMvYWpheC1hcGknKTtcblxuICAgIGZ1bmN0aW9uIFJrd0FqYXgoKSB7XG4gICAgICAgIHZhciBzZWxmID0gdGhpcztcblxuICAgICAgICBqUXVlcnkod2luZG93KS5vbigncmt3LWFqYXgtYXBpLWNvbnRlbnQtY2hhbmdlZCcsIHRoaXMub25BamF4Q29udGVudENoYW5nZWQuYmluZCh0aGlzKSk7XG4gICAgICAgIHRoaXMucGFyc2VDb250ZW50KGRvY3VtZW50LmJvZHkpO1xuICAgIH1cblxuXG4gICAgLyogRXZlcnl0aGluZyBpbiBoZXJlIHdpbGwgYmUgZXhlY3V0ZWQgYXMgc29vbiBhcyBuZXcgY29udGVudCBpcyBhZGRlZCB2aWEgQUpBWCovXG4gICAgUmt3QWpheC5wcm90b3R5cGUucGFyc2VDb250ZW50ID0gZnVuY3Rpb24ocm9vdCkge1xuICAgICAgICBmdW5jdGlvbiBmaW5kKHNlbGVjdG9yKSB7XG4gICAgICAgICAgICByZXR1cm4galF1ZXJ5KHJvb3QpLmlzKHNlbGVjdG9yKSA/IGpRdWVyeShyb290KSA6IGpRdWVyeShyb290KS5maW5kKCcqJykuZmlsdGVyKHNlbGVjdG9yKTtcbiAgICAgICAgfVxuXG4gICAgICAgIHZhciBzZWxmID0gdGhpcztcblxuICAgICAgICAvKiBBSkFYIGZ1bmN0aW9uYWxpdHkgZm9yIG5ldyBlbGVtZW50cyAqL1xuICAgICAgICBmaW5kKCcuYWpheCwgLmFqYXgtZmVlZGJhY2ssIC5hamF4IC5hamF4LW92ZXJyaWRlLCAuYWpheCAuYWpheC1zZW5kLCAuYWpheCAuYWpheC1vdmVycmlkZS1zdWJtaXQsIC5hamF4IGlucHV0LCAuYWpheCB0ZXh0YXJlYSwgLmFqYXggc2VsZWN0JykuYWpheEFwaSgpO1xuXG4gICAgICAgIGZpbmQoJy5uZXh0LXBhZ2VbaHJlZl0nKS5lYWNoKGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgdmFyIGxpbmsgPSBqUXVlcnkodGhpcyk7XG4gICAgICAgICAgICB2YXIgYm94ZXNDb250YWluZXIgPSBsaW5rLnBhcmVudCgpLnBhcmVudCgpO1xuICAgICAgICAgICAgYm94ZXNDb250YWluZXIuZmluZCgnLmJveC1sb2FkaW5nJykucmVtb3ZlKCk7XG5cbiAgICAgICAgICAgIGlmIChsaW5rLmhhc0NsYXNzKCdhdXRvbG9hZCcpKVxuICAgICAgICAgICAgICAgIGpRdWVyeSh3aW5kb3cpLnNjcm9sbChjaGVja0JvdHRvbSk7XG5cbiAgICAgICAgICAgIGxpbmsuY2xpY2soZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICAgICAgaWYgKCFqUXVlcnkodGhpcykuaGFzQ2xhc3MoJ2Rpc2FibGVkJykpIHtcblxuICAgICAgICAgICAgICAgICAgICBsaW5rLnJlbW92ZUF0dHIoJ2hyZWYnKS5hZGRDbGFzcygnZGlzYWJsZWQnKTtcbiAgICAgICAgICAgICAgICAgICAgYm94ZXNDb250YWluZXIuYXBwZW5kKCc8ZGl2IGNsYXNzPVwiYm94LWxvYWRpbmdcIi8+Jyk7XG4gICAgICAgICAgICAgICAgICAgIGpRdWVyeSh3aW5kb3cpLnNjcm9sbFRvcChqUXVlcnkod2luZG93KS5zY3JvbGxUb3AoKSAtIDIpO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pO1xuXG4gICAgICAgICAgICBmdW5jdGlvbiBjaGVja0JvdHRvbSgpIHtcbiAgICAgICAgICAgICAgICBpZiAoalF1ZXJ5KHdpbmRvdykuc2Nyb2xsVG9wKCkgKyBqUXVlcnkod2luZG93KS5oZWlnaHQoKSA+PSBqUXVlcnkoZG9jdW1lbnQpLmhlaWdodCgpIC0gMTAwKSB7XG4gICAgICAgICAgICAgICAgICAgIGlmICghbGluay5oYXNDbGFzcygnZGlzYWJsZWQnKSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgbGluay5jbGljaygpO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcblxuICAgICAgICAvKiBkZWxldGUgZGlzYWJsZWQgbW9yZSBib3ggaWYgdGhpcyBpcyB0aGUgb25seSBlbnRyeSBpbiB0aGUgYm94IGxpc3QgKi9cbiAgICAgICAgZmluZCgnZGl2LmJveC13cmFwcGVyIC5ib3guZGlzYWJsZWQ6Zmlyc3QtY2hpbGQnKS5wYXJlbnQoKS5yZW1vdmUoKTtcbiAgICAgICAgZmluZCgnZGl2IC5ib3gucHVibGljYXRpb24uZGlzYWJsZWQ6Zmlyc3QtY2hpbGQnKS5wYXJlbnQoKS5yZW1vdmUoKTtcblxuICAgICAgICAvKiBmdW5jdGlvbmFsaXR5IGZvciBzZWFyY2ggZmlsdGVycyAqL1xuICAgICAgICB2YXIgZmlsdGVycyA9IGZpbmQoJ2lucHV0LHNlbGVjdCcpO1xuICAgICAgICBmaWx0ZXJzLm9uKCdjaGFuZ2UnLCB0aGlzLnNlbmRTZWFyY2hGb3JtKTtcblxuICAgIH07XG4gICAgXG5cbiAgICBSa3dBamF4LnByb3RvdHlwZS5vbkFqYXhDb250ZW50Q2hhbmdlZCA9IGZ1bmN0aW9uKGV2ZW50LCBjb250ZW50KSB7XG4gICAgICAgIHRoaXMucGFyc2VDb250ZW50KGNvbnRlbnQpO1xuXG4gICAgICAgIC8qIHRyaWdnZXIgZm9yIGV4dGVybmFsIHN0dWZmICovXG4gICAgICAgIGpRdWVyeShkb2N1bWVudCkudHJpZ2dlcigncmt3LWFqYXgtYXBpLWNvbnRlbnQtbG9hZGVkJywgY29udGVudCk7XG5cbiAgICB9O1xuXG5cbiAgICBSa3dBamF4LnByb3RvdHlwZS5zZW5kU2VhcmNoRm9ybSAgPSBmdW5jdGlvbihldmVudCkge1xuICAgICAgICB2YXIgc2VhcmNoRm9ybSA9ICQodGhpcykuY2xvc2VzdCgnZm9ybS5hamF4Jyk7XG4gICAgICAgIGlmIChzZWFyY2hGb3JtLmxlbmd0aCkge1xuICAgICAgICAgICAgdmFyIHNlYXJjaFJlc3VsdCA9IHNlYXJjaEZvcm0uc2libGluZ3MoJy5zZWFyY2gtcmVzdWx0LXNlY3Rpb24nKS5maXJzdCgpO1xuICAgICAgICAgICAgaWYgKHNlYXJjaFJlc3VsdC5maW5kKCcubG9hZGluZy1pbmRpY2F0b3InKS5sZW5ndGggPT09IDApIHtcbiAgICAgICAgICAgICAgICBzZWFyY2hSZXN1bHQuYXBwZW5kKCc8ZGl2IGNsYXNzPVwibG9hZGluZy1pbmRpY2F0b3JcIi8+Jyk7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIC8qIHRyaWdnZXIgZm9yIGV4dGVybmFsIHN0dWZmICovXG4gICAgICAgICAgICBqUXVlcnkoZG9jdW1lbnQpLnRyaWdnZXIoJ3Jrdy1hamF4LWFwaS1zZWFyY2gtZm9ybScsIHNlYXJjaEZvcm0pO1xuXG4gICAgICAgICAgICBzZWFyY2hGb3JtLnN1Ym1pdCgpO1xuICAgICAgICB9XG4gICAgfTtcblxuICAgIG5ldyBSa3dBamF4KCk7XG5cbn0oKSk7XG4iLCJmdW5jdGlvbiBBamF4QXBpKGVsZW1lbnQpIHtcbiAgICB2YXIgc2VsZiA9IHRoaXM7XG4gICAgdGhpcy5lbGVtZW50ID0gZWxlbWVudDtcbiAgICB0aGlzLmVsZW1lbnRUeXBlID0gZWxlbWVudC5wcm9wKCd0YWdOYW1lJyk7XG5cbiAgICBpZiAodGhpcy5lbGVtZW50VHlwZSA9PT0gJ0ZPUk0nICYmIHRoaXMuZWxlbWVudC5oYXNDbGFzcygnYWpheCcpKSB7XG4gICAgICAgIHRoaXMuc2VuZEZvcm0odGhpcy5lbGVtZW50KTtcbiAgICB9IGVsc2UgaWYgKHRoaXMuZWxlbWVudFR5cGUgPT09ICdBJyAmJiB0aGlzLmVsZW1lbnQuaGFzQ2xhc3MoJ2FqYXgnKSkge1xuICAgICAgICB0aGlzLnNlbmRMaW5rKHRoaXMuZWxlbWVudCk7XG4gICAgfVxuICAgIGlmICh0aGlzLmVsZW1lbnQuaGFzQ2xhc3MoJ2FqYXgtZmVlZGJhY2snKSAmJiB0aGlzLmVsZW1lbnQuaGFzQ2xhc3MoJ2FqYXgnKSkge1xuICAgICAgICB0aGlzLmVsZW1lbnQuZmluZCgnaW5wdXQsIHRleHRhcmVhLCBzZWxlY3QnKS5lYWNoKGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgc2VsZi5zZW5kRmllbGQoalF1ZXJ5KHRoaXMpKTtcbiAgICAgICAgfSk7XG4gICAgfSBlbHNlIGlmICh0aGlzLmVsZW1lbnQuaGFzQ2xhc3MoJ2FqYXgtZmVlZGJhY2snKSkge1xuICAgICAgICB0aGlzLnNlbmRGaWVsZCh0aGlzLmVsZW1lbnQpO1xuICAgIH0gZWxzZSBpZiAoKHRoaXMuZWxlbWVudC5jbG9zZXN0KCdmb3JtLmFqYXguYWpheC1mZWVkYmFjaycpLmxlbmd0aCA+IDApICYmICh0aGlzLmVsZW1lbnQuaXMoJ2lucHV0JykgfHwgdGhpcy5lbGVtZW50LmlzKCdzZWxlY3QnKSB8fCB0aGlzLmVsZW1lbnQuaXMoJ3RleHRhcmVhJykpKSB7XG4gICAgICAgIHRoaXMuc2VuZEZpZWxkKHRoaXMuZWxlbWVudCk7XG4gICAgfVxuICAgIGlmICh0aGlzLmVsZW1lbnQuaGFzQ2xhc3MoJ2FqYXgtb3ZlcnJpZGUnKSkge1xuICAgICAgICB0aGlzLm92ZXJyaWRlRm9ybSh0aGlzLmVsZW1lbnQpO1xuICAgIH1cbiAgICBpZiAodGhpcy5lbGVtZW50Lmhhc0NsYXNzKCdhamF4LXNlbmQnKSkge1xuICAgICAgICB0aGlzLnNlbmRGb3JtQnlMaW5rKHRoaXMuZWxlbWVudCk7XG4gICAgfVxuICAgIGlmICh0aGlzLmVsZW1lbnQuaGFzQ2xhc3MoJ2FqYXgtb3ZlcnJpZGUtc3VibWl0JykpIHtcbiAgICAgICAgdGhpcy5zZW5kTm9ybWFsRm9ybUJ5TGluayh0aGlzLmVsZW1lbnQpO1xuICAgIH1cbn1cblxuQWpheEFwaS5wcm90b3R5cGUuc2VuZEZvcm0gPSBmdW5jdGlvbihmb3JtKSB7XG4gICAgdmFyIHNlbGYgPSB0aGlzO1xuICAgIGZvcm0ub24oJ3N1Ym1pdCcsIGZ1bmN0aW9uKGV2ZW50KSB7XG4gICAgICAgIGlmICghZm9ybS5oYXNDbGFzcygnb3ZlcnJpZGUtc3VibWl0JykpIHtcbiAgICAgICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgICAgICB2YXIgdXJsID0gZm9ybS5hdHRyKCdhY3Rpb24nKTtcbiAgICAgICAgICAgIHZhciBkYXRhID0gZm9ybS5zZXJpYWxpemVBcnJheSgpO1xuICAgICAgICAgICAgdmFyIHR5cGUgPSBmb3JtLmRhdGEoJ3BhZ2UtdHlwZScpO1xuXG4gICAgICAgICAgICAvLyBzZXQgc3BlY2lhbCBwYXJhbSBpZiBhdXRvLWNvbXBsZXRlIGlzIGFjdGl2ZVxuICAgICAgICAgICAgaWYgKGZvcm0uZmluZCgnaW5wdXRbdHlwZT1zZWFyY2hdJykuaXMoJzpmb2N1cycpKSB7XG4gICAgICAgICAgICAgICAgZGF0YS51bnNoaWZ0KHsnbmFtZSc6ICdzZWFyY2hhdXRvY29tcGxldGUnLCAndmFsdWUnOiAxfSk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBzZWxmLmFqYXhSZXF1ZXN0KHVybCwgZGF0YSwgdHlwZSk7XG4gICAgICAgIH1cbiAgICB9KTtcbn07XG5cbkFqYXhBcGkucHJvdG90eXBlLnNlbmRMaW5rID0gZnVuY3Rpb24obGluaykge1xuICAgIHZhciBzZWxmID0gdGhpcztcbiAgICBsaW5rLm9uKCdjbGljaycsIGZ1bmN0aW9uKGV2ZW50KSB7XG4gICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgIHZhciB1cmwgPSBsaW5rLmF0dHIoJ2hyZWYnKTtcbiAgICAgICAgdmFyIGRhdGEgPSBbXTtcbiAgICAgICAgdmFyIHR5cGUgPSBsaW5rLmRhdGEoJ3BhZ2UtdHlwZScpO1xuICAgICAgICBpZiAoISBsaW5rLmhhc0NsYXNzKCdkaXNhYmxlZCcpKSB7XG4gICAgICAgICAgICBzZWxmLmFqYXhSZXF1ZXN0KHVybCwgZGF0YSwgdHlwZSk7XG5cbiAgICAgICAgICAgIGlmIChsaW5rLmhhc0NsYXNzKCduZXh0LXBhZ2UnKSkge1xuICAgICAgICAgICAgICAgIGpRdWVyeShkb2N1bWVudCkudHJpZ2dlcignbmV4dC1wYWdlJywgbGluayk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICB9KTtcbn07XG5cbkFqYXhBcGkucHJvdG90eXBlLnNlbmRGaWVsZCA9IGZ1bmN0aW9uKGZpZWxkKSB7XG4gICAgaWYgKCFmaWVsZC5oYXNDbGFzcygnZmVlZGJhY2stZmllbGQnKSkge1xuICAgICAgICBmaWVsZC5hZGRDbGFzcygnZmVlZGJhY2stZmllbGQnKTtcblxuICAgICAgICB2YXIgc2VsZiA9IHRoaXM7XG4gICAgICAgIHZhciB1cmw7XG4gICAgICAgIHZhciBkYXRhO1xuICAgICAgICB2YXIgdHlwZTtcblxuICAgICAgICBpZiAodHlwZW9mIGZpZWxkLmF0dHIoJ2RhdGEtZmVlZGJhY2stdXJsJykgIT09ICd1bmRlZmluZWQnKSB7XG4gICAgICAgICAgICB1cmwgPSBmaWVsZC5kYXRhKCdmZWVkYmFjay11cmwnKTtcbiAgICAgICAgICAgIHR5cGUgPSBmaWVsZC5kYXRhKCdwYWdlLXR5cGUnKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHVybCA9IGZpZWxkLmNsb3Nlc3QoJ2Zvcm0uYWpheCcpLmRhdGEoJ2ZlZWRiYWNrLXVybCcpO1xuICAgICAgICAgICAgdHlwZSA9IGZpZWxkLmNsb3Nlc3QoJ2Zvcm0uYWpheCcpLmRhdGEoJ3BhZ2UtdHlwZScpO1xuICAgICAgICB9XG4gICAgICAgIGlmIChmaWVsZC5pcygnOnJhZGlvJykgfHwgZmllbGQuaXMoJzpjaGVja2JveCcpIHx8IGZpZWxkLmlzKCdzZWxlY3QnKSkge1xuICAgICAgICAgICAgZmllbGQub24oJ2NoYW5nZScsIGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgIGRhdGEgPSBmaWVsZC5zZXJpYWxpemVBcnJheSgpO1xuICAgICAgICAgICAgICAgIHNlbGYuYWpheFJlcXVlc3QodXJsLCBkYXRhLCB0eXBlKTtcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgZmllbGQub24oJ2JsdXInLCBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgICAgICBkYXRhID0gZmllbGQuc2VyaWFsaXplQXJyYXkoKTtcbiAgICAgICAgICAgICAgICBzZWxmLmFqYXhSZXF1ZXN0KHVybCwgZGF0YSwgdHlwZSk7XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgIH1cbn07XG5cbkFqYXhBcGkucHJvdG90eXBlLm92ZXJyaWRlRm9ybSA9IGZ1bmN0aW9uKGVsZW1lbnQpIHtcbiAgICB2YXIgc2VsZiA9IHRoaXM7XG4gICAgdmFyIGRhdGE7XG4gICAgdmFyIHR5cGU7XG4gICAgaWYgKGVsZW1lbnQucHJvcCgndGFnTmFtZScpID09PSAnQScpIHtcbiAgICAgICAgZWxlbWVudC5vbignY2xpY2snLCBmdW5jdGlvbihldmVudCkge1xuICAgICAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgICAgIHZhciBmb3JtID0gZWxlbWVudC5jbG9zZXN0KCdmb3JtLmFqYXgnKTtcbiAgICAgICAgICAgIHZhciB1cmwgPSBlbGVtZW50LmF0dHIoJ2hyZWYnKTtcbiAgICAgICAgICAgIHR5cGUgPSBmb3JtLmRhdGEoJ3BhZ2UtdHlwZScpO1xuICAgICAgICAgICAgZGF0YSA9IGZvcm0uc2VyaWFsaXplQXJyYXkoKTtcbiAgICAgICAgICAgIHNlbGYuYWpheFJlcXVlc3QodXJsLCBkYXRhLCB0eXBlKTtcbiAgICAgICAgfSk7XG4gICAgfSBlbHNlIGlmIChlbGVtZW50LnByb3AoJ3RhZ05hbWUnKSA9PT0gJ1NFTEVDVCcpIHtcbiAgICAgICAgZWxlbWVudC5vbignY2hhbmdlJywgZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICB2YXIgc2VsZWN0ID0galF1ZXJ5KHRoaXMpO1xuICAgICAgICAgICAgaWYgKHNlbGVjdC52YWwoKSkge1xuICAgICAgICAgICAgICAgIHZhciBmb3JtID0gc2VsZWN0LmNsb3Nlc3QoJ2Zvcm0uYWpheCcpO1xuICAgICAgICAgICAgICAgIHZhciB1cmwgPSBzZWxlY3QudmFsKCk7XG4gICAgICAgICAgICAgICAgdHlwZSA9IGZvcm0uZGF0YSgncGFnZS10eXBlJyk7XG4gICAgICAgICAgICAgICAgZGF0YSA9IGZvcm0uc2VyaWFsaXplQXJyYXkoKTtcbiAgICAgICAgICAgICAgICBzZWxmLmFqYXhSZXF1ZXN0KHVybCwgZGF0YSwgdHlwZSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgIH1cbn07XG5cbkFqYXhBcGkucHJvdG90eXBlLnNlbmRGb3JtQnlMaW5rID0gZnVuY3Rpb24obGluaykge1xuICAgIHZhciBzZWxmID0gdGhpcztcbiAgICBsaW5rLm9uKCdjbGljaycsIGZ1bmN0aW9uKGV2ZW50KSB7XG4gICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgIHZhciBmb3JtID0gbGluay5jbG9zZXN0KCdmb3JtLmFqYXgnKTtcbiAgICAgICAgdmFyIHVybCA9IGZvcm0uYXR0cignYWN0aW9uJyk7XG4gICAgICAgIHZhciBkYXRhID0gZm9ybS5zZXJpYWxpemVBcnJheSgpO1xuICAgICAgICB2YXIgdHlwZSA9IGZvcm0uZGF0YSgncGFnZS10eXBlJyk7XG5cbiAgICAgICAgc2VsZi5hamF4UmVxdWVzdCh1cmwsIGRhdGEsIHR5cGUpO1xuICAgIH0pO1xufTtcblxuQWpheEFwaS5wcm90b3R5cGUuc2VuZE5vcm1hbEZvcm1CeUxpbmsgPSBmdW5jdGlvbihsaW5rKSB7XG4gICAgdmFyIHNlbGYgPSB0aGlzO1xuICAgIGxpbmsub24oJ2NsaWNrJywgZnVuY3Rpb24oZXZlbnQpIHtcbiAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgdmFyIGZvcm0gPSBsaW5rLmNsb3Nlc3QoJ2Zvcm0uYWpheCcpO1xuICAgICAgICBmb3JtLmF0dHIoJ2FjdGlvbicsIGxpbmsuYXR0cignaHJlZicpKS5hZGRDbGFzcygnb3ZlcnJpZGUtc3VibWl0Jyk7XG4gICAgICAgIGZvcm0uc3VibWl0KCk7XG4gICAgfSk7XG59O1xuXG5BamF4QXBpLnByb3RvdHlwZS5hamF4UmVxdWVzdCA9IGZ1bmN0aW9uKHVybCwgZGF0YSwgdHlwZSkge1xuICAgIHZhciBzZWxmID0gdGhpcztcbiAgICBpZiAoISB0eXBlKVxuICAgICAgICB0eXBlID0gMTQzMzc3MDkwMjtcbiAgICBkYXRhLnVuc2hpZnQoeyduYW1lJzogJ3R5cGUnLCAndmFsdWUnOiB0eXBlfSk7XG4gICAgalF1ZXJ5LmFqYXgoe1xuICAgICAgICAnbWV0aG9kJzogJ2dldCcsXG4gICAgICAgICd1cmwnOiB1cmwsXG4gICAgICAgICdkYXRhJzogalF1ZXJ5LnBhcmFtKGRhdGEpLFxuICAgICAgICAnZGF0YVR5cGUnOiAnanNvbicsXG4gICAgICAgIC8vJ3VzZXJuYW1lJzogJ3Jrdy1rb21wZXRlbnonLFxuICAgICAgICAvLydwYXNzd29yZCc6ICdudkZIS0dHNjU3OHpmYXNmRicsXG4gICAgICAgICdjb21wbGV0ZSc6IGZ1bmN0aW9uIChyZXNwb25zZSkge1xuICAgICAgICAgICAgLy8gY29uc29sZS5sb2cocmVzcG9uc2UpO1xuICAgICAgICAgICAgdHJ5IHtcbiAgICAgICAgICAgICAgICByZXNwb25zZSA9IEpTT04ucGFyc2UocmVzcG9uc2UucmVzcG9uc2VUZXh0KTtcbiAgICAgICAgICAgICAgICBzZWxmLnBhcnNlQ29udGVudChyZXNwb25zZSk7XG4gICAgICAgICAgICB9IGNhdGNoIChlcnJvcikge1xuICAgICAgICAgICAgICAgIGNvbnNvbGUubG9nKGVycm9yLm1lc3NhZ2UpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfSk7XG59O1xuXG5BamF4QXBpLnByb3RvdHlwZS5wYXJzZUNvbnRlbnQgPSBmdW5jdGlvbihqc29uKSB7XG4gICAgdmFyIHNlbGYgPSB0aGlzO1xuICAgIHZhciBwYXJlbnQ7XG4gICAgZm9yICh2YXIgcHJvcGVydHkgaW4ganNvbikge1xuICAgICAgICBpZiAocHJvcGVydHkgPT09ICdtZXNzYWdlJykge1xuICAgICAgICAgICAgdmFyIG1lc3NhZ2VPYmplY3QgPSBqc29uW3Byb3BlcnR5XTtcbiAgICAgICAgICAgIGZvciAocGFyZW50IGluIG1lc3NhZ2VPYmplY3QpIHtcbiAgICAgICAgICAgICAgICB2YXIgbWVzc2FnZUNvbnRlbnQgPSBzZWxmLmdldE1lc3NhZ2VCb3gobWVzc2FnZU9iamVjdFtwYXJlbnRdLm1lc3NhZ2UsIG1lc3NhZ2VPYmplY3RbcGFyZW50XS50eXBlLCBwYXJlbnQpO1xuICAgICAgICAgICAgICAgIHNlbGYuYXBwZW5kQ29udGVudChwYXJlbnQsIG1lc3NhZ2VDb250ZW50KTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSBlbHNlIGlmIChwcm9wZXJ0eSA9PT0gJ2RhdGEnKSB7XG4gICAgICAgICAgICBpZiAodGhpcy5lbGVtZW50LnByb3AoJ3RhZ05hbWUnKSA9PT0gJ0ZPUk0nKSB7XG4gICAgICAgICAgICAgICAgalF1ZXJ5LmRhdGEodGhpcy5lbGVtZW50WzBdLCAnZGF0YU9iamVjdCcsIGpzb25bcHJvcGVydHldKTtcbiAgICAgICAgICAgICAgICB0aGlzLmVsZW1lbnQudHJpZ2dlcignYWpheC1kYXRhLW9iamVjdCcpO1xuICAgICAgICAgICAgfSBlbHNlIGlmICh0aGlzLmVsZW1lbnQuY2xvc2VzdCgnZm9ybS5hamF4JykubGVuZ3RoKSB7XG4gICAgICAgICAgICAgICAgalF1ZXJ5LmRhdGEodGhpcy5lbGVtZW50LmNsb3Nlc3QoJ2Zvcm0uYWpheCcpWzBdLCAnZGF0YU9iamVjdCcsIGpzb25bcHJvcGVydHldKTtcbiAgICAgICAgICAgICAgICB0aGlzLmVsZW1lbnQuY2xvc2VzdCgnZm9ybS5hamF4JykudHJpZ2dlcignYWpheC1kYXRhLW9iamVjdCcpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9IGVsc2UgaWYgKHByb3BlcnR5ID09PSAnaHRtbCcpIHtcbiAgICAgICAgICAgIHZhciBodG1sT2JqZWN0ID0ganNvbltwcm9wZXJ0eV07XG4gICAgICAgICAgICBmb3IgKHBhcmVudCBpbiBodG1sT2JqZWN0KSB7XG4gICAgICAgICAgICAgICAgZm9yICh2YXIgbWV0aG9kIGluIGh0bWxPYmplY3RbcGFyZW50XSkge1xuICAgICAgICAgICAgICAgICAgICBpZiAobWV0aG9kID09PSAnYXBwZW5kJykge1xuICAgICAgICAgICAgICAgICAgICAgICAgc2VsZi5hcHBlbmRDb250ZW50KHBhcmVudCwgaHRtbE9iamVjdFtwYXJlbnRdW21ldGhvZF0pO1xuICAgICAgICAgICAgICAgICAgICB9IGVsc2UgaWYgKG1ldGhvZCA9PT0gJ3ByZXBlbmQnKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBzZWxmLnByZXBlbmRDb250ZW50KHBhcmVudCwgaHRtbE9iamVjdFtwYXJlbnRdW21ldGhvZF0pO1xuICAgICAgICAgICAgICAgICAgICB9IGVsc2UgaWYgKG1ldGhvZCA9PT0gJ3JlcGxhY2UnKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBzZWxmLnJlcGxhY2VDb250ZW50KHBhcmVudCwgaHRtbE9iamVjdFtwYXJlbnRdW21ldGhvZF0pO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICB9IGVsc2UgaWYgKFxuICAgICAgICAgICAgKHByb3BlcnR5ID09PSAnamF2YVNjcmlwdEJlZm9yZScpXG4gICAgICAgICAgICB8fCAocHJvcGVydHkgPT09ICdqYXZhU2NyaXB0QWZ0ZXInKVxuICAgICAgICApIHtcbiAgICAgICAgICAgIHRyeSB7XG4gICAgICAgICAgICAgICAgZXZhbChqc29uW3Byb3BlcnR5XSk7XG4gICAgICAgICAgICB9IGNhdGNoIChlcnJvcikge1xuICAgICAgICAgICAgICAgIGNvbnNvbGUubG9nKGVycm9yLm1lc3NhZ2UpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfVxuXG4gICAgalF1ZXJ5KGRvY3VtZW50KS5vbignbmV4dC1wYWdlJywgZnVuY3Rpb24oZXZlbnQsIGVsZW1lbnQpIHtcbiAgICAgICAgalF1ZXJ5LmRhdGEoZWxlbWVudCwgJ2pzb24tYWpheCcsIGpzb24pO1xuICAgIH0pO1xufTtcblxuQWpheEFwaS5wcm90b3R5cGUuZ2V0TWVzc2FnZUJveCA9IGZ1bmN0aW9uKHRleHQsIHR5cGUsIHBhcmVudCkge1xuICAgIHZhciBib3ggPSBqUXVlcnkoJzxkaXYgY2xhc3M9XCJtZXNzYWdlLWJveFwiIGRhdGEtZm9yPVwiIycgKyBwYXJlbnQgKyAnXCI+JyArIHRleHQgKyAnPC9kaXY+Jyk7XG4gICAgaWYgKHR5cGUgPT09IDEpIHtcbiAgICAgICAgYm94LmFkZENsYXNzKCdzdWNjZXNzJyk7XG4gICAgfSBlbHNlIGlmICh0eXBlID09PSAyKSB7XG4gICAgICAgIGJveC5hZGRDbGFzcygnaGludCcpO1xuICAgIH0gZWxzZSBpZiAodHlwZSA9PT0gOTkpIHtcbiAgICAgICAgYm94LmFkZENsYXNzKCdlcnJvcicpO1xuICAgIH1cblxuICAgIHJldHVybiBib3g7XG59O1xuXG5BamF4QXBpLnByb3RvdHlwZS5hcHBlbmRDb250ZW50ID0gZnVuY3Rpb24oZWxlbWVudCwgY29udGVudCkge1xuICAgIHRyeSB7XG4gICAgICAgIHZhciBuZXdDb250ZW50ID0galF1ZXJ5KGNvbnRlbnQpLmFwcGVuZFRvKGpRdWVyeSgnIycgKyBlbGVtZW50KSk7XG4gICAgICAgIGpRdWVyeSgnIycgKyBlbGVtZW50KS5maW5kKCcuYm94LWxvYWRpbmcnKS5yZW1vdmUoKTtcbiAgICAgICAgalF1ZXJ5KGRvY3VtZW50KS50cmlnZ2VyKCdya3ctYWpheC1hcGktY29udGVudC1jaGFuZ2VkJywgbmV3Q29udGVudC5wYXJlbnQoKSk7XG4gICAgfSBjYXRjaCAoZXJyb3IpIHt9XG59O1xuXG5BamF4QXBpLnByb3RvdHlwZS5wcmVwZW5kQ29udGVudCA9IGZ1bmN0aW9uKGVsZW1lbnQsIGNvbnRlbnQpIHtcbiAgICB0cnkge1xuICAgICAgICB2YXIgbmV3Q29udGVudCA9IGpRdWVyeShjb250ZW50KS5wcmVwZW5kVG8oalF1ZXJ5KCcjJyArIGVsZW1lbnQpKTtcbiAgICAgICAgalF1ZXJ5KCcjJyArIGVsZW1lbnQpLmZpbmQoJy5ib3gtbG9hZGluZycpLnJlbW92ZSgpO1xuICAgICAgICBqUXVlcnkoZG9jdW1lbnQpLnRyaWdnZXIoJ3Jrdy1hamF4LWFwaS1jb250ZW50LWNoYW5nZWQnLCBuZXdDb250ZW50LnBhcmVudCgpKTtcblxuICAgIH0gY2F0Y2ggKGVycm9yKSB7fVxufTtcblxuQWpheEFwaS5wcm90b3R5cGUucmVwbGFjZUNvbnRlbnQgPSBmdW5jdGlvbihlbGVtZW50LCBjb250ZW50KSB7XG4gICAgdHJ5IHtcbiAgICAgICAgaWYgKGpRdWVyeShjb250ZW50KS5sZW5ndGggPiAwKSB7XG4gICAgICAgICAgICB2YXIgbmV3Q29udGVudCA9IGpRdWVyeShjb250ZW50KS5hcHBlbmRUbyhqUXVlcnkoJyMnICsgZWxlbWVudCkuZW1wdHkoKSk7XG4gICAgICAgICAgICBqUXVlcnkoZG9jdW1lbnQpLnRyaWdnZXIoJ3Jrdy1hamF4LWFwaS1jb250ZW50LWNoYW5nZWQnLCBuZXdDb250ZW50KTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIGpRdWVyeSgnIycgKyBlbGVtZW50KS5lbXB0eSgpLmFwcGVuZChjb250ZW50KTtcbiAgICAgICAgfVxuICAgIH0gY2F0Y2ggKGVycm9yKSB7fVxufTtcblxualF1ZXJ5LmZuLmFqYXhBcGkgPSBmdW5jdGlvbigpIHtcbiAgICBqUXVlcnkodGhpcykuZWFjaChmdW5jdGlvbigpIHtcbiAgICAgICAgbmV3IEFqYXhBcGkoalF1ZXJ5KHRoaXMpKTtcbiAgICB9KTtcbn07XG5cbm1vZHVsZS5leHBvcnRzID0gQWpheEFwaTtcbiJdfQ==
