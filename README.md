wblib2
======

Complete rework of wblib (https://github.com/webbird/wblib)

Classes
=======

wbList  - ListBuilder **ready to use**
wbLang  - Internationalization **ready to use**
wbForms - FormBuilder **NOT ready**

SQL Abstraction
===============

Usage example:

    spl_autoload_register(function($class)
    {
        $file = dirname(__FILE__).'/'.str_replace(array('\\','_'), array('/','/'), $class).'.php';
        if (file_exists($file)) {
            @require $file;
        }
        // next in stack
    });

    $db   = wblib\wbQuery::getInstance(array('user'=>'<user>','pass'=>'<password>','dbname'=>'<database>','prefix'=>''));
    $data = $db->search(array(
                    'fields' => 'count(t1.item_id) AS sum',
                        'tables' => array(
                            'mod_profiles_itemattr',
                            'mod_profiles_items'
                        ),
                        'join' => 't1.item_id == t2.item_id',
                        'jointype' => 'right outer join ',
                        'where' => array(
                            '( t1.fieldname == ? && t1.content == ? )',
                            '( t2.section_id == ? && t2.item_expired == ? && t2.item_locked == ? )'
                        ),
                        'params' => array(
                            'additional_item_categories',
                            10,
                            20,
                            'n',
                            'n'
                        )
                    ));
