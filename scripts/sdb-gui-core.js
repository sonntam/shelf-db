// This is the ShelfDB GUI extension
var ShelfDB = (function(sdb,$) {

  // Attach universal popup handler
  $(document).on('click','a[data-toggle="universalPopup"]', function(evt) {
    debugger;
    var imgUrl;
    var img;

    // Get image
    img = $(evt.currentTarget).find('img').first();

    if( img.length == 0 )
      return;

    // Get image url
    imgUrl = img.attr('data-other-src');

    if( typeof imgUrl === "undefined" )
      imgUrl = img.attr('src');

    if( typeof imgUrl === "undefined" )
      return;

    var popUp = $('#universalImagePopup');
    popUp.find('img').attr('src', imgUrl);
    popUp.modal('show');
  });

  var coreModule = (function(){

    var _spinnerRefCount = 0;

    var showWaitAnimationReferenced = function() {
      if( _spinnerRefCount == 0 )
        ;//this.loading('show');
      return ++_spinnerRefCount;
    };

    var hideWaitAnimationReferenced = function() {
      _spinnerRefCount--;
      if( _spinnerRefCount <= 0 )
      ;//this.loading('hide');
      return _spinnerRefCount;
    };

    var waitAnimationReferenced = function(cmd) {
      switch(cmd) {
        case 'show':
          return showWaitAnimationReferenced();
        case 'hide':
          return hideWaitAnimationReferenced();
        default: return null;
      }
    };

    return {
      showWaitAnimationReferenced: showWaitAnimationReferenced,
      hideWaitAnimationReferenced: hideWaitAnimationReferenced,
      waitAnimationReferenced: waitAnimationReferenced
    };
  })();

  if( typeof sdb.GUI === 'undefined' ) {
    sdb.GUI = {};
  }

  $.extend(sdb.GUI, {
    Core: coreModule
  });

  return sdb;
})(typeof ShelfDB !== 'undefined' ? ShelfDB : {}, jQuery);
