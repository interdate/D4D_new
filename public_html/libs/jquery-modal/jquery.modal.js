/*
    A simple jQuery kfModal (http://github.com/kylefox/jquery-kfModal)
    Version 0.6.0
*/
(function($) {

  var current = null;

  $.kfModal = function(el, options) {
    $.kfModal.close(); // Close any open kfModals.
    var remove, target;
    this.$body = $('body');
    this.options = $.extend({}, $.kfModal.defaults, options);
    this.options.doFade = !isNaN(parseInt(this.options.fadeDuration, 10));
    if (el.is('a')) {
      target = el.attr('href');
      //Select element by id from href
      if (/^#/.test(target)) {
        this.$elm = $(target);
        if (this.$elm.length !== 1) return null;
        this.$body.append(this.$elm);
        this.open();
      //AJAX
      } else {
        this.$elm = $('<div>');
        this.$body.append(this.$elm);
        remove = function(event, kfModal) { kfModal.elm.remove(); };
        this.showSpinner();
        el.trigger($.kfModal.AJAX_SEND);
        $.get(target).done(function(html) {
          if (!current) return;
          el.trigger($.kfModal.AJAX_SUCCESS);
          current.$elm.empty().append(html).on($.kfModal.CLOSE, remove);
          current.hideSpinner();
          current.open();
          el.trigger($.kfModal.AJAX_COMPLETE);
        }).fail(function() {
          el.trigger($.kfModal.AJAX_FAIL);
          current.hideSpinner();
          el.trigger($.kfModal.AJAX_COMPLETE);
        });
      }
    } else {
      this.$elm = el;
      this.$body.append(this.$elm);
      this.open();
    }
  };

  $.kfModal.prototype = {
    constructor: $.kfModal,

    open: function() {
      var m = this;
      if(this.options.doFade) {
        this.block();
        setTimeout(function() {
          m.show();
        }, this.options.fadeDuration * this.options.fadeDelay);
      } else {
        this.block();
        this.show();
      }
      if (this.options.escapeClose) {
        $(document).on('keydown.kfModal', function(event) {
          if (event.which == 27) $.kfModal.close();
        });
      }
      if (this.options.clickClose) this.blocker.click(function(e){
        if (e.target==this)
          $.kfModal.close();
      });
    },

    close: function() {
      this.unblock();
      this.hide();
      $(document).off('keydown.kfModal');
    },

    block: function() {
      this.$elm.trigger($.kfModal.BEFORE_BLOCK, [this._ctx()]);
      this.blocker = $('<div class="jquery-kfModal blocker"></div>');
      this.$body.css('overflow','hidden');
      this.$body.append(this.blocker);
      if(this.options.doFade) {
        this.blocker.css('opacity',0).animate({opacity: 1}, this.options.fadeDuration);
      }
      this.$elm.trigger($.kfModal.BLOCK, [this._ctx()]);
    },

    unblock: function() {
      if(this.options.doFade) {
        var self=this;
        this.blocker.fadeOut(this.options.fadeDuration, function() {
          self.blocker.children().appendTo(self.$body);
          self.blocker.remove();
          self.$body.css('overflow','');
        });
      } else {
        this.blocker.children().appendTo(this.$body);
        this.blocker.remove();
        this.$body.css('overflow','');
      }
    },

    show: function() {
      this.$elm.trigger($.kfModal.BEFORE_OPEN, [this._ctx()]);
      if (this.options.showClose) {
        this.closeButton = $('<a href="#close-kfModal" rel="kfModal:close" class="close-kfModal ' + this.options.closeClass + '">' + this.options.closeText + '</a>');
        this.$elm.append(this.closeButton);
      }
      this.$elm.addClass(this.options.kfModalClass + ' current');
      this.$elm.appendTo(this.blocker);
      if(this.options.doFade) {
        this.$elm.css('opacity',0).animate({opacity: 1}, this.options.fadeDuration);
      } else {
        this.$elm.show();
      }
      this.$elm.trigger($.kfModal.OPEN, [this._ctx()]);
    },

    hide: function() {
      this.$elm.trigger($.kfModal.BEFORE_CLOSE, [this._ctx()]);
      if (this.closeButton) this.closeButton.remove();
      this.$elm.removeClass('current');

      var _this = this;
      if(this.options.doFade) {
        this.$elm.fadeOut(this.options.fadeDuration, function () {
          _this.$elm.trigger($.kfModal.AFTER_CLOSE, [_this._ctx()]);
        });
      } else {
        this.$elm.hide(0, function () {
          _this.$elm.trigger($.kfModal.AFTER_CLOSE, [_this._ctx()]);
        });
      }
      this.$elm.trigger($.kfModal.CLOSE, [this._ctx()]);
    },

    showSpinner: function() {
      if (!this.options.showSpinner) return;
      this.spinner = this.spinner || $('<div class="' + this.options.kfModalClass + '-spinner"></div>')
        .append(this.options.spinnerHtml);
      this.$body.append(this.spinner);
      this.spinner.show();
    },

    hideSpinner: function() {
      if (this.spinner) this.spinner.remove();
    },

    //Return context for custom events
    _ctx: function() {
      return { elm: this.$elm, blocker: this.blocker, options: this.options };
    }
  };

  $.kfModal.close = function(event) {
    if (!current) return;
    if (event) event.preventDefault();
    current.close();
    var that = current.$elm;
    current = null;
    return that;
  };

  // Returns if there currently is an active kfModal
  $.kfModal.isActive = function () {
    return current ? true : false;
  }

  $.kfModal.defaults = {
    escapeClose: true,
    clickClose: true,
    closeText: 'Close',
    closeClass: '',
    kfModalClass: "kfModal",
    spinnerHtml: null,
    showSpinner: true,
    showClose: true,
    fadeDuration: null,   // Number of milliseconds the fade animation takes.
    fadeDelay: 1.0        // Point during the overlay's fade-in that the kfModal begins to fade in (.5 = 50%, 1.5 = 150%, etc.)
  };

  // Event constants
  $.kfModal.BEFORE_BLOCK = 'kfModal:before-block';
  $.kfModal.BLOCK = 'kfModal:block';
  $.kfModal.BEFORE_OPEN = 'kfModal:before-open';
  $.kfModal.OPEN = 'kfModal:open';
  $.kfModal.BEFORE_CLOSE = 'kfModal:before-close';
  $.kfModal.CLOSE = 'kfModal:close';
  $.kfModal.AFTER_CLOSE = 'kfModal:after-close';
  $.kfModal.AJAX_SEND = 'kfModal:ajax:send';
  $.kfModal.AJAX_SUCCESS = 'kfModal:ajax:success';
  $.kfModal.AJAX_FAIL = 'kfModal:ajax:fail';
  $.kfModal.AJAX_COMPLETE = 'kfModal:ajax:complete';

  $.fn.kfModal = function(options){
    if (this.length === 1) {
      current = new $.kfModal(this, options);
    }
    return this;
  };

  // Automatically bind links with rel="kfModal:close" to, well, close the kfModal.
  $(document).on('click.kfModal', 'a[rel="kfModal:close"]', $.kfModal.close);
  $(document).on('click.kfModal', 'a[rel="kfModal:open"]', function(event) {
    event.preventDefault();
    $(this).kfModal();
  });
})(jQuery);
