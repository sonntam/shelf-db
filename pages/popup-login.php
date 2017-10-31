<!-- Bootstrap modal -->
<div id="popupLoginDialog" class="modal fade" role="dialog" aria-labelledby="popupLoginHeader" aria-hidden="true" style="max-width:98%;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="popupLoginHeader" uilang="login"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="loginForm">
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <p uilang="loginHint"></p>
            </div>
            <div class="row">
              <div class="col-12 form-group">
                <label for="username" uilang="username"></label>
                <input class="form-control" type="text" name="username" id="username" value="" uilang="placeholder:enterUsername" minlength="2" required>
              </div>
            </div>
            <div class="row">
              <div class="col-12 form-group">
                <label for="password" uilang="password"></label>
                <input class="form-control" type="password" name="password" id="password" value="" uilang="placeholder:enterPassword" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="method" value="authenticate">
          <!--<button type="button" buttonresult="cancel" name="popupCancelBtn" class="btn btn-secondary" data-dismiss="modal" uilang="cancel"></button>-->
          <div class="container-fluid">
            <div class="row">
              <div class="col-12">
                <button type="submit" buttonresult="ok" name="popupOkBtn" class="btn btn-success btn-block" uilang="login"></button>
              </div>
            </div>
            <div class="row">
              <div class="col-12 text-right">
                <button type="button" class="btn btn-link" style="font-size: 90%" uilang="forgotPasswordLink"></button>
              </div>
            </div>
        </div>
        </div>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript">
  $('#loginForm').validate();
</script>
