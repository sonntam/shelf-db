// This is the ShelfDB GUI extension
var ShelfDB = (function(sdb,$) {

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
