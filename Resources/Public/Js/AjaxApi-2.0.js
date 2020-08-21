/*!
 *
 * AJAX API
 *
 * Author: helllicht
 *
 * Last updated: 24.07.2020
 *
 */

(function ($, window, document, undefined) {
  // Create the defaults once
  // Additional options, extending the defaults, can be passed as an object from the initializing call
  var pluginName = "ajaxApi",
    defaults = {
      // form: "form.ajax"
      boxSuccessClass: "success",
      boxHintClass: "hint",
      boxErrorClass: "error"
    };

  // The plugin constructor
  function Plugin(element, options) {
    this.element = element;
    // Merge defaults with passed options
    this.settings = $.extend({}, defaults, options);

    this._defaults = defaults;
    this._name = pluginName;

    this.init();
  }

  $.extend(Plugin.prototype, {
    init: function () {
      // Setup elements and global variables
      this.settings.$el = $(this.element);
      this.settings.elementType = this.settings.$el.prop("tagName");

      if (this.settings.elementType === "FORM") {
        this.settings.formElements = this.settings.$el.find(":input:not(.btn)");
        this.settings.url = this.settings.$el.attr("action");

        if (this.settings.$el.hasClass("ajax-feedback")) {
          this.settings.feedbackUrl = this.settings.$el.attr(
            "data-feedback-url"
          );
        }
      } else if (this.settings.elementType === "A") {
        this.settings.url = this.settings.$el.attr("href");
      }

      this.bindEvents();
    },

    // Bind all event listeners for this plugin
    bindEvents: function () {
      if (this.settings.elementType === "FORM") {
        var self = this;
        if (
          this.settings.$el.hasClass("ajax") &&
          this.settings.$el.hasClass("ajax-feedback")
        ) {
          // AJAX Feedback form
          this.settings.formElements.each(function () {
            $(this).on("change", self.sendField.bind(self));
          });
          this.settings.$el.on("submit", this.sendForm.bind(this));
        } else if (this.settings.$el.hasClass("ajax")) {
          // Regular AJAX form
          this.settings.$el.on("submit", this.sendForm.bind(this));

          if (this.settings.$el.find(".ajax-send")) {
            // AJAX send links
            this.settings.$el.find(".ajax-send").each(function () {
              $(this).on("click", self.sendFormByLink.bind(self));
            });
          }

          if (this.settings.$el.find(".ajax-override-submit")) {
            // AJAX target override form submit
            this.settings.$el.find(".ajax-override-submit").each(function () {
              $(this).on("click", self.sendNormalFormByLink.bind(self));
            });
          }

          if (this.settings.$el.find(".ajax-override")) {
            // AJAX override
            this.settings.$el.find(".ajax-override").each(function () {
              if ($(this).prop("tagName") === "A") {
                $(this).on("click", self.overrideForm.bind(self));
              } else if ($(this).prop("tagName") === "SELECT") {
                $(this).on("change", self.overrideForm.bind(self));
              }
            });
          }
        }
      } else if (this.settings.elementType === "A") {
        this.settings.$el.on("click", this.sendLink.bind(this));
      }
    },

    sendForm: function (e) {
      e.preventDefault();

      if (!this.settings.$el.hasClass("override-submit")) {
        var url = this.settings.url;
        var data = this.getFormValues();
        // Add typeNum to data array
        data.unshift({ name: "type", value: 250 });

        this.ajaxRequest(url, data);
      }
    },

    sendFormByLink: function (e) {
      // Convenience function – just calls sendForm :)
      e.preventDefault();
      this.sendForm(e);
    },

    sendNormalFormByLink: function (e) {
      e.preventDefault();
      var link = $(e.currentTarget);
      var form = link.closest("form.ajax");

      // Override form action
      form.attr("action", link.attr("href")).addClass("override-submit");
      // Unbind AJAX formSubmit event listener
      form.unbind("submit");
      // Submit form regularly
      form.submit();
    },

    sendLink: function (e) {
      e.preventDefault();

      var url = this.settings.url;
      var data = [];
      data.unshift({ name: "type", value: 250 });

      if (!this.settings.$el.hasClass("is-disabled")) {
        this.ajaxRequest(url, data);
      }
    },

    sendField: function (e) {
      var field = $(e.currentTarget);
      var url = this.settings.feedbackUrl;
      var data = field.serializeArray();

      // Add typeNum to data array
      data.unshift({ name: "type", value: 250 });

      this.ajaxRequest(url, data);
    },

    overrideForm: function (e) {
      e.preventDefault();
      var target = $(e.currentTarget);
      var form = this.settings.$el;
      var data = form
        .find("select, input")
        .not(".ajax-override")
        .serializeArray();

      if (target.prop("tagName") === "A") {
        var url = target.attr("href");
      } else if (target.prop("tagName") === "SELECT") {
        var url = target.val();
      }

      // Add typeNum to data array
      data.unshift({ name: "type", value: 250 });

      this.ajaxRequest(url, data);
    },

    getFormValues: function () {
      return this.settings.$el.serializeArray();
    },

    ajaxRequest: function (url, data) {
      var self = this;

      $.ajax({
        method: "get",
        url: url,
        data: $.param(data),
        dataType: "json",
        complete: function (response) {
          try {
            // Successful request
            response = JSON.parse(response.responseText);
            console.log(response);
            self.parseContent(response);
          } catch (error) {
            // Error in request
            console.log(error.message);
          }
        }
      });
    },

    parseContent: function (json) {
      for (var property in json) {
        if (property === "message") {
          var messageObject = json[property];
          for (var parent in messageObject) {
            var messageContent = this.getMessageBox(
              messageObject[parent].message,
              messageObject[parent].type,
              parent
            );
            this.appendContent(parent, messageContent);
          }
        } else if (property === "data") {
          // TBD – more input needed
        } else if (property === "html") {
          var htmlObject = json[property];
          for (var parent in htmlObject) {
            for (var method in htmlObject[parent]) {
              if (method === "append") {
                this.appendContent(parent, htmlObject[parent][method]);
              } else if (method === "prepend") {
                this.prependContent(parent, htmlObject[parent][method]);
              } else if (method === "replace") {
                this.replaceContent(parent, htmlObject[parent][method]);
              }
            }
          }
        }
      }
    },

    appendContent: function (element, content) {
      try {
        var newContent = jQuery(content).appendTo(jQuery("#" + element));
        jQuery("#" + element)
          .find(".box-loading")
          .remove();
        jQuery(document).trigger(
          "rkw-ajax-api-content-changed",
          newContent.parent()
        );
      } catch (error) {}
    },

    prependContent: function (element, content) {
      try {
        var newContent = jQuery(content).prependTo(jQuery("#" + element));
        jQuery("#" + element)
          .find(".box-loading")
          .remove();
        jQuery(document).trigger(
          "rkw-ajax-api-content-changed",
          newContent.parent()
        );
      } catch (error) {}
    },

    replaceContent: function (element, content) {
      try {
        if (jQuery(content).length > 0) {
          var newContent = jQuery(content).appendTo(
            jQuery("#" + element).empty()
          );
          jQuery(document).trigger("rkw-ajax-api-content-changed", newContent);
        } else {
          jQuery("#" + element)
            .empty()
            .append(content);
        }
      } catch (error) {}
    },

    getMessageBox: function (text, type, parent) {
      var box = jQuery(
        '<div class="message-box" data-for="#' + parent + '">' + text + "</div>"
      );
      if (type === 1) {
        box.addClass(this.settings.boxSuccessClass);
      } else if (type === 2) {
        box.addClass(this.settings.boxHintClass);
      } else if (type === 99) {
        box.addClass(this.settings.boxErrorClass);
      }

      return box;
    }
  });

  // A really lightweight plugin wrapper around the constructor,
  // preventing against multiple instantiations
  $.fn[pluginName] = function (options) {
    return this.each(function () {
      if (!$.data(this, "plugin_" + pluginName)) {
        $.data(this, "plugin_" + pluginName, new Plugin(this, options));
      }
    });
  };
})(jQuery, window, document);
