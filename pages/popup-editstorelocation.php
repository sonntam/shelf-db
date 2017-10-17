<?php
  require_once(dirname(__DIR__).'/classes/shelfdb.class.php');

  $_GET += array("id" => null);
  $_GET += array("method" => "add");

  $formData['method'] = $_GET['method'];
  $formData['id'] = $_GET['id'];

  // What should be done? Adding? Editing existing element?
  switch( strtolower($_GET['method']) ) {
    case 'edit':
      if( isset($_GET['id']) ) {
        // Fetch data
        $id = $_GET['id'];

        $storelocation = $pdb->StoreLocation()->GetById($id);

        if( $storelocation ) {
          $formData['name'] = $storelocation['name'];
        } else {
          return;
        }
      } else {
        return;
      }

      break;

    case 'add':
      $formData['name'] = '';
      break;

    default:
      return;
  }

?>
<script>

  $('#popupStoreLocationEditDialog #storelocationEditForm').on('submit', function (evt) {

    $.mobile.referencedLoading('show');

    evt.stopPropagation();
    evt.preventDefault();

    // Do not upload anything if nothing changed or no new image was selected
    var formData = $(evt.target).formData();

    function postUploadReaction(formData) {

      $.ajax({
        url: '/lib/edit-storelocation.php',
        type: 'POST',
        data: formData,
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, jqXHR) {
          $.mobile.referencedLoading('hide');
          if( data.success )
          {
            // Success
            $('#popupStoreLocationEditDialog #storelocationEditForm').trigger("positiveResponse", $.extend(data,{ buttonresult: 'ok'}));
            $('#popupStoreLocationEditDialog').popup('close');

          } else {
            // Handle error
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          // Handle error
          console.log('ERRORS: ' + textStatus + ' ' + errorThrown.message);
          $.mobile.referencedLoading('hide');
        }
      });
    };

    // Is there something to upload
    postUploadReaction(formData);
  });

</script>

<div data-role="popup" id="popupStoreLocationEditDialog" data-overlay-theme="a" data-theme="a" data-dismissible="false"> <!-- position: fixed; height: 95%; width: 95%; -->
  <div data-role="header" data-theme="a">
    <h1 name="dialogHeader" style="margin: 0 15px;" uilang="popupStoreLocation<?php echo ucfirst(strtolower($formData['method'])); ?>Header"></h1>
  </div>
  <div role="main" class="ui-content" >
    <h3 name="dialogHeadline" class="ui-title" uilang="popupStoreLocation<?php echo ucfirst(strtolower($formData['method'])); ?>UserAction"></h3>
    <form id="storelocationEditForm" data-ajax="false">
      <input type="hidden" name="method" id="method" value="<?php echo $formData['method']; ?>">
      <input type="hidden" name="id" id="id" value="<?php echo $formData['id']; ?>">
      <div class="ui-grid-solo">
        <div class="ui-block-a">
          <label for="name" uilang="editPartNewName"></label>
          <input type="text" name="name" id="name" value="<?php echo htmlentities($formData['name']); ?>" uilang="<?php if($formData['method'] == 'copy') echo "value:copyOf:value;"; ?>placeholder:enterName">
        </div>
        <div class="ui-block-a ui-grid-a">
          <div class="ui-block-a"><a href="#" buttonresult="cancel" name="popupCancelBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-b" data-rel="back" uilang="cancel"></a></div>
          <div class="ui-block-b"><button id="popupOkBtn" name="popupOkBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-a" buttonresult="ok" value="ok" type="submit" uilang="<?php echo $formData['method']; ?>"></button></div>
        </div>
      </div>
    </form>
  </div>
</div>
