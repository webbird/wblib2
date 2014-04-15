{include="header"}
    <div id="content" class="droppable">
{if="isset($form) && $form"}{$form}{/if}
	</div>
{include="rightblock"}
{include="leftblock"}
{include="templates"}
{if="isset($js) && $js"}
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $.getScript('{$url}/forms/wizard.js', function( data, textStatus, jqxhr ) {
            {$js}
            for(var item in formElements) {
                elem = $('#'+formElements[item]['id']);
                elem.droppable({drop:handleDrop,greedy:true}).off('dblclick').on('dblclick',handleClick).trigger('dblclick');
            }
        });
    });
</script>
{/if}
{include="footer"}