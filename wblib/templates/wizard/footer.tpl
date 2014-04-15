    <div id="header" class="dark">
      <div style="float:left">v0.1</div>
      <div id="wizard">wblib2 Form Wizard</div>
      <div id="info"></div>
    </div>
    <div id="columns" class="dark">
        <div style="width:350px;">{lang="Properties pane"}</div>
        <div style=""><strong>{lang="Workpane"}</strong></div>
        <div style="width:350px;float:right;">{lang="Source elements"}</div>
    </div>
    <div id="footer" class="dark">
	  &copy; {$year} <a href="http://www.webbird.de/">BlackBird Webprogrammierung</a>. All rights reserved.
    </div>
    <div id="bfuploaddlg" style="display:none;">
        <form action="xxx" enctype="multipart/form-data" method="post">
            <label for="bfformfile">{lang="Upload file"}</label><br />
                <input type="file" name="bfformfile" id="bfformfile" /><br />
            <strong>- {lang="or"} -</strong><br />
            <label for="bflocalfile">{lang="Open local file"}</label><br />
                <select id="bflocalfile" name="bflocalfile">
                    <option>[{lang="Please choose"}]</option>
                </select>
            <input type="submit" />
        </form>
    </div>
    <div id="dlgresult" title='{lang="Result"}' style="display:none;"></div>
  </body>
</html>