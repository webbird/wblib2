    <div id="rightblock">
        <div class="element type_fieldset" data-fieldtype="fieldset">fieldset</div>
        <div class="element type_text" data-fieldtype="text">text</div>
        <div class="element type_password" data-fieldtype="password">password</div>
        <div class="element type_select" data-fieldtype="select">select</div>
        <div class="element type_imageselect" data-fieldtype="imageselect">imageselect</div>
        <div class="element type_radiogroup" data-fieldtype="radiogroup">radiogroup</div>
        <div class="element type_textarea" data-fieldtype="textarea">textarea</div>
        <div class="element type_button" data-fieldtype="button">button</div>
        <br /><br />
        <button id="export_save" onclick="saveSettings('{$passthru_url}');" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary">
            {lang="Export / Save"}
        </button>
        <button id="bf_open_existing" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary">
            {lang="Open / upload existing"}
        </button>
        <br /><br /><br />
        <div class="element">{lang="Drag and drop the elements from the list above to the workpane."}</div>
        <div class="element">{lang="To sort the elements, also use drag and drop."}</div>
        <div class="element">{lang="To change the properties of an element, doubleclick on the element and change the settings in the properties table on the left."}</div>
    </div>