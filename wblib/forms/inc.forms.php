<?php

$FORMS = array(
    'example_login_form' => array(
        array(
            'type'     => 'legend',
            'label'     => 'Login',
        ),
        array(
            'type'     => 'text',
            'name'     => 'loginname',
            'label'    => 'Login name',
            'title'    => 'Please enter your login name',
            'required' => true,
        ),
        array(
            'type'     => 'password',
            'name'     => 'password',
            'label'    => 'Password',
            'required' => true,
        ),
    ),
    'example_mail_form' => array(
        array(
            'type'     => 'legend',
            'label'    => 'Mail',
        ),
        array(
            'type'     => 'hidden',
            'name'     => 'recipient',
            'value'    => 'nobody@nowhere.de',
        ),
        array(
            'type'     => 'text',
            'name'     => 'sendername',
            'label'    => 'Sender name',
            'required' => true,
            'style'    => 'width:300px;',
        ),
        array(
            'type'     => 'text',
            'name'     => 'sendermail',
            'label'    => 'Sender eMail',
            'required' => true,
            'allow'    => 'email',
        ),
        array(
            'type'     => 'text',
            'name'     => 'subject',
            'label'    => 'Subject',
            'required' => true,
        ),
        array(
            'type'     => 'textarea',
            'name'     => 'body',
            'label'    => 'Message',
            'required' => true,
        ),
    ),
    'example_multi_form' => array(
        array(
            'type'     => 'legend',
            'label'    => 'Fieldset 1',
        ),
        array(
            'type'     => 'date',
            'name'     => 'date',
            'label'    => 'Datefield (loads UI Datepicker)',
            'title'    => 'This will open an UI Datepicker to choose a date',
        ),
        array(
            'type'     => 'radiogroup',
            'label'    => 'Radiogroup',
            'name'     => 'rgroup2',
            'options'  => array('opt1','opt2','opt3','opt4'),
            'checked'  => 'opt1',
            'title'    => 'Choose one',
        ),
        array(
            'type'     => 'text',
            'name'     => 'edit',
            'label'    => 'Edit field',
            'title'    => 'Allows an integer between 5 and 15',
            'allow'    => 'int:5:15',
            'required' => true,
            'missing'  => 'Please insert a digit between 5 and 15',
            'invalid'  => 'Please insert a digit between 5 and 15',
        ),
        array(
            'type'     => 'textarea',
            'name'     => 'text',
            'label'    => 'Textarea field',
            'required' => true,
        ),
        array(
            'type'     => 'checkboxgroup',
            'label'    => 'Group of checkboxes',
            'name'     => 'checkboxgrp1',
            'options'  => array('Y'=>'Yes','N'=>'No'),
        ),
        array(
            'type'     => 'select',
            'name'     => 'select1',
            'label'    => 'Select Test',
            'options'  => array('Y','N'),
            'title'    => 'Please select something from the list',
        ),
        array(
            'type'     => 'select',
            'name'     => 'select2',
            'label'    => 'Select Test with key/value pairs',
            'options'  => array('Y'=>'Yes','N'=>'No'),
            'selected' => 'N',#
            'title'    => 'Please select something from the list',
        ),
        array(
            'type'     => 'legend',
            'label'    => 'Fieldset 2',
        ),
        array(
            'type'     => 'checkbox',
            'label'    => 'Single checkbox',
            'name'     => 'checkbox1',
        ),
        #array(
        #    'type'     => 'wysiwyg',
        #    'name'     => 'wysiwyg',
        #    'label'    => 'WYSIWYG field',
        #    'required' => true,
        #),
        array(
            'type'     => 'radiogroup',
            'label'    => 'Radiogroup',
            'name'     => 'rgroup1',
            'options'  => array('bla','fasel','demo','nix'),
            'checked'  => 'demo',
        ),
        #array(
        #    'type'     => 'wysiwyg',
        #    'name'     => 'wysiwyg2',
        #    'label'    => 'Another WYSIWYG field',
        #    'required' => true,
        #),

    ),
);