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
  $formData['originalFileUrl'] = '/img/supplier/default.png';
  $formData['method']   = $data['method'];

?>
<script type="text/javascript">
  (function(){
    let itemType     = $('input[name=type]').val();
    let uploadTarget = itemType == 'picture' ? 'tempImage' : 'tempFile';
    let targetType   = itemType == 'picture' ? 'partImage' : 'datasheetFile';
    $('#popupUploadFileDialog input[type=file]').on('change', function(evt) {
      debugger;
      ShelfDB.Core.uploadFile({
        uploadTarget: uploadTarget,
        error: function(textStatus, data) {
          alert("Something went wrong:\n"+data.error)
        },
        success: function(data, textStatus, jqXHR) {
          if( typeof data.error === 'undefined') {
            // Success
            $('#popupUploadFileDialog #imgPreview').attr('src', data.files[0]['fullpath']);
            $('#popupUploadFileDialog #fileName').val(data.files[0]['name']);
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
      if( formData['fileName'] == "" ) {
        // Nothing selected, do nothing
        return;
      } else {
        ShelfDB.Core.moveUploadedFile({
          uploadTarget: uploadTarget,
          tempFilename: formData['fileName'],
          targetType: targetType,
          success: function(data) {
            // Add picture to part
            debugger;
            if( itemType == "picture" ) {
              ShelfDB.Part.addPartPicture(formData.id, formData.fileName, function(data) {
                debugger;
                $('#popupUploadFileDialog #uploadFileForm').trigger("positiveResponse", $.extend(data,{ buttonresult: 'ok'}));
                $('#popupUploadFileDialog').modal('hide');
              });
            } else if( itemType == "datasheet" ) {
              ShelfDB.Part.addPartDatasheet(formData.id, formData.fileName, function(data) {
                debugger;
                $('#popupUploadFileDialog #uploadFileForm').trigger("positiveResponse", $.extend(data,{ buttonresult: 'ok'}));
                $('#popupUploadFileDialog').modal('hide');
              });
            }
          }
        }, evt);
      }
    });
  })();
  //# sourceURL=/pages/popup-uploadfile.php
</script>

<?php
  echo $pdb->RenderTemplate("popup-uploadfile.twig", array(
    "type" => $data["type"],
    "method" => $formData["method"],
    "originalFile" => $formData["originalFileUrl"],
    "fileId" => $formData["id"]
  ));
  return;
?>
