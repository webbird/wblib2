var passthru_url   = '';
var current_parent = '#content';
var _ctrl_index    = 1000;
var formElements   = new Array();
var configData     = new Array();
var formElement    = {
    id:       '',
    name:     '',
    type:     'text',  // text, password, checkbox, radio, ...
    label:    '',
    position: 0,
    required: 'no',
    value:    '',
    checked:  '',
    allow:    'string',
    options:  ''
};

function addOptions(elem,data,resetpreview)
{
    var options = '';
    var textarea = '';
    if(data.type == 'radiogroup')
    {
        for(var key in data.options) {
            var value = data.options[key];
            if(resetpreview===true)
            {
                var opt_string = $('#field_templates').find('#tpl_radioitem').clone();
                $("input", opt_string).prop({"name":key,"value":value,"id":key});
                var opt_label = $('<label/>').prop('for',key).text(value);
                opt_label.appendTo(opt_string);
                options = options + opt_string.html();
            }
            textarea = textarea + key + ':' + value + "\n";
        }
        if(resetpreview===true)
        {
            elem.append(options);
        }
        else
        {
            elem.append(options);
        }
        $('#leftblock textarea#item_options').text(textarea);
    }
}

function addElement(id,fieldtype,label,options)
{
//alert('passed options: id ['+id+'] type ['+fieldtype+'] label ['+label+'] options '+dump(options));
    // if no ID was passed, create one
    if(id.length == 0) {
        id = 'CTRL_'+(_ctrl_index++);
    }
    // find template and create object by cloning it
    var newelem = $('#field_templates').find('#tpl_'+fieldtype).clone();
    // if no label was passed, create default label from template
    if(label.length ==0) {
        label = $(newelem).find('label').text();
    }
    // add unique id
    $(newelem).prop('id',id).addClass('dropped');
    // add to elements list
    formElements[id] = $.extend({},formElement,{
        id:    id,
        name:  id,
        type:  fieldtype,
        label: label
    });
    // element has options (radiogroup, select)
    if(typeof options != 'undefined' && options.length > 0) {
        formElements[id].options = unserialize(options);
        addOptions(newelem,formElements[id],true);
    }
    // append new element to parent
    if(fieldtype == 'fieldset')
    {
        $(newelem).find('legend').text(label);
        $(newelem).appendTo('#content');
        current_parent = $('#'+id);
    }
    else
    {
        $(newelem).find('label:first').text(label);
        $(newelem).appendTo(current_parent);
    }
    return newelem;
}

// this is for debugging only!
function dump(arr,level) {
    var dumped_text = "";
    if(!level) { level = 0; }
    //The padding given at the beginning of the line.
    var level_padding = "";
    for(var j=0;j<level+1;j++) { level_padding += "    "; }
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
}   // ----- end function dump() -----

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
        ktype     = '',
        vals      = '',
        count     = 0,
        _utf8Size = function (str)
        {
            var size = 0,
                i    = 0,
                l    = str.length,
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
        _getType = function (inp)
        {
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

    switch (type)
    {
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
}   // ----- end function serialize -----

function unserialize(data) {
  //  discuss at: http://phpjs.org/functions/unserialize/
  // original by: Arpad Ray (mailto:arpad@php.net)
  // improved by: Pedro Tainha (http://www.pedrotainha.com)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Chris
  // improved by: James
  // improved by: Le Torbi
  // improved by: Eli Skeggs
  // bugfixed by: dptr1988
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //  revised by: d3x
  //    input by: Brett Zamir (http://brett-zamir.me)
  //    input by: Martin (http://www.erlenwiese.de/)
  //    input by: kilops
  //    input by: Jaroslaw Czarniak
  //        note: We feel the main purpose of this function should be to ease the transport of data between php & js
  //        note: Aiming for PHP-compatibility, we have to translate objects to arrays
  //   example 1: unserialize('a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}');
  //   returns 1: ['Kevin', 'van', 'Zonneveld']
  //   example 2: unserialize('a:3:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";s:7:"surName";s:9:"Zonneveld";}');
  //   returns 2: {firstName: 'Kevin', midName: 'van', surName: 'Zonneveld'}

  var that = this,
    utf8Overhead = function (chr) {
      // http://phpjs.org/functions/unserialize:571#comment_95906
      var code = chr.charCodeAt(0);
      if (code < 0x0080) {
        return 0;
      }
      if (code < 0x0800) {
        return 1;
      }
      return 2;
    };
  error = function (type, msg, filename, line) {
    throw new that.window[type](msg, filename, line);
  };
  read_until = function (data, offset, stopchr) {
    var i = 2,
      buf = [],
      chr = data.slice(offset, offset + 1);

    while (chr != stopchr) {
      if ((i + offset) > data.length) {
        error('Error', 'Invalid');
      }
      buf.push(chr);
      chr = data.slice(offset + (i - 1), offset + i);
      i += 1;
    }
    return [buf.length, buf.join('')];
  };
  read_chrs = function (data, offset, length) {
    var i, chr, buf;

    buf = [];
    for (i = 0; i < length; i++) {
      chr = data.slice(offset + (i - 1), offset + i);
      buf.push(chr);
      length -= utf8Overhead(chr);
    }
    return [buf.length, buf.join('')];
  };
  _unserialize = function (data, offset) {
    var dtype, dataoffset, keyandchrs, keys, contig,
      length, array, readdata, readData, ccount,
      stringlength, i, key, kprops, kchrs, vprops,
      vchrs, value, chrs = 0,
      typeconvert = function (x) {
        return x;
      };

    if (!offset) {
      offset = 0;
    }
    dtype = (data.slice(offset, offset + 1))
      .toLowerCase();

    dataoffset = offset + 2;

    switch (dtype) {
    case 'i':
      typeconvert = function (x) {
        return parseInt(x, 10);
      };
      readData = read_until(data, dataoffset, ';');
      chrs = readData[0];
      readdata = readData[1];
      dataoffset += chrs + 1;
      break;
    case 'b':
      typeconvert = function (x) {
        return parseInt(x, 10) !== 0;
      };
      readData = read_until(data, dataoffset, ';');
      chrs = readData[0];
      readdata = readData[1];
      dataoffset += chrs + 1;
      break;
    case 'd':
      typeconvert = function (x) {
        return parseFloat(x);
      };
      readData = read_until(data, dataoffset, ';');
      chrs = readData[0];
      readdata = readData[1];
      dataoffset += chrs + 1;
      break;
    case 'n':
      readdata = null;
      break;
    case 's':
      ccount = read_until(data, dataoffset, ':');
      chrs = ccount[0];
      stringlength = ccount[1];
      dataoffset += chrs + 2;

      readData = read_chrs(data, dataoffset + 1, parseInt(stringlength, 10));
      chrs = readData[0];
      readdata = readData[1];
      dataoffset += chrs + 2;
      if (chrs != parseInt(stringlength, 10) && chrs != readdata.length) {
        error('SyntaxError', 'String length mismatch');
      }
      break;
    case 'a':
      readdata = {};

      keyandchrs = read_until(data, dataoffset, ':');
      chrs = keyandchrs[0];
      keys = keyandchrs[1];
      dataoffset += chrs + 2;

      length = parseInt(keys, 10);
      contig = true;

      for (i = 0; i < length; i++) {
        kprops = _unserialize(data, dataoffset);
        kchrs = kprops[1];
        key = kprops[2];
        dataoffset += kchrs;

        vprops = _unserialize(data, dataoffset);
        vchrs = vprops[1];
        value = vprops[2];
        dataoffset += vchrs;

        if (key !== i)
          contig = false;

        readdata[key] = value;
      }

      if (contig) {
        array = new Array(length);
        for (i = 0; i < length; i++)
          array[i] = readdata[i];
        readdata = array;
      }

      dataoffset += 1;
      break;
    default:
      error('SyntaxError', 'Unknown / Unhandled data type(s): ' + dtype);
      break;
    }
    return [dtype, dataoffset - offset, typeconvert(readdata)];
  };

  return _unserialize((data + ''), 0)[2];
}

function saveSettings(passthru_url) {
    if(passthru_url.length>0)
    {
        $.post(
            passthru_url,
            {
                data: serialize(formElements),
                config: serialize(configData)
            },
            function(ajax) {
                if(ajax.success === true){
                    $("#dlgresult").html('Successfully saved').dialog('open');
                }
                else
                {
                    $("#dlgresult").html('Unable to save: '.ajax.message).dialog('open');
                }
            },
            "json"
        ).fail(function( jqXHR, textStatus, errorThrown ) {
            $("#dlgresult").html('Unable to save: '+errorThrown+'<br /><br /><span style="font-size:0.7em;">Called URL: '+passthru_url+'</span>').dialog('open');
        })
    }
    else
    {
        alert(serialize(formElements));
    }
}

function updateSettings() {
    var elem_id   = $('#item_id').text(),
        obj       = formElements[elem_id];
    // update object
    obj.name      = $('#item_name').val();
    obj.label     = $('#item_label').val();
    obj.allow     = $('#item_allow').val();
    obj.required  = $('#item_required_yes:checked').length ? 'yes' : 'no';
    obj.value     = $('#item_default').val();
    // if it's a radio group...
    if(obj.type=='radiogroup')
    {
        // get options
        var arrayOfLines = $('#item_options').val().split('\n');
        var options      = new Array();
        for(line in arrayOfLines)
        {
            if(arrayOfLines[line].length && arrayOfLines[line].indexOf(':'))
            {
                var values = arrayOfLines[line].split(':');
                options[values[0]] = values[1];
            }
        }
        if(obj.value.length) {
            obj.checked = obj.value;
            obj.value = '';
        }
        elem = $('#field_templates').find('#tpl_'+obj.type).clone().prop({'id':elem_id});
        obj.options = options;
        addOptions(elem,obj,true);
        $('div#'+elem_id).replaceWith(elem);
    }
    // replace form element with new data
    formElements[elem_id] = obj;
    // update view
    if(obj.type==='fieldset') {
        $('fieldset#'+elem_id).find('legend').text(obj.label);
    }
    else {
        $('div#'+elem_id).find('label:first').text(obj.label);
    }
    if(obj.required==='yes') {
        $('div#'+elem_id).find('span.required').text('*');
    }
    else {
        $('div#'+elem_id).find('span.required').text(' ');
    }
}   // ----- end function updateSettings() -----

$("#bfuploaddlg").dialog({
    modal: true,
    autoOpen: false,
    height: 250,
    width: 400,
    buttons:
    {
        Close: function() { $( this ).dialog( "close" ) },
        Open: function() { $( this ).dialog( "close" ) }
    }
});

$('#dlgresult').dialog({
    modal: true,
    autoOpen: false,
    height: 150,
    width: 200,
    buttons:
    {
        Close: function() { $( this ).dialog( "close" ) },
    }
});

$('button#bf_open_existing').click( function() {
    $("#bfuploaddlg").dialog('open');
});

function handleClick(e) {
    var current;
    var id;
    e.stopPropagation();
    // remove class highlight from any item
    $('.highlight').removeClass('highlight');
    // add to current target
    $(e.target).addClass('highlight').removeClass('dropped');
    // find parent element (for fieldsets, for example)
    current = $(e.target);
    if( $(e.target).is('[id]') !== true ) {
        current = $(e.target).parent();
        while($(current).is('[id]') !== true ) {
            current = $(e.target).parent();
        }
    }

    id = $(current).prop('id');

    // this is no form element, but a span, so use text()
    $('#item_id').text(id);
    $('#item_type').text(formElements[id].type);
    // form values
    $('#item_name').val(formElements[id].name);
    $('#item_label').val(formElements[id].label);
    if(formElements[id].required==='yes') {
        $('#item_required_no').prop('checked','');
        $('#item_required_yes').prop('checked','checked');
    }
    else {
        $('#item_required_yes').prop('checked','');
        $('#item_required_no').prop('checked','checked');
    }
    if(formElements[id].type=='fieldset')
    {
        $('#item_default_div').hide();
        $('#item_allow_div').hide();
        $('#item_required_div').hide();
    }
    else if (formElements[id].type=='radiogroup')
    {
        addOptions(current,formElements[id]);
        $('#item_allow_div').hide();
        $('#item_options_div').show();
    }
    else
    {
        $('#item_options_div').hide();
        $('#item_allow').val(formElements[id].allow);
        $('#item_default_div').show();
        $('#item_allow_div').show();
        $('#item_required_div').show();
    }
    // make this item draggable
    $(e.target).draggable({grid:[20,20],cursor:"move",zIndex:1000,containment:"#content"});
    // show properties form
    $('#item_properties').show();
}
/*******************************************************************
 * handles an element drop
 ******************************************************************/
function handleDrop( event, ui ) {
    // get the element type
    var fieldtype = $(ui.draggable).attr('data-fieldtype');
    // add to list; id will be created automatically
    var newelem = addElement('',fieldtype,'');
    if(fieldtype=='fieldset') {
        newelem.droppable({drop:handleDrop,greedy:true});
    }
    // add double click event
    newelem.off('dblclick').on('dblclick',handleClick);
    // highlight
    newelem.effect('highlight','slow');
    // trigger click
    newelem.trigger('dblclick');// JavaScript Document
}

$('#rightblock .element').draggable({helper:"clone",grid:[20,20],cursor:"move",zIndex:1000,containment:"#content"});
$('.droppable').droppable({drop: handleDrop});
$('.ui-button').button();
$('button#save_settings').off('click').on('click',function(e){e.preventDefault();});
