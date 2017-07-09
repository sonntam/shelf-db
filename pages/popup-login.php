<script>
  $('#loginForm').validate();
</script>
<div data-role="popup" id="popupLoginDialog" data-overlay-theme="b" data-theme="b" data-dismissible="false" style="max-width:98%;">
    <div data-role="header" data-theme="a">
      <h1 style="margin: 0 15px;" uilang="login"></h1>
    </div>
    <div role="main" class="ui-content">
      <h3 uilang="login" class="ui-title"></h3>
      <p uilang="loginHint"></p>
      <form id="loginForm" data-ajax="false">
        <label for="username" uilang="username"></label>
        <input type="text" name="username" id="username" value="" uilang="placeholder:enterUsername" data-theme="a" minlength="2" required>
        <label for="password" uilang="password"></label>
        <input type="password" name="password" id="password" value="" uilang="placeholder:enterPassword" data-theme="a" required>
        <input type="hidden" name="method" value="authenticate">
        <div class="ui-grid-a">
          <div class="ui-block-a">
            <a href="#" buttonresult="cancel" name="popupCancelBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-b" data-rel="back" uilang="cancel"></a>
          </div>
          <div class="ui-block-b">
            <button type="submit" buttonresult="ok" name="popupOkBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-a" data-rel="back" data-transition="flow" uilang="ok">
          </div>
        </div>
      </form>
</div>
