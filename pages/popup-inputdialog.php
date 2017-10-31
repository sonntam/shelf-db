<div role="dialog" class="modal fade" id="popupInputDialog" aria-labelledby="popupInputHeader" aria-hidden="true" style="max-width:98%;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="popupInputHeader" name="dialogHeader"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="formInputDialog">
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <h6 name="dialogHeadline" class="ui-title"></h6>
            </div>
            <div class="row">
              <p name="dialogMessage"></p>
            </div>
            <div class="row">
              <div class="col-12 form-group">
                <label for="usertext" name="dialogTextLabel"></label>
                <input class="form-control" type="text" name="dialogValue" value="" placeholder="">
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
                <button type="submit" buttonresult="ok" name="popupOkBtn" class="btn btn-primary btn-block" uilang="ok">
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
