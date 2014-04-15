<?php
// wblib2 autoloader
spl_autoload_register(function($class)
{
#echo "autloading -$class-<br />";
    $file = str_replace('\\','/',dirname(__FILE__)).'/../../'.str_replace(array('\\','_'), array('/','/'), $class).'.php';
#echo "file: $file<br />";
    if (file_exists($file)) {
        @require $file;
    }
    // next in stack
});
include_once dirname(__FILE__).'/../wbFormsWizard.php';
$_be_time = microtime(TRUE);
$_be_mem  = memory_get_usage();

$w = wblib\wbFormsWizard::getInstance();
$w->set('wblib_url','http://localhost/_projects/bcwa/modules/lib_wblib/wblib');
$w->show();
