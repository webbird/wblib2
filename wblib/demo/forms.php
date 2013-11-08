<?php
include_once dirname(__FILE__).'/../wbForms.php';

$_be_time = microtime(TRUE);
$_be_mem  = memory_get_usage();

$form = wblib\wbForms::getInstanceFromFile();
$form->setForm('example_multi_form');

// add an element programmatically
//$form->addElement(array('name'=>'testtest','label'=>'Added later'),'edit','before');

// add additional CSS file
$form->addCSSLink('custom.css');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>wbForms Demo</title>
        <link rel="stylesheet" href="index.css" type="text/css" media="screen" />
        <?php echo $form->getHeaders(); ?>
    </head>
    <body>
        <div class="header"><h1>Advanced form</h1></div>
        <div class="content">
            Please note: This form uses a custom CSS file to style the Tooltips!<br /><br />
            No data will be sent by sending this form, you will only get a dump of the $_REQUEST array.<br /><br />
            [ <a href="index.php">&laquo; Back to index page</a> ]<br /><br />
<?php
    echo $form->getForm();
?>
            <div style="width:48%;float:left;">
                <h3>Form init array</h3>
<?php
    echo "<textarea cols=\"100\" rows=\"20\" style=\"width: 100%;\">";
    $form->dump();
    echo "</textarea></div>";
    if($form->isSent())
    {
?>
        <div style="width:48%;float:right;">
        <h3>Form data</h3>
<?php
        echo "<textarea cols=\"100\" rows=\"20\" style=\"width: 100%;\">";
        print_r( $form->getData(1) );
        echo "</textarea><br />";
        echo "validate result: -", ( $form->isValid() === true ? 'ok' : 'not ok' ), "-<br />";
        if(!$form->isValid())
            $form->getErrors();
        echo "</div>";
    }
?>

    <br style="clear:both;" /><br />
        <div style="border:1px solid #f00;">
            Memory usage: <?php echo sprintf('%0.2f',( (memory_get_usage() - $_be_mem) / (1024 * 1024) )) . ' MB'; ?><br />
            Script run time: <?php echo sprintf('%0.2f',( microtime(TRUE) - $_be_time )) . ' sec'; ?>
        </div>
    </div>
    </body>
</html>