<?php
  require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  $data = array_replace_recursive( array(
    "id" => null,
    "method" => null,
    "itemtype" => null, // "part"
    "type" => null, // "picture", "datasheet"
  ), $_GET, $_POST);

  if( !$data["id"] || !$data["method"] || !$data["itemtype"] || !$data["type"] )
    return;

  $formData['id'] = $data['id'];
  $formData['imageId'] = null;
  $formData['imageUrl'] = '/img/supplier/default.png';
  $formData['method'] = $data['method'];

?>
<script>
  $('#popupUploadFileDialog input[type=file]').on('change', uploadFiles);

  $('#popupUploadFileDialog #uploadFileForm').on('submit', function (evt) {

    $.mobile.referencedLoading('show');

    evt.stopPropagation();
    evt.preventDefault();

    // Do not upload anything if nothing changed or no new image was selected
    var formData = $(evt.target).formData();

    function postUploadReaction(formData) {
      $.ajax({
        url: '/lib/edit-part.php',
        type: 'POST',
        data: formData,
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, jqXHR) {
          $.mobile.referencedLoading('hide');
          if( data.success )
          {
            // Success
            $('#popupUploadFileDialog #uploadFileForm').trigger("positiveResponse", $.extend(data,{ buttonresult: 'ok'}));
            $('#popupUploadFileDialog').popup('close');

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
          tempFilename: $('#popupUploadFileDialog #imgPreview').attr('src'),
          type: 'moveTempToTarget',
          target: 'partImage' // TODO: Also for datasheets
        },
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, jqXHR) {
          if( typeof data.error === 'undefined') {
            // Success -> create new supplier entry in database
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

  $('#popupUploadFileDialog .ui-simple-popup').on('click', function(evt) {
    evt.preventDefault();
    evt.stopPropagation();
    var id = $(evt.currentTarget).attr('link');
    $(id).simpledialog2({
      mode: 'blank',
      themeDialog: 'a',
      headerText: false,
      headerClose: false,
      dialogAllow: false,
      forceInput: false,
      blankContent: true,
      showModal: false,
        zindex: 2000
    });
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
          $('#popupUploadFileDialog #imgPreview').attr('src', data.files[0]['fullpath']);
          $('#popupUploadFileDialog #imageFileName').val(data.files[0]['name']);
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

  //# sourceURL=/pages/popup-uploadfile.php
</script>

<div data-role="popup" id="popupUploadFileDialog" data-overlay-theme="a" data-theme="a" data-dismissible="false"> <!-- position: fixed; height: 95%; width: 95%; -->
  <div data-role="header" data-theme="a">
    <h1 name="dialogHeader" style="margin: 0 15px;" uilang="popupUploadFile"></h1>
  </div>
  <div role="main" class="ui-content" >
    <h3 name="dialogHeadline" class="ui-title" uilang="popupUploadFileUserAction"></h3>
    <form id="uploadFileForm" data-ajax="false">
      <input type="hidden" name="imageFileName" id="imageFileName" value="">
      <input type="hidden" name="id" id="id" value="<?php echo $formData['id']; ?>">
      <input type="hidden" name="method" id="method" value="<?php echo $formData['method']; ?>">
      <div class="ui-grid-solo">
        <div class="ui-block-a">
          <div style="display: flex; flex-flow: row">
            <div class="ui-shadow" style="text-align: center; background-color: lightgray; flex: 0 0 10em; width: 10em; height: 10em">
              <img id="imgPreview" class="ui-center-element-relative" original-src="<?php echo $formData['imageUrl']; ?>" style="max-width:10em; max-height:10em" src="<?php echo htmlentities($formData['imageUrl']); ?>">
            </div>
            <div class="ui-grid-solo" style="flex: 1; margin-left: 1em; align-self: flex-end">
              <div class="ui-block-a">
                <label for="file" uilang="uploadImageLabel"></label>
                <input id="file" name="file" type="file" value="">
              </div>
            </div>
          </div>
        </div>
        <div class="ui-block-a ui-grid-a">
          <div class="ui-block-a"><a href="#" buttonresult="cancel" name="popupCancelBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-b" data-rel="back" uilang="cancel"></a></div>
          <div class="ui-block-b"><button id="popupOkBtn" name="popupOkBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-a" buttonresult="ok" value="ok" type="submit" uilang="ok"></button></div>
        </div>
      </div>
    </form>
  </div>
</div>
