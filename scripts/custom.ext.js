// [name] is the name of the event "click", "mouseover", ..
// same as you'd pass it to bind()
// [fn] is the handler function
$.fn.bindFirst = function(name, fn) {
    // bind as you normally would
    // don't want to miss out on any jQuery magic
    this.on(name, fn);

    // Thanks to a comment by @Martin, adding support for
    // namespaced events too.
    this.each(function() {
        var handlers = $._data(this, 'events')[name.split('.')[0]];
        // take out the handler we just inserted from the end
        var handler = handlers.pop();
        // move it at the beginning
        handlers.splice(0, 0, handler);
    });
};

$.fn.formData = function() {
  // https://stackoverflow.com/a/24012884
  return $(this).serializeArray().reduce(function(obj, item) {
    obj[item.name] = item.value;
    return obj;
  }, {});
};

$.mobile.switchPopup = function(sourceElement, destinationElement, onswitched) {
    var afterClose = function() {
        destinationElement.popup("open");
        sourceElement.off("popupafterclose", afterClose);

        if (onswitched && typeof onswitched === "function"){
            onswitched();
        }
    };

    sourceElement.on("popupafterclose", afterClose);
    sourceElement.popup("close");
};

// Spinner count
$.mobile.spinnerRefCount = 0;
$.mobile.referencedLoading = function(cmd) {
  switch(cmd) {
    case 'show':
      if( this.spinnerRefCount == 0 )
        this.loading('show');

      this.spinnerRefCount++;
      break;
    case 'hide':
      this.spinnerRefCount--;
      if( this.spinnerRefCount <= 0 )
      this.loading('hide');
      break;
  }
};

// Open external confirm dialog
function confirmPopUp(options) {

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

    $popuptarget.load('/pages/popup-confirmdialog.php', function() {
      var $popup = $('#popupConfirmDialog');
      Lang.searchAndReplace();
      setupPopup($popup);
      $(this).enhanceWithin();

      $.mobile.referencedLoading('hide');

      $popup.popup('open', { transition: options.transition});
    });
  }
}


// Open external input dialog
function inputPopUp(header, headline, message, confirmbtntext,
  textlabel, textplaceholder, textdefault, fnc_ok, fnc_cancel) {

  var $popup = $('#popupInputDialog');

  // The setup function
  var setupPopup = function($popup) {
    // Text replace and callback setup
    var $buttonresult = "";
    var $input = $popup.find("[name='dialogText']");
    var $okbtn = $popup.find("[name='popupOkBtn']");
    var $cancelbtn = $popup.find("[name='popupCancelBtn']");

    $popup.find("[name='dialogHeader']").first().text(header);
    $popup.find("[name='dialogHeadline']").first().text(headline);
    $popup.find("[name='dialogMessage']").first().text(message);
    $popup.find("[name='dialogTextLabel']").first().text(textlabel);
    $input.val(textdefault);
    $input.attr('placeholder',textplaceholder);
    $okbtn.text(confirmbtntext);

    // Keypress handlers
    $input.off('keypress');
    $input.one('keypress', function(e){
      if(e.keyCode == 13) {
        // Submit
        e.stopPropagation();
        $okbtn.trigger("click");
      }
    });

    $input.off('keyup');
    $input.one('keyup', function(e){
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
        $popup.find("input").first().focus().select();
    });
    $popup.one('popupafterclose', function(ev,ui) {
      if( $buttonresult == "ok" && fnc_ok ) {
        fnc_ok($input.val());
      } else if ($buttonresult == "cancel" && fnc_cancel) {
        fnc_cancel($input.val());
      }
    });

    return $popup;
  };

  // Check if we need to load
  if( $popup.length > 0 )
  {
    setupPopup($popup);
    $popup.popup('open', { transition: "pop"});

  } else {

    var $popuptarget = $('<div />').appendTo('body');

    $.mobile.referencedLoading('show');

    $popuptarget.load('/pages/popup-inputdialog.php', function() {
      var $popup = $('#popupInputDialog');
      Lang.searchAndReplace();
      setupPopup($popup);
      $(this).enhanceWithin();

      $.mobile.referencedLoading('hide');

      $popup.popup('open', { transition: "pop"});
    });
  }
}

// Open external input dialog
function inputMultilinePopUp(header, headline, message, confirmbtntext,
  textlabel, textplaceholder, textdefault, fnc_ok, fnc_cancel) {

    var $popup = $('#popupInputMultilineDialog');

    var setupPopup = function($popup) {
      var $buttonresult;

      var $input = $popup.find("[name='dialogText']");
      var $okbtn = $popup.find("[name='popupOkBtn']");
      var $cancelbtn = $popup.find("[name='popupCancelBtn']");

      $popup.find("[name='dialogHeader']").first().text(header);
      $popup.find("[name='dialogHeadline']").first().text(headline);
      $popup.find("[name='dialogMessage']").first().text(message);
      $popup.find("[name='dialogTextLabel']").first().text(textlabel);
      $input.val(textdefault);
      $input.attr('placeholder',textplaceholder);
      $okbtn.text(confirmbtntext);

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
      });
      $popup.one('popupafterclose', function(ev,ui) {
        if( $buttonresult == "ok" && fnc_ok ) {
          fnc_ok($input.val());
        } else if ($buttonresult == "cancel" && fnc_cancel) {
          fnc_cancel($input.val());
        }
      });
    };

    if( $popup.length > 0 ) {
      setupPopup($popup);
      $popup.popup('open', { transition: "pop"});
    } else {
      $popuptarget = $('<div />').appendTo('body');

      $.mobile.referencedLoading('show');

      $popuptarget.load('/pages/popup-inputmultilinedialog.php', function() {
        $popup = $('#popupInputMultilineDialog');

        Lang.searchAndReplace();
        setupPopup($popup);
        $(this).enhanceWithin();

        $.mobile.referencedLoading('hide');

        $popup.popup('open', { transition: "pop"});
      });
    }
}

// Function to handle loading of external popup and enhancing
function openExternalPopup(options) {
  // Get options
  var defaults = {
    url: null,
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

    var dialogId = $('[origin="'+options.pageUrl+'"]');

    dialogId.popup('open', { transition: options.transition});

  } else {
    $popuptarget = $('<div />').appendTo('body');
    $popuptarget.attr({
      	name: 'externalPopup',
        'pageurl': options.url,
        'pageparams': urlParams
    });

    $.mobile.referencedLoading('show');

    $popuptarget.load(options.url + (urlParams?"?"+urlParams:""), function() {

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
}
