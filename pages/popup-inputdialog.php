<div data-role="popup" id="popupInputDialog" data-overlay-theme="b" data-theme="b" data-dismissible="false" style="min-width:20em; max-width:98%;">
    <div data-role="header" data-theme="a">
    <h1 name="dialogHeader" style="margin: 0 15px;"></h1>
    </div>
    <div role="main" class="ui-content">
        <h3 name="dialogHeadline" class="ui-title"></h3>
        <p name="dialogMessage"></p>
        <form id="formInputDialog" data-ajax="false">
          <label for="usertext" class="ui-hidden-accessible" name="dialogTextLabel"></label>
          <input type="text" name="dialogValue" value="" placeholder="" data-theme="a">
          <div class="ui-grid-a">
            <div class="ui-block-a">
              <a href="#" buttonresult="cancel" name="popupCancelBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-b" data-rel="back" uilang="cancel"></a>
            </div>
            <div class="ui-block-b">
              <button type="submit" buttonresult="ok" name="popupOkBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-a" data-rel="back" data-transition="flow" uilang="ok">
              <!--<a href="#" buttonresult="ok" name="popupOkBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-a" data-rel="back" data-transition="flow" uilang="ok"></a>-->
            </div>
          </div>
        </form>
    </div>
</div>
