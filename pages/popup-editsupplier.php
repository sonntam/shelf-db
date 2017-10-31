<?php
  require_once(dirname(__DIR__).'/classes/ShelfDB.class.php');

  $data = array_replace_recursive( array(
    "method" => "add"
  ), $_GET, $_POST);

  $formData['method'] = $data['method'];
  $formData['changeToDefaultImg'] = "false";
  $formData['id'] = $data['id'];
  $formData['imageId'] = null;

  // What should be done? Adding? Editing existing element?
  switch( strtolower($data['method']) ) {
    case 'edit':
      if( isset($data['id']) ) {
        // Fetch data
        $id = $data['id'];

        $supplier = $pdb->Supplier()->GetById($id);

        if( $supplier ) {
            $formData['name'] = $supplier['name'];
            $formData['imageUrl'] = "/img/supplier/".$supplier['pict_fname'];
            $formData['imageId'] = $supplier['pict_id'];
            $formData['urlTemplate']  = $supplier['urlTemplate'];
        } else {
          return;
        }
      } else {
        return;
      }

      break;

    case 'copy':
      if( isset($data['id']) ) {
        // Fetch data
        $id = $data['id'];

        $supplier = $pdb->Supplier()->GetById($id);

        if( $supplier ) {
            $formData['name'] = $supplier['name'];
            $formData['imageUrl'] = "/img/supplier/".$supplier['pict_fname'];
            $formData['urlTemplate']  = $supplier['urlTemplate'];
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
      $formData['imageUrl'] = '/img/supplier/default.png';
      $formData['urlTemplate'] = '';
      break;

    default:
      return;
  }

?>
<script>
  $('#popupSupplierEditDialog #resetImg').click(function(evt) {
      evt.preventDefault();
      evt.stopPropagation();

      $('#popupSupplierEditDialog #file').val("");
      $('#popupSupplierEditDialog #imgPreview').attr('src', $('#popupSupplierEditDialog #imgPreview').attr('original-src'));
      $('#popupSupplierEditDialog #changeToDefaultImg').val('false');
      $('#popupSupplierEditDialog #imageFileName').val("");
  });

  $('#popupSupplierEditDialog #defaultImg').click(function(evt) {
      evt.preventDefault();
      evt.stopPropagation();

      $('#popupSupplierEditDialog #file').val("");
      $('#popupSupplierEditDialog #imgPreview').attr('src', '/img/supplier/default.png');
      $('#popupSupplierEditDialog #changeToDefaultImg').val('true');
      $('#popupSupplierEditDialog #imageFileName').val("");

  });

  $('#popupSupplierEditDialog input[type=file]').on('change', uploadFiles);

  $('#popupSupplierEditDialog #supplierEditForm').on('submit', function (evt) {

    evt.stopPropagation();
    evt.preventDefault();

    // Do not upload anything if nothing changed or no new image was selected
    var formData = $(evt.target).formData();

    function postUploadReaction(formData) {
      $.ajax({
        url: ShelfDB.Core.basePath+'lib/edit-supplier.php',
        type: 'POST',
        data: formData,
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, jqXHR) {
          if( data.success )
          {
            // Success
            $('#popupSupplierEditDialog #supplierEditForm').trigger("positiveResponse", $.extend(data,{ buttonresult: 'ok'}));
            $('#popupSupplierEditDialog').modal('hide');

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
      postUploadReaction(formData);
    } else {
      $.ajax({
        url: ShelfDB.Core.basePath+'lib/upload-files.php',
        type: 'POST',
        data: {
          tempFilename: $('#popupSupplierEditDialog #imgPreview').attr('src'),
          type: 'moveTempToTarget',
          target: 'supplierImage'
        },
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, jqXHR) {
          if( typeof data.error === 'undefined') {
            // Success -> create new supplier entry in database
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

  $('#popupSupplierEditDialog .ui-simple-popup').on('click', function(evt) {
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

    $('#popupSupplierEditDialog #changeToDefaultImg').val('false');

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
          $('#popupSupplierEditDialog #imgPreview').attr('src', data.files[0]['fullpath']);
          $('#popupSupplierEditDialog #imageFileName').val(data.files[0]['name']);
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

<div role="dialog" class="modal fade" id="popupSupplierEditDialog" aria-labelledby="popupSupplierEditHeader" aria-hidden="true" style="max-width:98%;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="popupSupplierEditHeader" uilang="<?php
          echo ($data['method'] == 'edit' ? "popupSupplierEditHeader" : "popupSupplierAddHeader");
          ?>"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="supplierEditForm">
        <input type="hidden" name="method" id="method" value="<?php echo $formData['method']; ?>">
        <input type="hidden" name="changeToDefaultImg" id="changeToDefaultImg" value="<?php echo $formData['changeToDefaultImg']; ?>">
        <input type="hidden" name="imageFileName" id="imageFileName" value="">
        <input type="hidden" name="id" id="id" value="<?php echo $formData['id']; ?>">
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="col-12">
                <h6 name="dialogHeadline" class="ui-title" uilang="<?php
                  echo ($data['method'] == 'edit' ? "popupSupplierEditUserAction" : "popupSupplierAddUserAction");
                  ?>"></h6>
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
                <img id="imgPreview" original-src="<?php echo $formData['imageUrl']; ?>" style="background-color: lightgray; object-fit: contain; width:10em; height:10em" src="<?php echo htmlentities($formData['imageUrl']); ?>">
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
            <div class="row pt-3">
              <div class="col-12">
                <label style="display: inline;" for="urlTemplate" uilang="editSupplierArticleUrl"></label>
                <a tabindex="0" uilang="data-content:editSupplierArticleUrlHint" role="button" link="#popupInfoSupplierUrl" data-toggle="popover"
                data-trigger="focus" class="btn btn-secondary btn-sm"
                title=""><i class="fa fa-info"></i></a>
              </div>
            </div>
            <div class="row pt-1">
              <div class="col-12">
                <input class="form-control" type="text" name="urlTemplate" id="urlTemplate" value="<?php echo htmlentities($formData['urlTemplate']); ?>" uilang="placeholder:enterUrl">
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
<script type="text/javascript">
  $(function() {
    $('#popupSupplierEditDialog [data-toggle=popover]').popover();
  });
</script>
