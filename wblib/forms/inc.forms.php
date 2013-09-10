<?php

$FORMS = array(
    'example_login_form' => array(
        array(
            'type'     => 'legend',
            'text'     => 'Login',
        ),
        array(
            'type'     => 'text',
            'name'     => 'loginname',
            'label'    => 'Login name',
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
            'infotext' => 'This will open an UI Datepicker to choose a date',
        ),
        array(
            'type'     => 'text',
            'name'     => 'field2',
            'label'    => 'Field 2',
            'infotext' => 'This is a tooltip',
        ),
        array(
            'type'     => 'text',
            'name'     => 'field3',
            'label'    => 'Field 3',
            'required' => true,
        ),
        array(
            'type'     => 'select',
            'name'     => 'select1',
            'label'    => 'Select Test',
            'options'  => array('Y','N'),
            'infotext' => 'Please select something from the list',
        ),
        array(
            'type'     => 'select',
            'name'     => 'select2',
            'label'    => 'Select Test with key/value pairs',
            'options'  => array('Y'=>'Yes','N'=>'No'),
            'selected' => 'N',#
            'infotext' => 'Please select something from the list',
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
        array(
            'type'     => 'radiogroup',
            'label'    => 'Radiogroup',
            'name'     => 'rgroup1',
            'options'  => array('bla','fasel','demo','nix'),
            'checked'  => 'demo',
        ),
        array(
            'type'     => 'checkboxgroup',
            'label'    => 'Group of checkboxes',
            'name'     => 'checkboxgrp1',
            'options'  => array('Y'=>'Yes','N'=>'No'),
        ),
    ),
);