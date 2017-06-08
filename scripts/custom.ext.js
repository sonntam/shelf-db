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

// Open external confirm dialog
function confirmPopUp(selector, header, text, confirmbtntext, fnc_ok,
  fnc_cancel) {

  var setupPopup = function() {

    var $buttonresult = "";
    var $popup = $('#popupDialog');
    var $cancelbtn = $popup.find("[name='popupCancelBtn']");

    $popup.find("[name='dialogHeader']").first().text(header);
    $popup.find("[name='dialogText']").first().text(text);
    $popup.find("[name='popupOkBtn']").first().text(confirmbtntext);

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
      $popup.find('a').off('click');
    });

    $popup.one('popupafteropen', function(ev,ui) {
        $cancelbtn.focus();
    });

    $popup.one('popupafterclose', function(ev,ui) {

      if( $buttonresult == "ok" && fnc_ok ) {
        fnc_ok();
      } else if ($buttonresult == "cancel" && fnc_cancel) {
        fnc_cancel();
      }
    });

    return $popup;
  };

  // Check if we need to load
  var $popup = $('#popupDialog');
  var $tgt = $(selector);

  if( $popup.length > 0 )
  {
    setupPopup();
    $popup.popup('open', { transition: "pop"});

  } else {

    $tgt.load('/pages/popup-confirmdialog.php', function() {

      Lang.searchAndReplace();
      $popup = setupPopup();
      $tgt.enhanceWithin();

      $popup.popup('open', { transition: "pop"});
    });
  }
}


// Open external input dialog
function inputPopUp(selector, header, headline, message, confirmbtntext,
  textlabel, textplaceholder, textdefault, fnc_ok, fnc_cancel) {

  // The setup function
  var setupPopup = function() {
    // Text replace and callback setup
    var $buttonresult = "";
    var $popup = $('#popupInputDialog');
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
      $popup.find('a').off('click');
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
  var $popup = $('#popupInputDialog');
  var $tgt = $(selector);

  if( $popup.length > 0 )
  {
    setupPopup();
    $popup.popup('open', { transition: "pop"});

  } else {

    $tgt.load('/pages/popup-inputdialog.php', function() {

      Lang.searchAndReplace();
      $popup = setupPopup();
      $tgt.enhanceWithin();

      $popup.popup('open', { transition: "pop"});
    });
  }
}

// Function to handle loading of external popup and enhancing
function openExternalPopup(evtClickSource, targetSelector, pageUrl) {

    $(targetSelector).load(pageUrl, function() {
      var dialogId = $(this).children().first();

      Lang.searchAndReplace();
      $(this).enhanceWithin();

      var openDialog = function() {
          dialogId.popup('open');
      }

      dialogId.popup({
        beforeposition: function() {
            $(this).css( {
              height: window.innerHeight - 30,
              maxWidth: window.innerWidth - 30
            });
        }
      });

      $(evtClickSource).unbind('click');
      $(evtClickSource).click( openDialog );

      openDialog();

    });
}
