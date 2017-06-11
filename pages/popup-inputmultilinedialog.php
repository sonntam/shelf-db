<div data-role="popup" id="popupInputMultilineDialog" data-overlay-theme="b" data-theme="b" data-dismissible="false" style="max-width:500px;">
    <div data-role="header" data-theme="a">
    <h1 name="dialogHeader" style="margin: 0 15px;"></h1>
    </div>
    <div role="main" class="ui-content">
        <h3 name="dialogHeadline" class="ui-title"></h3>
        <p name="dialogMessage"></p>
        <label for="usertext" class="ui-hidden-accessible" name="dialogTextLabel"></label>
        <textarea id="usertext" name="dialogText" data-theme="a"></textarea>
        <a href="#" buttonresult="cancel" name="popupCancelBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b" data-rel="back" uilang="cancel"></a>
        <a href="#" buttonresult="ok" name="popupOkBtn" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-a" data-rel="back" data-transition="flow" uilang="ok"></a>
    </div>
</div>
