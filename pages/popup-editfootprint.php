<?php
  require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  $_GET += array("id" => null);
  $_GET += array("method" => "add");

  $formData['method'] = $_GET['method'];
  $formData['changeToDefaultImg'] = "false";
  $formData['id'] = $_GET['id'];
  $formData['imageId'] = null;

  // What should be done? Adding? Editing existing element?
  switch( strtolower($_GET['method']) ) {
    case 'edit':
      if( isset($_GET['id']) ) {
        // Fetch data
        $id = $_GET['id'];

        $footprint = $pdb->Footprint()->GetById($id);

        if( $footprint ) {
            $formData['name'] = $footprint['name'];
            $formData['imageUrl'] = "/img/footprint/".$footprint['pict_fname'];
			      $formData['imageId'] = $footprint['pict_id'];
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

        $footprint = $pdb->Footprint()->GetById($id);

        if( $footprint ) {
            $formData['name'] = $footprint['name'];
            $formData['imageUrl'] = "/img/footprint/".$footprint['pict_fname'];
			$formData['imageId'] = $supplier['pict_id'];
        } else {
          return;
        }
      } else {
        return;
      }

      break;

    case 'add':
      $formData['name'] = '';
      $formData['imageUrl'] = '/img/footprint/default.png';
      break;

    default:
      return;
  }

?>
<script>
  $('#popupFootprintEditDialog #resetImg').click(function(evt) {
      evt.preventDefault();
      evt.stopPropagation();

      $('#popupFootprintEditDialog #file').val("");
      $('#popupFootprintEditDialog #imgPreview').attr('src', $('#popupFootprintEditDialog #imgPreview').attr('original-src'));
      $('#popupFootprintEditDialog #changeToDefaultImg').val('false');
      $('#popupFootprintEditDialog #imageFileName').val("");
  });

  $('#popupFootprintEditDialog #defaultImg').click(function(evt) {
      evt.preventDefault();
      evt.stopPropagation();

      $('#popupFootprintEditDialog #file').val("");
      $('#popupFootprintEditDialog #imgPreview').attr('src', '/img/footprint/default.png');
      $('#popupFootprintEditDialog #changeToDefaultImg').val('true');
      $('#popupFootprintEditDialog #imageFileName').val("");

  });

  $('#popupFootprintEditDialog input[type=file]').on('change', uploadFiles);

  $('#popupFootprintEditDialog #footprintEditForm').on('submit', function (evt) {
    debugger;

    evt.stopPropagation();
    evt.preventDefault();

    // Do not upload anything if nothing changed or no new image was selected
    var formData = $(evt.target).formData();

    function postUploadReaction(formData) {
      debugger;
      $.ajax({
        url: ShelfDB.Core.basePath+'lib/edit-footprint.php',
        type: 'POST',
        data: formData,
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, jqXHR) {
          if( data.success )
          {
            // Success
            $('#popupFootprintEditDialog #footprintEditForm').trigger("positiveResponse", $.extend(data,{ buttonresult: 'ok'}));
            $('#popupFootprintEditDialog').modal('hide');

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

    // Is there something to upload
    if( formData['imageFileName'] == "" ) {
      debugger;
      postUploadReaction(formData);
    } else {
      debugger;
      $.ajax({
        url: ShelfDB.Core.basePath+'lib/upload-files.php',
        type: 'POST',
        data: {
          tempFilename: $('#popupFootprintEditDialog #imgPreview').attr('src'),
          type: 'moveTempToTarget',
          target: 'footprintImage'
        },
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, jqXHR) {
          if( typeof data.error === 'undefined') {
            // Success -> create new footprint entry in database
            postUploadReaction(formData);
          } else {
            // Handle error
          }

        },
        error: function(jqXHR, textStatus, errorThrown) {
          // Handle error
          console.log('ERRORS: ' + textStatus + ' ' + errorThrown.message);
        }
      });
    }
  });

  // Upload files
  function uploadFiles(event) {
    debugger;
    files = event.target.files;

    /*event.stopPropagation();
    event.preventDefault();*/
    if( files.length <= 0 ) {
      //$('#imgPreview').attr('src','');
      event.stopPropagation();
      event.preventDefault();
      return;
    }

    $('#popupFootprintEditDialog #changeToDefaultImg').val('false');

    // Add the files
    var data = new FormData();
    $.each(files, function(key, value) {
      data.append(key,value);
    });

    $.each({
      type: 'uploadToTemp',
      target: 'tempImage'
    }, function(key, value) {
      data.append(key,value);
    });

    $.ajax({
      url: ShelfDB.Core.basePath+'lib/upload-files.php',
      type: 'POST',
      data: data,
      cache: false,
      dataType: 'json',
      processData: false,
      contentType: false,
      success: function(data, textStatus, jqXHR) {
        if( typeof data.error === 'undefined')
        {
          // Success
          $('#popupFootprintEditDialog #imgPreview').attr('src', data.files[0]['fullpath']);
          $('#popupFootprintEditDialog #imageFileName').val(data.files[0]['name']);
        } else {
          // Handle error
        }

      },
      error: function(jqXHR, textStatus, errorThrown) {
        // Handle error
        console.log('ERRORS: ' + textStatus + ' ' + errorThrown.message);
      }
    });
  }


</script>

<div role="dialog" class="modal fade" id="popupFootprintEditDialog" aria-labelledby="popupFootprintEditHeader" aria-hidden="true" style="max-width:98%;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="popupFootprintEditHeader" uilang="popupFootprintAddHeader"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="footprintEditForm">
        <input type="hidden" name="method" id="method" value="<?php echo $formData['method']; ?>">
        <input type="hidden" name="changeToDefaultImg" id="changeToDefaultImg" value="<?php echo $formData['changeToDefaultImg']; ?>">
        <input type="hidden" name="imageFileName" id="imageFileName" value="">
        <input type="hidden" name="id" id="id" value="<?php echo $formData['id']; ?>">
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="col-12">
                <h6 name="dialogHeadline" class="ui-title" uilang="popupFootprintAddUserAction"></h6>
              </div>
            </div>
            <div class="row">
              <div class="col-12 form-group">
                <label for="name" uilang="editPartNewName"></label>
                <input class="form-control" type="text" name="name" id="name" value="<?php echo htmlentities($formData['name']); ?>" uilang="<?php if($formData['method'] == 'copy') echo "value:copyOf:value;"; ?>placeholder:enterName">
              </div>
            </div>
            <div class="row">
              <div class="col-auto text-center">
                <img id="imgPreview" original-src="<?php echo $formData['imageUrl']; ?>" style="background-color: lightgray; width:10em; height:10em; object-fit: contain;" src="<?php echo htmlentities($formData['imageUrl']); ?>">
              </div>
              <div class="col">
                <div class="row">
                  <div class="col">
                    <label for="file" uilang="uploadImageLabel"></label>
                    <input class="form-control" id="file" name="file" type="file" value="">
                  </div>
                </div>
                <div class="row">
            <?php if( $formData['imageId'] && in_array($formData['method'], array('copy','edit') ) ) { ?>
                  <div class="col-12 pb-1">
                    <button type="button" class="btn btn-sm btn-secondary btn-sm btn-block" id="resetImg" uilang="resetImage"></button>
                  </div>
            <?php } ?>
                  <div class="col-12">
                    <button type="button" class="btn btn-sm btn-secondary btn-sm btn-block" id="defaultImg" uilang="defaultImage"></button>
                  </div>
                </div>
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
