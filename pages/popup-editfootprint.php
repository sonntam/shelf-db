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

    $.mobile.referencedLoading('show');

    evt.stopPropagation();
    evt.preventDefault();

    // Do not upload anything if nothing changed or no new image was selected
    var formData = $(evt.target).formData();

    function postUploadReaction(formData) {

      $.ajax({
        url: '/lib/edit-footprint.php',
        type: 'POST',
        data: formData,
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, jqXHR) {
          $.mobile.referencedLoading('hide');
          if( data.success )
          {
            // Success
            $('#popupFootprintEditDialog #footprintEditForm').trigger("positiveResponse", $.extend(data,{ buttonresult: 'ok'}));
            $('#popupFootprintEditDialog').popup('close');

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
    if( formData['imageFileName'] == "" ) {
      postUploadReaction(formData);
    } else {
      $.ajax({
        url: '/lib/upload-files.php',
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
            $.mobile.referencedLoading('hide');
          }

        },
        error: function(jqXHR, textStatus, errorThrown) {
          // Handle error
          console.log('ERRORS: ' + textStatus + ' ' + errorThrown.message);
          $.mobile.referencedLoading('hide');
        }
      });
    }
  });

  // Upload files
  function uploadFiles(event) {
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

    $.mobile.referencedLoading('show', {
      theme: "a"
    });
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
      url: '/lib/upload-files.php',
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
        $.mobile.referencedLoading('hide');

      },
      error: function(jqXHR, textStatus, errorThrown) {
        // Handle error
        console.log('ERRORS: ' + textStatus + ' ' + errorThrown.message);
        $.mobile.referencedLoading('hide');
      }
    });
  }


</script>

<div data-role="popup" id="popupFootprintEditDialog" data-overlay-theme="a" data-theme="a" data-dismissible="false"> <!-- position: fixed; height: 95%; width: 95%; -->
  <div data-role="header" data-theme="a">
    <h1 name="dialogHeader" style="margin: 0 15px;" uilang="popupFootprintAddHeader"></h1>
  </div>
  <div role="main" class="ui-content" >
    <h3 name="dialogHeadline" class="ui-title" uilang="popupFootprintAddUserAction"></h3>
    <form id="footprintEditForm" data-ajax="false">
      <input type="hidden" name="method" id="method" value="<?php echo $formData['method']; ?>">
      <input type="hidden" name="changeToDefaultImg" id="changeToDefaultImg" value="<?php echo $formData['changeToDefaultImg']; ?>">
      <input type="hidden" name="imageFileName" id="imageFileName" value="">
      <input type="hidden" name="id" id="id" value="<?php echo $formData['id']; ?>">
      <div class="ui-grid-solo">
        <div class="ui-block-a">
          <label for="name" uilang="editPartNewName"></label>
          <input type="text" name="name" id="name" value="<?php echo htmlentities($formData['name']); ?>" uilang="<?php if($formData['method'] == 'copy') echo "value:copyOf:value;"; ?>placeholder:enterName">
        </div>
        <div class="ui-block-a">
          <div style="display: flex; flex-flow: row">
            <div class="ui-shadow" style="text-align: center; background-color: lightgray; flex: 0 0 10em; width: 10em">
              <img id="imgPreview" class="ui-center-element-relative" original-src="<?php echo $formData['imageUrl']; ?>" style="max-width:10em; max-height:10em" src="<?php echo htmlentities($formData['imageUrl']); ?>">
            </div>
            <div class="ui-grid-solo" style="flex: 1; margin-left: 1em; align-self: flex-end">
              <div class="ui-block-a">
                <label for="file" uilang="uploadImageLabel"></label>
                <input id="file" name="file" type="file" value="">
              </div>
              <div class="ui-block-a">
				<?php if( $formData['imageId'] && in_array($formData['method'], array('copy','edit') ) ) { ?>
                <button type="button" class="ui-btn ui-mini" id="resetImg" uilang="resetImage"></button>
				<?php } ?>
                <button type="button" class="ui-btn ui-mini" id="defaultImg" uilang="defaultImage"></button>

              </div>
            </div>
          </div>
        </div>
        <div class="ui-block-a ui-grid-a">
          <div class="ui-block-a"><a href="#" buttonresult="cancel" name="popupCancelBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-b" data-rel="back" uilang="cancel"></a></div>
          <div class="ui-block-b"><button id="popupOkBtn" name="popupOkBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-a" buttonresult="ok" value="ok" type="submit" uilang="<?php echo $formData['method']; ?>"></button></div>
        </div>
      </div>
    </form>
  </div>
</div>
