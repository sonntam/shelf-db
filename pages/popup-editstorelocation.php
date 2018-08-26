<?php
  require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

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

        $sl = $pdb->StoreLocation()->GetById($id);

        if( $sl ) {
            $formData['name'] = $sl['name'];
        } else {
          return;
        }
      } else {
        return;
      }

      break;

    case 'copy':
      if( isset($_GET['id']) ) {
        // Fetch data
        $id = $_GET['id'];

        $sl = $pdb->StoreLocation()->GetById($id);

        if( $sl ) {
            $formData['name'] = $sl['name'];
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
<script type="text/javascript">

  $('#popupStoreLocationEditDialog #storeLocationEditForm').on('submit', function (evt) {
    debugger;

    evt.stopPropagation();
    evt.preventDefault();

    // Do not upload anything if nothing changed or no new image was selected
    var formData = $(evt.target).formData();

    function postUploadReaction(formData) {
      $.ajax({
        url: ShelfDB.Core.basePath+'lib/edit-storelocation.php',
        type: 'POST',
        data: formData,
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, jqXHR) {
          if( data.success )
          {
            // Success
            $('#popupStoreLocationEditDialog #storeLocationEditForm').trigger("positiveResponse", $.extend(data,{ buttonresult: 'ok'}));
            $('#popupStoreLocationEditDialog').modal('hide');

          } else {
            // Handle error
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          // Handle error
          console.log('ERRORS: ' + textStatus + ' ' + errorThrown.message);
        }
      });
    };

    postUploadReaction(formData);
  });




</script>

<div role="dialog" class="modal fade" id="popupStoreLocationEditDialog" aria-labelledby="popupStoreLocationEditHeader" aria-hidden="true" style="max-width:98%;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="popupStoreLocationEditHeader" uilang="popupStoreLocationAddHeader"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="storeLocationEditForm">
        <input type="hidden" name="method" id="method" value="<?php echo $formData['method']; ?>">
        <input type="hidden" name="changeToDefaultImg" id="changeToDefaultImg" value="<?php echo isset( $formData['changeToDefaultImg'] ) ? $formData['changeToDefaultImg'] : ''; ?>">
        <input type="hidden" name="imageFileName" id="imageFileName" value="">
        <input type="hidden" name="id" id="id" value="<?php echo $formData['id']; ?>">
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="col-12">
                <h6 name="dialogHeadline" class="ui-title" uilang="popupStoreLocationAddUserAction"></h6>
              </div>
            </div>
            <div class="row">
              <div class="col-12 form-group">
                <label for="name" uilang="editPartNewName"></label>
                <input class="form-control" type="text" name="name" id="name" value="<?php echo htmlentities($formData['name']); ?>" uilang="<?php if($formData['method'] == 'copy') echo "value:copyOf:value;"; ?>placeholder:enterName">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="container-fluid">
            <div class="row">
              <div class="col-6">
                <button type="button" buttonresult="cancel" name="popupCancelBtn" class="btn btn-secondary btn-block" data-dismiss="modal" uilang="cancel"></button>
              </div>
              <div class="col-6">
                <button id="popupOkBtn" type="submit" buttonresult="ok" value="ok" name="popupOkBtn" class="btn btn-primary btn-block" uilang="<?php echo $formData['method']; ?>">
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
