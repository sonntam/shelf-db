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

// Get data of a html form
$.fn.formData = function() {
  // https://stackoverflow.com/a/24012884
  return $(this).serializeArray().reduce(function(obj, item) {
    obj[item.name] = item.value;
    return obj;
  }, {});
};

// Show second popup over another one
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

// Show loading spinner with refcount
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
