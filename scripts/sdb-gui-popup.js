// This is the ShelfDB GUI extension
var ShelfDB = (function(sdb,$) {

  // Popup module
  var popupModule = (function() {

    return {
      // Open external confirm dialog
      confirmPopUp: function(options) {

        var defaults = {
          header: "",
          text: "",
          confirmButtonText: "Ok",
          transition: "pop",
          confirm: undefined,
          cancel: undefined
        };

        options = $.extend(defaults, options);

        var $popup = $('#popupConfirmDialog');

        var setupPopup = function($popup) {

          var $buttonresult = "";
          var $cancelbtn = $popup.find("[name='popupCancelBtn']");

          $popup.find("[name='dialogHeader']").first().text(options.header);
          $popup.find("[name='dialogText']").first().text(options.text);
          $popup.find("[name='popupOkBtn']").first().text(options.confirmButtonText);

          // Keypress handlers
          $popup.off('keypress');
          $popup.one('keypress', function(e){
            if(e.keyCode == 13) {
              // Submit
              e.stopPropagation();
              //$okbtn.trigger("click");
            }
          });

          $popup.off('keyup');
          $popup.one('keyup', function(e){
            if(e.keyCode == 27) {
              e.stopPropagation();
              $cancelbtn.trigger("click");
            }
          });

          // Button click handlers
          $popup.find('a').one('click', function(ev){
            // Return buttonresult
            $buttonresult = $(ev.target).attr('buttonresult');
            console.log($buttonresult);
          });

          $popup.one('popupafteropen', function(ev,ui) {
              $cancelbtn.focus();
          });

          $popup.one('popupafterclose', function(ev,ui) {

            if( $buttonresult == "ok" && options.confirm ) {
              options.confirm();
            } else if ($buttonresult == "cancel" && options.cancel ) {
              options.cancel();
            }
          });

          return $popup;
        };

        // Check if we need to load
        if( $popup.length > 0 )
        {
          setupPopup($popup);
          $popup.popup('open', { transition: options.transition });

        } else {

          var $popuptarget = $('<div />').appendTo('body');

          $.mobile.referencedLoading('show');

          $popuptarget.load(sdb.Core.basePath+'pages/popup-confirmdialog.php', function() {
            var $popup = $('#popupConfirmDialog');
            Lang.searchAndReplace();
            setupPopup($popup);
            $(this).enhanceWithin();

            $.mobile.referencedLoading('hide');

            $popup.popup('open', { transition: options.transition});
          });
        }
      },

      // Open external input dialog
      inputPopUp: function (options) {

        // Get options
        var defaults = {
          header: Lang.get('change'),
          headline: Lang.get('enterName'),
          message: "",
          confirmButtonText: Lang.get('ok'),
          textLabel: "",
          textPlaceholder: "",
          textDefault: "",
          ok: null,
          cancel: null,
          transition: "pop",
          validatorRules: [],
          closeManually: false
        };

        options = $.extend(defaults,options);

        var $popup = $('#popupInputDialog');

        // The setup function
        var setupPopup = function($popup) {
          // Text replace and callback setup
          var $buttonresult = "";
          var $input = $popup.find("[name='dialogValue']");
          var $okbtn = $popup.find("[name='popupOkBtn']");
          var $cancelbtn = $popup.find("[name='popupCancelBtn']");
          var $form = $popup.find("#formInputDialog");
          if( options.inputRequired ) $input.attr('required','');

          // Reset validator to allow for new rulesets
          $form.removeData('validator');
          $form.validate({
            rules: {
              dialogValue: options.validatorRules
            }
          });

          $popup.find("[name='dialogHeader']").first().text(options.header);
          $popup.find("[name='dialogHeadline']").first().text(options.headline);
          $popup.find("[name='dialogMessage']").first().text(options.message);
          $popup.find("[name='dialogTextLabel']").first().text(options.textLabel);
          $input.val(options.textDefault);
          $input.attr('placeholder',options.textPlaceholder);
          $okbtn.text(options.confirmButtonText);

          // Keypress handlers
          $popup.off('keypress');
          $popup.on('keypress', function(e){
            if(e.keyCode == 13) {
              // Submit
              e.stopPropagation();
              e.preventDefault();
              $okbtn.trigger("click");
            }
          });

          $popup.off('keyup');
          $popup.on('keyup', function(e){
            if(e.keyCode == 27) {
              e.stopPropagation();
              e.preventDefault();
              $cancelbtn.trigger("click");
            }
          });

          $form.off('submit');
          $form.on('submit', function(evt) {
            evt.preventDefault();
            evt.stopPropagation();

            if( !$(evt.target).valid() )
              return;

            if( options.ok ) options.ok($input.val());

            if( !options.closeManually )
              $popup.popup('close');
          });

          // Button click handlers
          $popup.find('a').off('click');
          $popup.find('a').one('click', function(ev){
            // Return buttonresult
            $buttonresult = $(ev.target).attr('buttonresult');
            console.log($buttonresult);
          });

          $popup.off('popupafteropen');
          $popup.one('popupafteropen', function(ev,ui) {
              $popup.find("input").first().focus().select();
          });

          $popup.off('popupafterclose');
          $popup.one('popupafterclose', function(ev,ui) {
            if ($buttonresult == "cancel" && options.cancel) {
              options.cancel($input.val());
            }
            $form.validate().resetForm();
          });

          return $popup;
        };

        // Check if we need to load
        if( $popup.length > 0 )
        {
          setupPopup($popup);
          $popup.popup('open', { transition: options.transition });

        } else {

          var $popuptarget = $('<div />').appendTo('body');

          $.mobile.referencedLoading('show');

          $popuptarget.load(sdb.Core.basePath+'pages/popup-inputdialog.php', function() {
            var $popup = $('#popupInputDialog');
            Lang.searchAndReplace();
            setupPopup($popup);
            $(this).enhanceWithin();

            $.mobile.referencedLoading('hide');

            $popup.popup('open', { transition: options.transition });
          });
        }
      },

      // Open external input dialog
      inputMultilinePopUp: function(options) {

          var defaults = {
            header: Lang.get('input'),
            headline: "",
            message: "",
            confirmButtonText: Lang.get('ok'),
            textLabel: "",
            textPlaceholder: "",
            textDefault: "",
            ok: null,
            cancel: null,
            transition: "pop",
            afteropen: null
          };

          options = $.extend(defaults,options)

          var $popup = $('#popupInputMultilineDialog');

          var setupPopup = function($popup) {
            var $buttonresult;

            var $input = $popup.find("[name='dialogText']");
            var $okbtn = $popup.find("[name='popupOkBtn']");
            var $cancelbtn = $popup.find("[name='popupCancelBtn']");

            $popup.find("[name='dialogHeader']").first().text(options.header);
            $popup.find("[name='dialogHeadline']").first().text(options.headline);
            $popup.find("[name='dialogMessage']").first().text(options.message);
            $popup.find("[name='dialogTextLabel']").first().text(options.textLabel);
            $input.val(options.textDefault);
            $input.attr('placeholder',options.textPlaceholder);
            $okbtn.text(options.confirmButtonText);

            // Keypress handlers
            $popup.off('keyup');
            $popup.one('keyup', function(e){
              if(e.keyCode == 27) {
                e.stopPropagation();
                $cancelbtn.trigger("click");
              }
            });

            // Button click handlers
            $popup.find('a').one('click', function(ev){
              // Return buttonresult
              $buttonresult = $(ev.target).attr('buttonresult');
              console.log($buttonresult);
            });

            $popup.one('popupafteropen', function(ev,ui) {
                $popup.find("textarea").first().focus();
                if( options.afteropen ) options.afteropen(ev,ui);
            });
            $popup.one('popupafterclose', function(ev,ui) {
              if( $buttonresult == "ok" && options.ok ) {
                options.ok($input.val());
              } else if ($buttonresult == "cancel" && options.cancel) {
                options.cancel($input.val());
              }
            });
          };

          if( $popup.length > 0 ) {
            setupPopup($popup);
            $popup.popup('open', { transition: options.transition });
          } else {
            $popuptarget = $('<div />').appendTo('body');

            $.mobile.referencedLoading('show');

            $popuptarget.load(sdb.Core.basePath+'pages/popup-inputmultilinedialog.php', function() {
              $popup = $('#popupInputMultilineDialog');

              Lang.searchAndReplace();
              setupPopup($popup);
              $(this).enhanceWithin();

              $.mobile.referencedLoading('hide');

              $popup.popup('open', { transition: options.transition });
            });
          }
      },

      // Function to handle loading of external popup and enhancing
      openExternalPopup: function(options) {
        // Get options
        var defaults = {
          url: null,
          postdata: null,
          afteropen: undefined,
          afterclose: undefined,
          click: undefined,
          submit: undefined,
          forceReload: false,
          transition: "pop",
          minBorder: 30,
          customEventName: undefined,
          customEventHandler: undefined,
          constrainHeight: true,
          fixedMaxWidth: null
        };

        options = $.extend(defaults, options);

        if(!(options.url)) return;

        // Cleanup pageUrl and remove GET stuff
        var urlSplit = options.url.split("?");

        options.url = urlSplit[0];
        var urlParams = urlSplit[1] || "";

        var $popuptarget = $('[name="externalPopup"][pageurl="'+options.url+'"]');

        // Force a reload if the page parameters differ as we do not know if the appearance changes server-side
        if( $popuptarget.length > 0 && urlParams != $popuptarget.attr('pageparams') ) {
          options.forceReload = true;
        }

        if( options.forceReload && $popuptarget.length > 0 )
        {
          $('[origin="'+options.url+'"]').popup('destroy');
          $popuptarget.remove();
          $popuptarget = $();
        }

        if( $popuptarget.length > 0 ) {

          var dialogId = $('[origin="'+options.url+'"]');

          dialogId.popup('open', { transition: options.transition});

        } else {
          $popuptarget = $('<div />').appendTo('body');
          $popuptarget.attr({
            	name: 'externalPopup',
              'pageurl': options.url,
              'pageparams': urlParams
          });

          $.mobile.referencedLoading('show');

          $popuptarget.load(options.url + (urlParams?"?"+urlParams:""), options.postdata, function() {

            var dialogId = $(this).find('[data-role="popup"]').first();
            dialogId.attr({
              'origin': options.url
            });
            Lang.searchAndReplace();
            $(this).enhanceWithin();

            // Setup event handlers
            dialogId.popup({
              beforeposition: function() {

                  $(this).css( {
                    maxHeight: ( options.constrainHeight ? window.innerHeight - options.minBorder : null ),
                    maxWidth: ( options.fixedMaxWidth ? options.fixedMaxWidth : window.innerWidth - options.minBorder )
                  });

                },
              afterclose: (options.afterclose ? options.afterclose : null),
              afteropen: (options.afteropen ? options.afteropen : null),
            });

            if( options.customEventName && options.customEventHandler )
              dialogId.on(options.customEventName, options.customEventHandler);

            if( options.click )
              dialogId.find('a, button').click( options.click );

            if( options.submit )
              dialogId.find('form').on( 'submit', options.submit );

            $.mobile.referencedLoading('hide');

            dialogId.popup('open', { transition: options.transition});
          });
        }
      },

      // Login user dialog
      loginPopup: function(options) {
        defaults = {
          loggedInSelector: '[data-group=loggedIn]',
          loggedOutSelector: '[data-group=loggedOut]',
          success: null
        };

        options = $.extend({},defaults, options);
        debugger;
        sdb.GUI.Popup.openExternalPopup({
          url: sdb.Core.basePath+'pages/popup-login.php',
          submit: function(evt) {
            evt.preventDefault();
            evt.stopPropagation();

            if( !$(evt.target).valid() )
              return;

            $.mobile.referencedLoading('show');

            $.ajax({
              url: sdb.Core.basePath+'lib/edit-user.php',
              data: $(evt.target).formData(),
              method: 'post',
              cache: false,
              dataType: 'json',
              success: function(data) {
                $.mobile.referencedLoading('hide');

                if( data.success ) {
                  if( options.success && typeof options.success === 'function' )
                    options.success(data);

                  $(options.loggedInSelector).removeClass('ui-screen-hidden');
                  $(options.loggedOutSelector).addClass('ui-screen-hidden');

                  $('#popupLoginDialog').popup('close');
                } else {
                  // Show error
                  $('#popupLoginDialog').find('input').addClass('error');
                }

              },
              error: function() {
                // Show error
                $.mobile.referencedLoading('hide');
              }
            });
          },
          afterclose: function(evt) {
            $(this).find("form").validate().resetForm();
            $(this).find("#username").val("");
            $(this).find("#password").val("");
          }
        });
      }
    }
  })();

  if( typeof sdb.GUI === 'undefined' ) {
    sdb.GUI = {};
  }

  $.extend(sdb.GUI, {
    Popup: popupModule
  });

  return sdb;
})(typeof ShelfDB !== 'undefined' ? ShelfDB : {}, jQuery);
