<?php
  require_once(dirname(__DIR__).'/classes/partdatabase.class.php');

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

        $supplier = $pdb->Suppliers()->GetById($id);

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

        $supplier = $pdb->Suppliers()->GetById($id);

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

    $.mobile.referencedLoading('show');

    evt.stopPropagation();
    evt.preventDefault();

    // Do not upload anything if nothing changed or no new image was selected
    var formData = $(evt.target).formData();

    function postUploadReaction(formData) {
      $.ajax({
        url: '/lib/edit-supplier.php',
        type: 'POST',
        data: formData,
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, jqXHR) {
          $.mobile.referencedLoading('hide');
          if( data.success )
          {
            // Success
            $('#popupSupplierEditDialog #supplierEditForm').trigger("positiveResponse", $.extend(data,{ buttonresult: 'ok'}));
            $('#popupSupplierEditDialog').popup('close');

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
          $('#popupSupplierEditDialog #imgPreview').attr('src', data.files[0]['fullpath']);
          $('#popupSupplierEditDialog #imageFileName').val(data.files[0]['name']);
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

<div data-role="popup" id="popupSupplierEditDialog" data-overlay-theme="a" data-theme="a" data-dismissible="false"> <!-- position: fixed; height: 95%; width: 95%; -->
  <div data-role="header" data-theme="a">
    <h1 name="dialogHeader" style="margin: 0 15px;" uilang="<?php
      echo ($data['method'] == 'edit' ? "popupSupplierEditHeader" : "popupSupplierAddHeader");
      ?>"></h1>
  </div>
  <div role="main" class="ui-content" >
    <h3 name="dialogHeadline" class="ui-title" uilang="<?php
      echo ($data['method'] == 'edit' ? "popupSupplierEditUserAction" : "popupSupplierAddUserAction");
      ?>"></h3>
    <form id="supplierEditForm" data-ajax="false">
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
            <div class="ui-shadow" style="text-align: center; background-color: lightgray; flex: 0 0 10em; width: 10em; height: 10em">
              <img id="imgPreview" class="ui-center-element-relative" original-src="<?php echo $formData['imageUrl']; ?>" style="max-width:10em; max-height:10em" src="<?php echo htmlentities($formData['imageUrl']); ?>">
            </div>
            <div class="ui-grid-solo" style="flex: 1; margin-left: 1em; align-self: flex-end">
              <div class="ui-block-a">
                <label for="file" uilang="uploadImageLabel">Bild hochladen</label>
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
        <div class="ui-block-a">
          <!--<div data-role="collapsible" data-collapsed-icon="info" data-expanded-icon="info" data-collapsed="true">-->
            <!--<h4>-->
              <label style="display: inline;" for="urlTemplate" uilang="editSupplierArticleUrl"></label>
            <!--</h4>-->
            <a link="#popupInfoSupplierUrl"
            class="ui-simple-popup ui-btn ui-alt-icon ui-nodisc-icon ui-btn-inline ui-icon-info ui-btn-icon-notext my-tooltip-btn"
            title="Learn more" uilang="moreInfo"></a>
            <div data-role="popup" id="popupInfoSupplierUrl" class="ui-content" data-theme="a" style="max-width:350px;">
              <p style="margin: 1em; text-align: justify" uilang="editSupplierArticleUrlHint"></p>
            </div>

          <!--</div>-->
          <input type="text" name="urlTemplate" id="urlTemplate" value="<?php echo htmlentities($formData['urlTemplate']); ?>" uilang="placeholder:enterUrl">
        </div>
        <div class="ui-block-a ui-grid-a">
          <div class="ui-block-a"><a href="#" buttonresult="cancel" name="popupCancelBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-b" data-rel="back" uilang="cancel"></a></div>
          <div class="ui-block-b"><button id="popupOkBtn" name="popupOkBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-a" buttonresult="ok" value="ok" type="submit" uilang="<?php echo $formData['method']; ?>"></button></div>
        </div>
      </div>
    </form>
  </div>
</div>
