<?php
include_once dirname(__FILE__).'/../wbForms.php';

$_be_time = microtime(TRUE);
$_be_mem  = memory_get_usage();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>wbForms Wizard</title>
    <script charset=windows-1250 src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js" type="text/javascript"></script>
    <script charset=windows-1250 src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/vader/jquery-ui.min.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="wizard.css" type="text/css" media="screen" />
  </head>
  <body>

    <div id="content" class="droppable">

	</div>

    <div id="leftblock">
        <form id="item_properties" style="display:none;">
        <fieldset>
            <legend>Item properties</legend>
            <span class="label">Item ID: <span id="item_id"></span></span><br />
            <label for="item_type">Type:</label>
              <input type="text" name="item_type" id="item_type" value="" disabled="disabled" /><br />
            <label for="item_name">Fieldname:</label>
              <input type="text" name="item_name" id="item_name" value="" /><br />
            <div id="item_label_div">
              <label for="item_label">Label:</label>
                <input type="text" name="item_label" id="item_label" value="" /><br />
            </div>
            <div id="item_default_div">
              <label for="item_default">Default value:</label>
                <input type="text" name="item_default" id="item_default" value="" /><br />
            </div>
            <div id="item_required_div">
              <label for="item_required">Required field:</label>
                <input type="radio" name="item_required" id="item_required_yes" value="yes" /> Yes
                <input type="radio" name="item_required" id="item_required_no" value="no" checked="checked" /> No<br />
            </div>
            <div id="item_allow_div">
              <label for="item_allow">Allow:</label>
                <select id="item_allow" name="item_allow">
                  <option value="plain">Plaintext (no restrictions)</option>
                  <option value="string">String</option>
                  <option value="number">Number</option>
                  <option value="integer">Integer</option>
                  <option value="boolean">Boolean</option>
                  <option value="email">eMail</option>
                  <option value="password">Password</option>
                  <option value="uri">URL</option>
                </select><br /><br />
            </div>
            <button id="save_settings" onclick="updateSettings();" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary">
                Update field settings
            </button>
        </fieldset>
        </form>
    </div>
    <div id="rightblock">
<?php
    foreach( array( 'fieldset', 'text', 'password', 'select', 'textarea', 'button' ) as $elem )
    {
        echo '<div class="element type_'.$elem.'" data-fieldtype="'.$elem.'">'.$elem.'</div>';
    }
?>
    <br /><br />
    <button id="export_save" onclick="saveSettings();" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary">
        Export / Save
    </button>

        <br /><br /><br />
        <div class="element">Drag and drop the elements from the list above to the workpane.</div>
        <div class="element">To sort the elements, use drag and drop.</div>
        <div class="element">To change the properties of an element, doubleclick on the element and change the settings in the properties table on the left.</div>
    </div>

    <div id="header" class="dark">
      <div style="float:left">v0.1</div>
      <div id="wizard">wblib2 Form Wizard</div>
      <div id="info"></div>
    </div>
    <div id="columns" class="dark">
        <div style="width:350px;">Properties pane</div>
        <div style=""><strong>Workpane</strong></div>
        <div style="width:350px;float:right;">Source elements</div>
    </div>
    <div id="footer" class="dark">
	  &copy; 2013 <a href="http://www.webbird.de/">BlackBird Webprogrammierung</a>. All rights reserved.
    </div>
    <div id="field_templates" style="display:none;">
        <fieldset id="tpl_fieldset" class="ui-widget ui-widget-content ui-corner-all"><legend class="ui-widget ui-widget-header ui-corner-all">Double click to change options</legend></fieldset>
        <div id="tpl_text"><label>Label</label>:<span class="required"></span><input type="text" value="Double click to change options" /><br /></div>
        <div id="tpl_password"><label>Label</label>:<span class="required"></span><input type="password" value="Double click to change options" /><br /></div>
        <div id="tpl_select"><label>Label</label>:<span class="required"></span><select name="select"><option value="opt1">opt1</option><option value="opt2">opt2</option><option value="opt3">opt3</option></select><br /></div>
        <div id="tpl_textarea"><label>Label</label>:<span class="required"></span><textarea>Double click to change options</textarea><br /></div>
        <button id="tpl_button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary">Double click to change button</button>
    </div>
    <script charset=windows-1250 type="text/javascript">
        var _ctrl_index  = 1000;
        var formElements = new Array();
        var formElement  = {
            id:       '',
	        name:     '',
	        type:     'text',  // text, password, checkbox, radio, ...
	        label:    '',
	        position: 0,
			required: 'no',
			value:    '',
			allow:    'string'
        };

        function serialize (mixed_value) {
          // https://raw.github.com/kvz/phpjs/master/functions/var/serialize.js
          // http://kevin.vanzonneveld.net
          // +   original by: Arpad Ray (mailto:arpad@php.net)
          // +   improved by: Dino
          // +   bugfixed by: Andrej Pavlovic
          // +   bugfixed by: Garagoth
          // +      input by: DtTvB (http://dt.in.th/2008-09-16.string-length-in-bytes.html)
          // +   bugfixed by: Russell Walker (http://www.nbill.co.uk/)
          // +   bugfixed by: Jamie Beck (http://www.terabit.ca/)
          // +      input by: Martin (http://www.erlenwiese.de/)
          // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net/)
          // +   improved by: Le Torbi (http://www.letorbi.de/)
          // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net/)
          // +   bugfixed by: Ben (http://benblume.co.uk/)
          // %          note: We feel the main purpose of this function should be to ease the transport of data between php & js
          // %          note: Aiming for PHP-compatibility, we have to translate objects to arrays
          // *     example 1: serialize(['Kevin', 'van', 'Zonneveld']);
          // *     returns 1: 'a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}'
          // *     example 2: serialize({firstName: 'Kevin', midName: 'van', surName: 'Zonneveld'});
          // *     returns 2: 'a:3:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";s:7:"surName";s:9:"Zonneveld";}'
          var val, key, okey,
            ktype = '', vals = '', count = 0,
            _utf8Size = function (str) {
              var size = 0,
                i = 0,
                l = str.length,
                code = '';
              for (i = 0; i < l; i++) {
                code = str.charCodeAt(i);
                if (code < 0x0080) {
                  size += 1;
                }
                else if (code < 0x0800) {
                  size += 2;
                }
                else {
                  size += 3;
                }
              }
              return size;
            },
            _getType = function (inp) {
              var match, key, cons, types, type = typeof inp;

              if (type === 'object' && !inp) {
                return 'null';
              }
              if (type === 'object') {
                if (!inp.constructor) {
                  return 'object';
                }
                cons = inp.constructor.toString();
                match = cons.match(/(\w+)\(/);
                if (match) {
                  cons = match[1].toLowerCase();
                }
                types = ['boolean', 'number', 'string', 'array'];
                for (key in types) {
                  if (cons == types[key]) {
                    type = types[key];
                    break;
                  }
                }
              }
              return type;
            },
            type = _getType(mixed_value)
          ;

          switch (type) {
            case 'function':
              val = '';
              break;
            case 'boolean':
              val = 'b:' + (mixed_value ? '1' : '0');
              break;
            case 'number':
              val = (Math.round(mixed_value) == mixed_value ? 'i' : 'd') + ':' + mixed_value;
              break;
            case 'string':
              val = 's:' + _utf8Size(mixed_value) + ':"' + mixed_value + '"';
              break;
            case 'array': case 'object':
              val = 'a';
          /*
                if (type === 'object') {
                  var objname = mixed_value.constructor.toString().match(/(\w+)\(\)/);
                  if (objname == undefined) {
                    return;
                  }
                  objname[1] = this.serialize(objname[1]);
                  val = 'O' + objname[1].substring(1, objname[1].length - 1);
                }
                */

              for (key in mixed_value) {
                if (mixed_value.hasOwnProperty(key)) {
                  ktype = _getType(mixed_value[key]);
                  if (ktype === 'function') {
                    continue;
                  }

                  okey = (key.match(/^[0-9]+$/) ? parseInt(key, 10) : key);
                  vals += this.serialize(okey) + this.serialize(mixed_value[key]);
                  count++;
                }
              }
              val += ':' + count + ':{' + vals + '}';
              break;
            case 'undefined':
              // Fall-through
            default:
              // if the JS object has a property which contains a null value, the string cannot be unserialized by PHP
              val = 'N';
              break;
          }
          if (type !== 'object' && type !== 'array') {
            val += ';';
          }
          return val;
        }

        // this is for debugging only!
        function dump(arr,level) {
            var dumped_text = "";
            if(!level) { level = 0; }
            //The padding given at the beginning of the line.
            var level_padding = "";
            for(var j=0;j<level+1;j++) { level_padding += " "; }
            if(typeof(arr) == 'object') { //Array/Hashes/Objects
                for(var item in arr) {
                    var value = arr[item];
                    if(typeof(value) == 'object') { //If it is an array,
                        dumped_text += level_padding + "'" + item + "' ...\n";
                        dumped_text += dump(value,level+1);
                    } else {
                        dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
                    }
                }
            } else { //Stings/Chars/Numbers etc.
                dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
            }
            return dumped_text;
        }
        function saveSettings() {
            alert(serialize(formElements));
        }

        function updateSettings() {
            var elem_id   = $('#item_id').text();
            var elem      = formElements[elem_id];
            // update object
            elem.name     = $('#item_name').val();
            elem.label    = $('#item_label').val();
            elem.allow    = $('#item_allow').val();
            elem.required = $('#item_required_yes:checked').length ? 'yes' : 'no';
            elem.value    = $('#item_default').val();
            formElements[elem_id] = elem;
            // update view
            if(elem.type==='fieldset') {
                $('fieldset#'+elem_id).find('legend').text(elem.label);
            }
            else {
                $('div#'+elem_id).find('label').text(elem.label);
            }
            if(elem.required==='yes') {
                $('div#'+elem_id).find('span.required').text('*');
            }
            else {
                $('div#'+elem_id).find('span.required').text(' ');
            }
//alert(dump(formElements[elem_id]));
        }
        jQuery(document).ready( function($) {
            function handleClick(e) {
                e.stopPropagation();
                // remove class highlight from any item
                $('.highlight').removeClass('highlight');
                // add to current target
                $(e.target).addClass('highlight').removeClass('dropped');
                // this is no form element, but a span, so use text()
                $('#item_id').text(formElements[$(e.target).prop('id')].id);
                // form values
                $('#item_name').val(formElements[$(e.target).prop('id')].name);
                $('#item_type').val(formElements[$(e.target).prop('id')].type);
                if(formElements[$(e.target).prop('id')].required==='yes') {
                    $('#item_required_no').prop('checked','');
                    $('#item_required_yes').prop('checked','checked');
                }
                else {
                    $('#item_required_yes').prop('checked','');
                    $('#item_required_no').prop('checked','checked');
                }
                if(formElements[$(e.target).prop('id')].type=='fieldset')
                {
                    $('#item_default_div').hide();
                    $('#item_allow_div').hide();
                    $('#item_required_div').hide();
                }
                else
                {
                    $('#item_label').val(formElements[$(e.target).prop('id')].label);
                    $('#item_allow').val(formElements[$(e.target).prop('id')].allow);
                    $('#item_default_div').show();
                    $('#item_allow_div').show();
                    $('#item_required_div').show();
                }
                // make this item draggable
                $(e.target).draggable({grid:[20,20],cursor:"move",zIndex:1000,containment:"#content"});
                // show properties form
                $('#item_properties').show();
            }
            function handleDrop( event, ui ) {
                var fieldtype = $(ui.draggable).attr('data-fieldtype');
                // find template
                var newelem = $('#field_templates').find('#tpl_'+fieldtype).clone();
                // add unique id
                $(newelem).prop('id','CTRL_'+(_ctrl_index++)).addClass('dropped');
                // append
                $(this).append(newelem);
                // highlight
                newelem.effect('highlight','slow');
                // make fieldsets droppable
                if(fieldtype=='fieldset') {
                    newelem.droppable({drop:handleDrop,greedy:true});
                }
                // add to list; use formElement as a template, overriding some options
                formElements[$(newelem).prop('id')] = $.extend({},formElement,{
                    id:    $(newelem).prop('id'),
                    name:  $(newelem).prop('id'),
                    type:  fieldtype,
                    label: $(newelem).find('label').text()
                });
                // add double click event
                $('.dropped').off('dblclick').on('dblclick',handleClick);
                // trigger click
                $(newelem).trigger('dblclick');
            }
            $('#rightblock .element').draggable({helper:"clone",grid:[20,20],cursor:"move",zIndex:1000,containment:"#content"});
            $('.droppable').droppable({drop: handleDrop});
            $('.ui-button').button();
            $('button#save_settings').off('click').on('click',function(e){e.preventDefault();});
        });
    </script>
  </body>
</html>