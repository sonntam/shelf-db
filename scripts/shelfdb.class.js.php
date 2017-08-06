<?php
  require_once(dirname(__DIR__).'/classes/shelfdb.class.php');
?>
$.shelfdb = {

  getSupplierByIdAsync: function(args) {

    opts = {
      id: null,
      partNr: null,
      done: null
    };

    $.extend(opts, args);

    $.mobile.referencedLoading('show');
    $.ajax({
      url: '<?php echo $pdb->RelRoot(); ?>lib/json.suppliers.php',
      type: 'POST',
      dataType: 'json',
      data: {
        id: opts.id,
        partNr: opts.partNr
      }
    }).done(function(data) {
      if( opts.done )
        opts.done(data);

      $.mobile.referencedLoading('hide');
    });
  }

};
