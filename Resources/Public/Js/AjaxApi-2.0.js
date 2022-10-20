!function(n,i,a){var s="ajaxApi",r={boxSuccessClass:"success",boxHintClass:"hint",boxErrorClass:"error",loadingIndicatorActiveClass:"is-ajax-loading",loadingIndicatorTargetClass:"is-ajax-target",loadingIndicatorHtml:'<div class="loading-indicator"></div>',loadingIndicatorHtmlClass:"ajax-overlay"};function e(t,e){this.element=t,this.settings=n.extend({},r,e),this._defaults=r,this._name=s,this.init()}n.extend(e.prototype,{init:function(){this.settings.$el=n(this.element),this.settings.elementType=this.settings.$el.prop("tagName"),"FORM"===this.settings.elementType?(this.settings.formElements=this.settings.$el.find(":input:not(.btn)"),this.settings.url=this.settings.$el.attr("action"),this.settings.$el.hasClass("ajax-feedback")&&(this.settings.feedbackUrl=this.settings.$el.attr("data-feedback-url")),this.settings.$el.data("ajax-indicator-id")?this.settings.indicatorTarget=n("#"+this.settings.$el.data("ajax-indicator-id")):this.settings.indicatorTarget=this.settings.$el):"A"===this.settings.elementType?(this.settings.url=this.settings.$el.attr("href"),this.settings.$el.data("ajax-indicator-id")&&(this.settings.indicatorTarget=n("#"+this.settings.$el.data("ajax-indicator-id")))):"TEMPLATE"===this.settings.elementType&&(this.settings.url=this.settings.$el.data("ajax-url"),this.settings.ignore=!1,!this.settings.$el.data("ajax-ignore")||1!==this.settings.$el.data("ajax-ignore")&&"true"!==this.settings.$el.data("ajax-ignore").toLowerCase()||(this.settings.ignore=!0),this.settings.ignore||(this.settings.$el.data("ajax-max-width")?this.sendOnViewport():this.sendOnPageLoad())),this.bindEvents()},bindEvents:function(){var t;"FORM"===this.settings.elementType?(t=this).settings.$el.hasClass("ajax")&&this.settings.$el.hasClass("ajax-feedback")?(this.settings.formElements.each(function(){n(this).on("change",t.sendField.bind(t))}),this.settings.$el.on("submit",this.sendForm.bind(this))):this.settings.$el.hasClass("ajax")&&(this.settings.$el.on("submit",this.sendForm.bind(this)),this.settings.$el.hasClass("ajax-submit-only")||this.settings.formElements.each(function(){n(this).on("change",t.sendForm.bind(t))}),this.settings.$el.find(".ajax-send")&&this.settings.$el.find(".ajax-send").each(function(){n(this).on("click",t.sendFormByLink.bind(t))}),this.settings.$el.find(".ajax-override-submit")&&this.settings.$el.find(".ajax-override-submit").each(function(){n(this).on("click",t.sendNormalFormByLink.bind(t))}),this.settings.$el.find(".ajax-override")&&this.settings.$el.find(".ajax-override").each(function(){"A"===n(this).prop("tagName")?n(this).on("click",t.overrideForm.bind(t)):"SELECT"===n(this).prop("tagName")&&n(this).on("change",t.overrideForm.bind(t))})):"A"===this.settings.elementType?this.settings.$el.on("click",this.sendLink.bind(this)):"TEMPLATE"===this.settings.elementType&&this.settings.$el.data("ajax-max-width")&&jQuery(i).on("resize",this.sendOnViewport.bind(this))},addLoadingIndicator:function(t){var e=n.parseHTML('<div class="'+this.settings.loadingIndicatorHtmlClass+'">'+this.settings.loadingIndicatorHtml+"</div>");this.settings.$el.addClass(this.settings.loadingIndicatorActiveClass).blur(),this.settings.indicatorTarget&&(this.settings.indicatorTarget.addClass(this.settings.loadingIndicatorTargetClass),this.settings.indicatorTarget.append(e))},removeLoadingIndicator:function(){try{this.settings.$el.removeClass(this.settings.loadingIndicatorActiveClass).blur()}catch(t){}this.settings.indicatorTarget&&(this.settings.indicatorTarget.removeClass(this.settings.loadingIndicatorTargetClass),this.settings.indicatorTarget.find("."+this.settings.loadingIndicatorHtmlClass).remove())},sendForm:function(t){var e,s,i;t.preventDefault(),this.settings.$el.hasClass("override-submit")||(e=this.settings.url,s=this.getFormValues(),i=this.generateRequestId(),this.ajaxRequest(i,e,s),this.addLoadingIndicator(t),this.settings.$el.hasClass("ajax-scroll-top")&&n("html, body").stop().animate({scrollTop:this.settings.$el.offset().top},1e3,"easeOutQuart"))},sendFormByLink:function(t){t.preventDefault(),this.sendForm(t)},sendNormalFormByLink:function(t){t.preventDefault();var e=n(t.currentTarget),s=e.closest("form.ajax");s.attr("action",e.attr("href")).addClass("override-submit"),s.unbind("submit"),s.submit()},sendLink:function(t){t.preventDefault();var e=this.settings.url,s=this.generateRequestId();this.settings.$el.hasClass(this.settings.loadingIndicatorActiveClass)||(this.ajaxRequest(s,e,[]),this.addLoadingIndicator(t))},sendField:function(t){var e=n(t.currentTarget),s=this.settings.feedbackUrl,i=e.serializeArray(),a=this.generateRequestId();this.ajaxRequest(a,s,i)},overrideForm:function(t){t.preventDefault();var e,s=n(t.currentTarget),i=this.settings.$el.find("select, input").not(".ajax-override").serializeArray(),a=this.generateRequestId();"A"===s.prop("tagName")?e=s.attr("href"):"SELECT"===s.prop("tagName")&&(e=s.val()),e&&this.ajaxRequest(a,e,i)},sendOnPageLoad:function(){var t=this.settings.url,e=this.generateRequestId();t&&this.ajaxRequest(e,t,[],!0)},sendOnViewport:function(){var t=this.settings.url,e=this.settings.$el,s=this.generateRequestId();t&&e.data("ajax-max-width")>=jQuery(i).width()&&this.ajaxRequest(s,t,[],!0)},getFormValues:function(){return this.settings.$el.serializeArray()},generateRequestId:function(){return Math.random().toString(36).substring(5)},ajaxRequest:function(t,e,s,i){var a=this;s.unshift({name:"type",value:250}),n.ajax({method:"post",url:e,data:n.param(s),dataType:"json",complete:function(t){try{t=JSON.parse(t.responseText),a.parseContent(t),a.removeLoadingIndicator(),i||a.updateBrowserHistory(t,e,n.param(s))}catch(t){console.log(t.message)}}})},parseContent:function(t){for(var e in t)if("message"===e){var s=t[e];for(var i in s){var a=this.getMessageBox(s[i].message,s[i].type,i);this.appendContent(i,a)}}else if("data"!==e&&"html"===e){var n=t[e];for(var i in n)for(var r in n[i])"append"===r?this.appendContent(i,n[i][r]):"prepend"===r?this.prependContent(i,n[i][r]):"replace"===r&&this.replaceContent(i,n[i][r])}},appendContent:function(t,e){try{var s=jQuery(e);s.ajaxApi().find(".ajax").ajaxApi(),s.appendTo(jQuery("#"+t)),jQuery(a).trigger("rkw-ajax-api-content-changed",s.parent())}catch(t){console.log(t.message)}},prependContent:function(t,e){try{var s=jQuery(e);s.ajaxApi().find(".ajax").ajaxApi(),s.prependTo(jQuery("#"+t)),jQuery(a).trigger("rkw-ajax-api-content-changed",s.parent())}catch(t){console.log(t.message)}},replaceContent:function(t,e){try{var s;0<jQuery(e).length?((s=jQuery(e)).ajaxApi().find(".ajax").ajaxApi(),s.appendTo(jQuery("#"+t).empty()),jQuery(a).trigger("rkw-ajax-api-content-changed",s)):jQuery("#"+t).empty().append(e)}catch(t){console.log(t.message)}},getMessageBox:function(t,e,s){var i=jQuery('<div class="message-box" data-for="#'+s+'">'+t+"</div>");return 1===e?i.addClass(this.settings.boxSuccessClass):2===e?i.addClass(this.settings.boxHintClass):99===e&&i.addClass(this.settings.boxErrorClass),i},updateBrowserHistory:function(t,e,s){}}),n.fn[s]=function(t){return this.each(function(){n.data(this,"plugin_"+s)||n.data(this,"plugin_"+s,new e(this,t))})}}(jQuery,window,document);
