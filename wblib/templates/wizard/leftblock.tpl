    <div id="leftblock">
        <form id="item_properties" style="display:none;">
        <fieldset class="rounded">
            <legend class="rounded">{lang="Item properties"}</legend>
            <span class="banner">
                <span class="label">{lang="Item ID"}:</span> <span id="item_id"></span><br />
                <span class="label">{lang="Type"}:</span> <span id="item_type"></span>
            </span><br />
            <div>
              <label for="item_name">{lang="Fieldname"}:</label>
                <input type="text" name="item_name" id="item_name" value="" /><br />
            </div>
            <div id="item_label_div">
              <label for="item_label">{lang="Label"}:</label>
                <input type="text" name="item_label" id="item_label" value="" /><br />
            </div>
            <div id="item_default_div">
              <label for="item_default">{lang="Default value"}:</label>
                <input type="text" name="item_default" id="item_default" value="" /><br />
            </div>
            <div id="item_required_div">
              <label for="item_required">{lang="Required field"}:</label><br />
                <input type="radio" name="item_required" id="item_required_yes" value="yes" /> {lang="Yes"}
                <input type="radio" name="item_required" id="item_required_no" value="no" checked="checked" /> {lang="No"}<br />
            </div>
            <div id="item_options_div">
              <label for="item_options">{lang="Options (&lt;ID&gt;:&lt;Value&gt;)"}:</label><br />
              <textarea name="item_options" id="item_options"></textarea>
            </div>
            <div id="item_allow_div">
              <label for="item_allow">{lang="Allow"}:</label><br />
                <select id="item_allow" name="item_allow">
                  <option value="plain">{lang="Plaintext (no restrictions)"}</option>
                  <option value="string">{lang="String"}</option>
                  <option value="number">{lang="Number"}</option>
                  <option value="integer">{lang="Integer"}</option>
                  <option value="boolean">{lang="Boolean"}</option>
                  <option value="email">{lang="eMail"}</option>
                  <option value="password">{lang="Password"}</option>
                  <option value="uri">{lang="URL"}</option>
                </select><br /><br />
            </div><br /><br />
            <button id="save_settings" onclick="updateSettings();" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary">
                {lang="Update field settings"}
            </button>
        </fieldset>
        <div class="element">{lang="Please note: Layout is controlled by CSS, so there are no width/height or similar options here"}</div>
        </form>
        {$current_lang}
    </div>