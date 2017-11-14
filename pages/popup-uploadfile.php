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

  $formData['id']       = $data['id'];
  $formData['imageId']  = null;
  $formData['imageUrl'] = '/img/supplier/default.png';
  $formData['method']   = $data['method'];

?>
<script type="text/javascript">
  $('#popupUploadFileDialog input[type=file]').on('change', function(evt) {
    ShelfDB.Core.uploadFile({
      uploadTarget: 'tempImage',
      success: function(data, textStatus, jqXHR) {
        if( typeof data.error === 'undefined') {
          // Success
          $('#popupUploadFileDialog #imgPreview').attr('src', data.files[0]['fullpath']);
          $('#popupUploadFileDialog #imageFileName').val(data.files[0]['name']);
        } else {
          // Handle error
        }
      }
    }, evt);
  });

  $('#popupUploadFileDialog #uploadFileForm').on('submit', function (evt) {
    debugger;
    evt.stopPropagation();
    evt.preventDefault();

    // Do not upload anything if nothing changed or no new image was selected
    var formData = $(evt.target).formData();

    // Is there something to upload
    if( formData['imageFileName'] == "" ) {
      // Nothing selected, do nothing
      return;
    } else {
      ShelfDB.Core.moveUploadedFile({
        uploadTarget: 'tempImage',
        tempFilename: formData['imageFileName'],
        targetType: 'partImage',
        success: function(data) {
          // Add picture to part
          debugger;
          ShelfDB.Part.addPartPicture(formData.id, formData.imageFileName, function(data) {
            debugger;
            $('#popupUploadFileDialog #uploadFileForm').trigger("positiveResponse", $.extend(data,{ buttonresult: 'ok'}));
            $('#popupUploadFileDialog').modal('hide');
          });
        }
      }, evt);
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

  //# sourceURL=/pages/popup-uploadfile.php
</script>

<div role="dialog" class="modal fade" id="popupUploadFileDialog" aria-labelledby="popupUploadFileHeader" aria-hidden="true" style="max-width:98%;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="popupUploadFileHeader" uilang="popupUploadFile"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="uploadFileForm">
        <input type="hidden" name="method" id="method" value="<?php echo $formData['method']; ?>">
        <input type="hidden" name="imageFileName" id="imageFileName" value="">
        <input type="hidden" name="id" id="id" value="<?php echo $formData['id']; ?>">
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="col-12">
                <h6 name="dialogHeadline" class="ui-title" uilang="popupUploadFileUserAction"></h6>
              </div>
            </div>
            <div class="row">
              <div class="col-auto text-center">
                <img id="imgPreview" original-src="<?php echo $formData['imageUrl']; ?>" style="background-color: lightgray; object-fit: contain; width:10em; height:10em" src="<?php echo htmlentities($formData['imageUrl']); ?>">
              </div>
              <div class="col">
                <div class="row">
                  <div class="col">
                    <label for="file" uilang="uploadImageLabel"></label>
                    <input class="form-control" id="file" name="file" type="file" value="">
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
                <button id="popupOkBtn" type="submit" buttonresult="ok" value="ok" name="popupOkBtn" class="btn btn-primary btn-block" uilang="upload">
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript">
  $(function() {
    $('#popupUploadFileDialog [data-toggle=popover]').popover();
  });
</script>
