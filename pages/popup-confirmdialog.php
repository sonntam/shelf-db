<div role="dialog" class="modal fade" id="popupConfirmDialog" aria-labelledby="popupConfirmHeader" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="popupInputHeader" name="dialogHeader"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="container-fluid">
          <div class="row">
            <h6 uilang="areYouSureQuestion" class="ui-title"></h6>
          </div>
          <div class="row">
            <p name="dialogText"></p>
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
              <button type="button" buttonresult="ok" name="popupOkBtn" class="btn btn-primary btn-block" data-dismiss="modal" uilang="ok">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
