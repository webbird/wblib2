<?php

/**
 *          _     _  _ _     ______
 *         | |   | |(_) |   (_____ \
 *    _ _ _| |__ | | _| |__   ____) )
 *   | | | |  _ \| || |  _ \ / ____/
 *   | | | | |_) ) || | |_) ) (_____
 *    \___/|____/ \_)_|____/|_______)
 *
 *
 *   @category     wblib2
 *   @package      wbForms
 *   @author       BlackBird Webprogrammierung
 *   @copyright    2013 BlackBird Webprogrammierung
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU Lesser General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   Lesser General Public License for more details.
 *
 *   You should have received a copy of the GNU Lesser General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 **/

namespace wblib;

/**
 * form builder class
 *
 * @category   wblib2
 * @package    wbForms
 * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
 * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
 */
if ( ! class_exists( 'wbForms', false ) )
{
    /**
     * form builder base class
     *
     * @category   wblib2
     * @package    wbFormsBase
     * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbFormsBase
    {
        /**
         * logger
         **/
        private   static $analog     = NULL;
        /**
         * accessor to wbLang if available
         **/
        private   static $wblang     = NULL;
        /**
         * log level
         **/
        protected static $loglevel   = 7;
        /**
         * global configuration options
         **/
        protected static $config     = array(
            'blank_span'      => NULL,
            'breaks'          => NULL,
            'css_files'       => NULL,
            'file'            => NULL,
            'form_style'      => NULL,
            'jquery'          => NULL,
            'jquery_src'      => NULL,
            'jquery_ui'       => NULL,
            'jquery_ui_css'   => NULL,
            'jquery_ui_src'   => NULL,
            'jquery_ui_theme' => NULL,
            'required_span'   => NULL,
            'required_style'  => NULL,
            'var'             => NULL,
            'wrapperclass'    => NULL,
        );

        /**
         * accessor to wbLang (if available)
         *
         * returns the original message if wbLang is not available
         *
         * @access protected
         * @param  string    $msg
         * @return string
         **/
        protected static function t($message)
        {
            if( !self::$wblang && !self::$wblang == -1)
            {
                try
                {
                    self::$wblang = wbLang::getInstance();
                }
                catch ( wbFormsExection $e )
                {
                    self::log('Unable to load wbLang',7);
                    self::$wblang = -1;
                }
            }
            if( self::$wblang !== -1 )
                return self::$wblang->t($message);
            else
                return $message;
        }   // end function t()

        /**
         * setter for configuration options
         *
         * @access public
         * @param  mixed  $key   - config key or array of key-value-pairs
         * @param  string $value - if $key is a string, this is the value
         * @return void
         **/
        public static function config($key,$value=NULL)
        {
            if(!is_array($key) && $value!==NULL)
                $key = array( $key => $value );
            foreach( $key as $k => $v )
                self::$config[$k] = $v;
        }   // end function config()

        /**
         * accessor to Analog (if installed)
         *
         * Note: Log messages are ignored if no Analog is available!
         *
         * @access protected
         * @param  string   $message
         * @param  integer  $level
         * @return
         **/
        protected static function log($message, $level = 3)
        {
            $class = get_called_class();
            if($level>$class::$loglevel) return;
            if( !self::$analog && !self::$analog == -1)
            {
                if(file_exists(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php'))
                {
                    include_once(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php');
                    wblib_init_3rdparty(dirname(__FILE__).'/debug/','wbForms',$class::$loglevel);
                    self::$analog = true;
                }
                else
                {
                    self::$analog = -1;
                }
            }
            if ( self::$analog !== -1 )
                \Analog::log($message,$level);
        }   // end function log()
    }

    /**
     * form builder form class
     *
     * @category   wblib2
     * @package    wbForms
     * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbForms extends wbFormsBase
    {
        /**
         * array of named instances (=forms)
         **/
        protected static $instances  = array();
        /**
         * log level
         **/
        protected static $loglevel   = 7;
        /**
         * array of known forms
         **/
        protected static $FORMS      = array();
        /**
         * name of current form (set by setForm())
         **/
        protected static $CURRENT    = NULL;
        /**
         * output template
         **/
        protected static $tpl
            = "<div class=\"%wrapperclass%\">\n<form action=\"%action%\" enctype=\"%enctype%\" method=\"%method%\" name=\"%name%\" id=\"%id%\" %class%%style%>\n%content%\n</form>\n</div>\n";
        /**
         * form attribute defaults
         **/
        protected static $attributes = array(
            // <form> attributes
            'action'       => NULL,
            'method'       => 'post',
            'id'           => NULL,
            'name'         => NULL,
            'class'        => 'ui-widget',
            'enctype'      => 'application/x-www-form-urlencoded',
            // internal attributes
            'content'      => NULL,
            'width'        => NULL,
            'wrapperclass' => NULL,
        );
        /**
         * current form attributes
         **/
        protected $attr = array();
        /**
         * additional css files to load
         **/
        protected $css  = array();

        /**
         * no cloning!
         **/
        private function __clone() {}

        /**
         * make static functions OOP
         **/
        public function __call($method, $args)
        {
            self::log(sprintf('searching for method [%s]',$method),7);
            if(count($args))
                self::log('args',var_export($args,1),7);
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }   // end function __call()

        /**
         * constructor; private to make sure that it can only be called
         * using getInstance()
         *
         * resets the global config settings!
         **/
        private function __construct() {
            self::resetGlobals();
        }   // end function __construct()

        /**
         * resets all configuration options to their defaults
         *
         * @access public
         * @return void
         **/
        public static function resetGlobals()
        {
            self::$config = array(
                // default forms definition file name
                'file'            => 'inc.forms.php',
                // default variable name
                'var'             => 'FORMS',
                // add breaks (<br />) after each element
                'breaks'          => true,
                // <form> style
                'form_style'       => 'width:800px;margin:10px auto;',
                // list of custom css files to add
                'css_files'       => array(),
                // form wrapper div class
                'wrapperclass'    => 'fbform',
                // markup for required fields
                'required_span'   => '<span class="fbrequired" style="display:inline-block;width:15px;color:#B94A48;">*</span>',
                // if field is not required, print this instead
                'blank_span'      => '<span class="fbblank" style="display:inline-block;width:15px;">&nbsp;</span>',
                //
                'required_style'  => NULL,
                // button line
                'button_line'     => '<div class="ui-dialog-buttonpane" style="float:right;">%buttons%</div>',
            // ----- jQuery UI options -----
                // load jQuery core
                'jquery'          => true,
                // load jQuery UI
                'jquery_ui'       => true,
                // jQuery Core CDN
                'jquery_src'      => 'https://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js',
                // jQuery UI CDN
                'jquery_ui_src'   => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js',
                // jQuery UI theme CDN
                'jquery_ui_css'   => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/%theme%/jquery-ui.min.css',
                // theme name
                'jquery_ui_theme' => 'sunny',
            );
            // default paths to search inc.forms.php
            $callstack = debug_backtrace();
            self::$config['workdir']
                = ( isset($callstack[1]) && isset($callstack[1]['file']) )
                ? wbValidate::path(realpath(dirname($callstack[0]['file'])))
                : wbValidate::path(realpath(dirname(__FILE__)));
            self::$config['path'] = self::$config['workdir'].'/forms';
            self::$config['fallback_path'] = self::$config['workdir'].'/forms';
        }   // end function resetGlobals()
        
        /**
         * Create an instance
         *
         * @access public
         * @param  string  $name - optional name, default: 'default'
         * @return object
         **/
        public static function getInstance($name='default')
        {
            if ( !array_key_exists( $name, self::$instances ) ) {
                self::log(sprintf('creating new instance with name [%s]',$name),7);
                self::$instances[$name] = new self();
            }
            return self::$instances[$name];
        }   // end function getInstance()

        /**
         * Create an instance by loading the form configuration(s) from file
         *
         * This is a wrapper combining resetGlobals(), loadFile() and
         * getInstance() into one call
         *
         * @access public
         * @param  string  $name - optional name, default: 'default'
         * @param  string  $file - file name
         * @param  string  $path - optional search path
         * @param  string  $var  - optional var name (default: '$FORMS')
         * @return object
         **/
        public static function getInstanceFromFile($name='default',$file='inc.forms.php',$path=NULL,$var=NULL)
        {
            self::resetGlobals();
            self::loadFile($file,$path,$var);
            return self::getInstance($name);
        }   // end function getInstanceFromFile()

        /**
         * load form configuration from a file
         *
         * @access public
         * @param  string  $file - file name
         * @param  string  $path - optional search path
         * @param  string  $var  - optional var name (default: '$FORMS')
         * @return void
         **/
        public static function loadFile($file = 'inc.forms.php', $path = NULL, $var = NULL)
        {
            $var = ( $var ? $var : self::$config['var'] );
            if(!file_exists($file))
            {
                $search_paths = array(
                    self::$config['workdir'],
                    self::$config['path'],
                    self::$config['fallback_path']
                );
                if($path)
                    array_unshift( $search_paths, $path );
                foreach($search_paths as $path)
                {
                    if(file_exists($path.'/'.$file))
                    {
                        $file = $path.'/'.$file;
                        break;
                    }
                }
            }
            if(!file_exists($file))
                throw new wbFormsException(
                    sprintf(
                        "Configuration file [%s] not found in the possible search paths!\n[%s]",
                        $file,
                        str_replace(' ', '    ',var_export($search_paths,1))
                    )
                );

            try
            {
                include $file;
                $ref = NULL;
                eval("\$ref = & \$".$var.";");
                if (isset($ref) && is_array($ref)) {
                    self::log('adding form data: '.str_replace(' ', '    ',var_export($ref,1)),7);
                    self::$FORMS = array_merge(self::$FORMS, $ref);
                }
            }
            catch ( wbFormsException $e )
            {
                self::log(sprintf('unable to load the file, exception [%s]',$e->getMessage()));
            }

        }   // end function loadFile()

        /***********************************************************************
         *    NON STATIC METHODS
         **********************************************************************/

        /**
         * allows to add custom css files; they will be loaded into the HTML
         * header using JavaScript (jQuery)
         *
         * @access public
         * @param  string  $url
         * @return
         **/
        public function addCSS($url)
        {
            self::$config['css_files'][] = $url;
        }   // end function addCSS()

        /**
         * creates a button line (a list of submit buttons enclosed in a div)
         *
         * @access public
         * @return string
         **/
        public function getButtonline()
        {
            // add default buttons (submit, reset)
            $output  = wbFormsElementButton::get(
                array(
                    'id'      => 'submit_'.wbForms::$CURRENT,
                    'label'   => 'Submit',
                    'style'   => 'float:left',
                    'onclick' => "$('#".wbForms::$CURRENT."').submit()",
                ))->render();
            $output .= wbFormsElementButton::get(
                array(
                    'id'      => 'reset_'.wbForms::$CURRENT,
                    'label'   => 'Reset',
                    'style'   => 'float:left',
                    'onclick' => "getElementById('".wbForms::$CURRENT."').reset();",
                ))->render();
            wbFormsJQuery::addComponent('submit_'.wbForms::$CURRENT,'button', 'icons: { primary: "ui-icon-circle-check" }');
            wbFormsJQuery::addComponent('reset_'.wbForms::$CURRENT,'button', 'icons: { primary: "ui-icon-closethick" }');
            return str_ireplace(
                '%buttons%',
                $output,
                self::$config['button_line']
            );
        }   // end function getButtonline()

        /**
         * get (render) the form; direct output (echo) by default
         *
         * @access public
         * @param  string  $name   - form to render
         * @param  boolean $return - set to true to return the HTML
         * @return string
         **/
        public function getForm($name='default',$return=false)
        {
            // set $CURRENT
            $this->setForm($name);

            // check if the form is available and has components
            if(!isset(self::$FORMS[$name]))
            {
                self::log(sprintf('Required form [%s] not found!',$name),4);
                return false;
            }
            if(!count(self::$FORMS[$name]))
            {
                self::log(sprintf('Required form [%s] has no elements!',$name),4);
                return false;
            }

            // render the elements
            $elements = array();
            foreach(self::$FORMS[$name] as $elem)
            {
                $classname = 'wblib\wbFormsElement'.ucfirst(strtolower($elem['type']));
                if(class_exists($classname))
                {
                    $elements[] = $classname::get($elem)->render();
                    continue;
                }
                $elements[] = wbFormsElement::get($elem)->render();
            }

            // make sure we have a submit button
            $elements[] = self::getButtonline();

            // finish the form
            if ( count($elements) )
            {
                if (!$return) echo   self::render(implode('',$elements));
                else          return self::render(implode('',$elements));
            }
        }   // end function getForm()

        /**
         * loads the form elements; this allows to call printHeaders() to load
         * all the CSS and JS into the <head>
         *
         * @access public
         * @param  string  $name   - form name
         * @return boolean
         **/
        public function setForm($name='default')
        {
            if(isset(self::$FORMS[$name]))
            {
                wbForms::$CURRENT = $name;
                $this->attr['id'] = $name;
            }
        }   // end function setForm()
        
        /**
         *
         * @access public
         * @return
         **/
        public function printHeaders()
        {
            echo wbFormsJQuery::getComponents(true);
        }   // end function printHeaders()
        
        /**
         *
         * @access public
         * @return
         **/
        public function printForm($name=NULL)
        {
            if(!$name)
                $name = wbForms::$CURRENT;
            self::log(sprintf('calling getForm(%s)',$name),7);
            $this->getForm($name);
        }   // end function printForm()
        

        /**
         * render the form
         *
         * @access public
         * @param  string  $form - form contents
         * @return
         **/
        public static function render($form)
        {
            if ( self::$config['form_style'] != '' )
                self::$config['style']
                    = ( isset(self::$config['style']) && self::$config['style'] != '' )
                    ? self::$config['style'] . self::$config['form_style']
                    : self::$config['form_style']
                    ;

            if(!isset(self::$attributes['action']))
                self::$attributes['action'] = $_SERVER['SCRIPT_NAME'];

            $output = wbForms::$tpl;
            foreach( wbFormsElement::$add_key as $key )
            {
                $output = str_ireplace(
                    '%'.$key.'%',
                    ( isset(self::$config[$key]) ? $key.'="'.self::$config[$key].'" ' : '' ),
                    $output
                );
            }
            $output =  str_ireplace(
                array_map(
                    function( $e ) { return "%$e%"; },
                    array_keys(self::$attributes)
                ),
                array_merge(
                    self::$attributes,
                    self::$config,
                    array(
                        'content' => $form,
                        'id'      => self::$CURRENT,
                        'name'    => self::$CURRENT
                    )
                ),
                $output
            );
            $output .= wbFormsJQuery::getComponents();
            return $output;
        }   // end function render()
    }   // ----------  end class wbForms ----------

    /**
     * form builder jQuery interface class
     *
     * @category   wblib2
     * @package    wbFormsJQuery
     * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    if ( ! class_exists( 'wbFormsJQuery', false ) )
    {
        class wbFormsJQuery extends wbFormsBase
        {
            /**
             * list of UI components to load
             **/
            public static $ui_components = array();

            /**
             * simple way to add a jQuery UI component; will not set any
             * options
             *
             * @access public
             * @param  string  $id   - HTML element id
             * @param  string  $name - component name
             * @return string
             **/
            public static function addComponent($id,$name,$options=NULL)
            {
                if ( ! isset(self::$ui_components[$id]) )
                    self::$ui_components[$id] = array();
                array_push(
                    self::$ui_components[$id],
                      'jQuery("#'.$id.'").'.$name.'('
                    . ( $options ? '{ '.$options.' }' : '' )
                    . ');'
                );
            }   // end function addComponent()

            /**
             * returns the necessary JS to load the configured components
             *
             * @access public
             * @return mixed  - string or NULL if no components where found
             **/
            public static function getComponents($called_from_header=false)
            {
                $code   = NULL;
                $output = NULL;
                // always load the core if UI is required
                if(count(self::$ui_components))
                {
                    self::$config['jquery']    = true;
                    self::$config['jquery_ui'] = true;
                }
                if(self::$config['jquery'])
                    $output = '<script type="text/javascript" src="'
                            . self::$config['jquery_src']
                            . '"></script>'
                            . "\n";
                if(self::$config['jquery_ui'])
                {
                    $output .= '<script type="text/javascript" src="'
                            .  self::$config['jquery_ui_src']
                            .  '"></script>'
                            .  "\n";
                    if ( $called_from_header )
                        $output .= '<link rel="stylesheet" href="'
                                .  str_ireplace('%theme%',self::$config['jquery_ui_theme'],self::$config['jquery_ui_css'])
                                .  '" type="text/css" media="screen" />'
                                ;
                    else
                        $code   = "\n\t\t"
                                .  '$("head").append(\'<link rel="stylesheet" href="'
                                .  str_ireplace('%theme%',self::$config['jquery_ui_theme'],self::$config['jquery_ui_css'])
                                .  '" type="text/css" />\');'
                                ;
                }
                if(count(self::$config['css_files']))
                    foreach(self::$config['css_files'] as $url)
                        $code   .= "\n\t\t"
                                .  '$("head").append(\'<link rel="stylesheet" href="'
                                .  $url
                                .  '" type="text/css" />\');'
                                ;

                self::$ui_components = wbArray::ArrayUniqueRecursive(self::$ui_components);

                if(count(self::$ui_components))
                    $code   .= "\n\t\t"
                            . implode(
                                  "\n\t\t",
                                  array_map(
                                      function ($e) { return $e[0]; },
                                      array_values(self::$ui_components)
                                  )
                              );

                if($output||$code)
                    return $output . ( $code ? self::render($code) : '' );

                return NULL;
            }   // end function getComponents()
            
            /**
             * render
             *
             * @access public
             * @return string
             **/
            public static function render($code)
            {
                return
                    '<script type="text/javascript">'."\n".
                    'if ( typeof jQuery !== \'undefined\' ) {'."\n".
                    '    jQuery(document).ready(function($) {'."\n".
                             $code."\n".
                    '    });'."\n".
                    '}'."\n".
                    '</script>'."\n"
                    ;
            }   // end function render()
        }
    }   // ---------- end class wbFormsJQuery ----------

    /**
     * form builder element base class
     *
     * @category   wblib2
     * @package    wbFormsElement
     * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    if ( ! class_exists( 'wbFormsElement', false ) )
    {
        class wbFormsElement extends wbFormsBase
        {
            /**
             * default attributes for every element
             **/
            protected $attributes = array(
                // HTML attributes
                'accesskey' => NULL,
                'class'     => NULL,
                'disabled'  => false,
                'id'        => NULL,
                'infotext'  => NULL,
                'label'     => NULL,
                'name'      => NULL,
                'onblur'    => NULL,
                'onchange'  => NULL,
                'onclick'   => NULL,
                'onfocus'   => NULL,
                'onselect'  => NULL,
                'readonly'  => false,
                'required'  => false,
                'style'     => NULL,
                'tabindex'  => NULL,
                'value'     => NULL,
                // internal attributes
                'allow'     => NULL,
                'equal_to'  => NULL,
                'invalid'   => NULL,
                'missing'   => NULL,
                'type'      => NULL,
                'required_span' => NULL,
            );
            /**
             *
             **/
            public static $add_key = array(
                'value'   , 'tabindex', 'accesskey', 'class'   , 'style'   ,
                'disabled', 'readonly', 'required' , 'checked' , 'selected',
                'onblur'  , 'onchange', 'onclick'  , 'onfocus' , 'onselect',
            );
            /**
             * default output template
             **/
            public static $tpl
                = "%required_span%%label%<input type=\"%type%\" name=\"%name%\" id=\"%id%\" %value%%required%%tabindex%%accesskey%%class%%style%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect%/>\n";
            /**
             *
             **/
            protected $attr = array(
                'style' => 'margin:5px auto;'
            );

            /**
             * creates a new form element
             *
             * @access public
             * @param  array  $options
             * @return object
             **/
            public function __construct($options=array())
            {
                $this->init();
                foreach($this->attributes as $key => $default)
                {
                    if(isset($options[$key]))
                        $this->attr[$key] = $options[$key];
                    else
                        if($default)
                            $this->attr[$key] = $default;
                }
            }   // end function __construct()

            /**
             * returns an instance (normally 'getInstance()')
             *
             * @access public
             * @param  array  $options
             * @return
             **/
            public static function get($options=array())
            {
                $class = get_called_class();
                return new $class($options);
            }   // end function get()

            /**
             * generates an unique element name if none is given
             *
             * @access protected
             * @param  integer  $length
             * @return string
             **/
            protected function generateName( $length = 5 ) {
                for(
                       $code_length = $length, $newcode = '';
                       strlen($newcode) < $code_length;
                       $newcode .= chr(!rand(0, 2) ? rand(48, 57) : (!rand(0, 1) ? rand(65, 90) : rand(97, 122)))
                );
                return 'fbformfield_'.$newcode;
            }   // end function generateName()

            /**
             * function prototype, element classes may override this to add
             * custom attributes
             *
             * @access public
             * @return object (chainable)
             **/
            public function init()
            {
                return $this;
            }   // end function init()

            /**
             * checks for required attributes like 'id' and 'name'
             *
             * @access public
             * @return void
             **/
            public function checkAttr()
            {
                if(!isset($this->attr['name']))
                    $this->attr['name'] = wbFormsElement::generateName();
                if(!isset($this->attr['id']))
                    $this->attr['id'] = $this->attr['name'];
                if(isset($this->attr['required']) && $this->attr['required'] === true)
                {
                    $this->attr['required_span'] = self::$config['required_span'];
                    $this->attr['required']      = 'required'; // valid XHTML
                    $this->attr['class'] =
                        (
                            isset($this->attr['class'])
                          ? $this->attr['class'].' ui-state-highlight'
                          : 'ui-state-highlight'
                        );
                }
                else
                {
                    $this->attr['required_span'] = self::$config['blank_span'];
                }
                self::log('attributes: '.var_export($this->attr,1),7);
            }   // end function checkAttr()

            /**
             * default render method for most element types; for those who need
             * different markup, this method must be overridden
             *
             * @access public
             * @return string  - HTML
             **/
            public function render()
            {
                return $this->replaceAttr();
        	}   // end function render()

            /**
             * replaces the placeholders in the output template with the
             * appropriate element options
             *
             * @access protected
             * @return string
             **/
            protected function replaceAttr($tpl_var='tpl')
            {
                $this->checkAttr();

                if(isset($this->attr['label']) && !$this instanceof wbFormsElementLabel && !$this instanceof wbFormsElementButton)
                {
                    $label = new wbFormsElementLabel(array('id'=>$this->attr['id'],'label'=>self::t($this->attr['label'])));
                    $this->attr['label'] = $label->render();
                }

                $class  = get_called_class();
                $output = $class::${$tpl_var};
                foreach( self::$add_key as $key )
                {
                    $output = str_ireplace(
                        '%'.$key.'%',
                        ( isset($this->attr[$key]) ? $key.'="'.$this->attr[$key].'" ' : '' ),
                        $output
                    );
                }
                self::log('template after replacing keyed placeholders',7);
                self::log($output,7);

                $output = str_ireplace(
                    array_map(
                        function( $e ) { return "%$e%"; },
                        array_keys($this->attributes)
                    ),
                    array_merge($this->attributes,$this->attr),
                    $output
                );
                self::log('template after replacing normal placeholders',7);
                self::log($output,7);

                // remove any placeholders not replaced yet
                $output = preg_replace( '~%\w+%~', '', $output );

                return $output
                    . (
                          (
                                 wbForms::$config['breaks']
                              && !$this instanceof wbFormsElementFieldset
                              && !$this instanceof wbFormsElementLegend
                              && !$this instanceof wbFormsElementLabel
                              && !$this instanceof wbFormsElementButton
                              && !$this instanceof wbFormsElementRadio
                          )
                        ? '<br />'
                        : ''
                      );
            }   // end function replaceAttr()
            
        }   // ---------- end class wbFormsElement ----------

/*******************************************************************************
 * special field types
 ******************************************************************************/

        /**
         * form builder fieldset element class
         *
         * @category   wblib2
         * @package    wbFormsElementFieldset
         * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
         * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
         */
        class wbFormsElementFieldset extends wbFormsElement
        {
            private static $is_open
                = false;
            public  static $tpl
                = '<fieldset %class%%style%>';
            public $attr
                = array(
                      'class' => 'ui-widget ui-widget-content ui-corner-all ui-helper-clearfix',
                      'style' => 'margin-bottom:15px;',
                  );

            /**
             * open a <fieldset>; this also closes any fieldset that was opened
             * before
             *
             * please note that fieldsets cannot be nested!
             *
             * @access public
             * @return string
             **/
            public function open()
            {
                $close = self::close();
                self::$is_open = true;
                return $close.$this->replaceAttr();
            }   // end function open()

            /**
             * closes a <fieldset>
             *
             * @access public
             * @return string
             **/
            public function close()
            {
                if(self::$is_open)
                {
                    self::$is_open = false;
                    return '</fieldset>';
                }
            }   // end function close()
        }   // ---------- end class wbFormsElementFieldset ----------

        /**
         * form builder legend element class; auto-opens a fieldset
         *
         * @category   wblib2
         * @package    wbFormsElementLegend
         * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
         * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
         */
        class wbFormsElementLegend extends wbFormsElement
        {
            public static $tpl
                = "<legend id=\"%id%\" %class%%style%>%label%</legend>\n";
            public $attr = array(
                'class' => 'ui-widget ui-widget-header ui-corner-all',
                'style' => 'padding: 5px 10px;'
            );
            /**
             *
             * @access public
             * @return
             **/
            public function render()
            {
                 return
                       wbFormsElementFieldset::get()->open()
                     . $this->replaceAttr();
            }   // end function render()
        }   // ---------- end class wbFormsElementLegend ----------

        /**
         * form builder label element class
         *
         * @category   wblib2
         * @package    wbFormsElementLabel
         * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
         * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
         */
        class wbFormsElementLabel extends wbFormsElement
        {
            public static $tpl
                = '<label for="%id%" %class%%style%>%label%</label>';
            public $attr = array(
                'class'    => 'fblabel',
                //'style'    => 'display:inline-block;min-width:250px;margin-left:15px;',
            );
        }   // ---------- end class wbFormsElementLabel ----------

        /**
         * form builder textarea element class
         *
         * @category   wblib2
         * @package    wbFormsElementTextarea
         * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
         * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
         */
        class wbFormsElementTextarea extends wbFormsElement
        {
            public static $tpl
                = '%required_span%%label%<textarea name="%name%" id="%id%" %tabindex%%accesskey%%class%%style%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect%>%value%</textarea>';
        }   // ---------- end class wbFormsElementTextarea ----------

        /**
         * form builder select element class
         *
         * @category   wblib2
         * @package    wbFormsElementSelect
         * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
         * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
         */
        class wbFormsElementSelect extends wbFormsElement
        {
            public static $tpl
                = '%required_span%%label%<select name="%name%" id="%id%" %multiple%%tabindex%%accesskey%%class%%style%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect%>%options%</select>';
            /**
             * adds select specific attributes
             **/
            public function init()
            {
                $this->attributes['options']  = NULL;
                $this->attributes['selected'] = NULL;
                $this->attributes['multiple'] = NULL;
                return $this;
            }
            public function render()
            {
                $options   = array();
                $isIndexed = array_values($this->attr['options']) === $this->attr['options'];
                $sel       = array();
                if(isset($this->attr['selected']))
                    $sel[$this->attr['selected']] = 'selected="selected"';
                if(isset($this->attr['multiple']))
                    $this->attr['multiple'] = 'multiple="multiple"';
                if($isIndexed)
                    foreach($this->attr['options'] as $item)
                        $options[] = '<option value="'.$item.'" '.( isset($sel[$item]) ? $sel[$item] : '' ).'>'.$item.'</option>'."\n";
                else
                    foreach($this->attr['options'] as $value => $item)
                        $options[] = '<option value="'.$value.'" '.( isset($sel[$value]) ? $sel[$value] : '' ).'>'.$item.'</option>'."\n";
                $this->attr['options'] = implode('',$options);
                return $this->replaceAttr();
            }   // end function render()
        }   // ---------- end class wbFormsElementSelect ----------

        /**
         * form builder radio element class
         *
         * @category   wblib2
         * @package    wbFormsElementRadio
         * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
         * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
         */
        class wbFormsElementRadio extends wbFormsElement
        {
            public static $tpl
                = "<input type=\"%type%\" name=\"%name%\" id=\"%id%\" %value%%checked%%required%%tabindex%%accesskey%%class%%style%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect%/> %label%\n";
            public function init()
            {
                $this->attributes['checked'] = NULL;
                return $this;
            }
        }   // ---------- end class wbFormsElementRadio ----------

        /**
         * form builder radio group class
         *
         * groups a list of radio elements
         *
         * @category   wblib2
         * @package    wbFormsElementRadioGroup
         * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
         * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
         */
        class wbFormsElementRadiogroup extends wbFormsElement
        {
            public  static $tpl
                = '%required_span%<div class="radiogroup" id="%id%">%options%</div>';
            private static $number = 0;
            public function init()
            {
                $this->attributes['type']    = 'radio';
                $this->attributes['checked'] = 'checked';
                $this->attributes['options'] = array();
                return $this;
            }
            public function render()
            {
                $this->checkAttr();
                self::$number++;
                $options   = array();
                $isIndexed = array_values($this->attr['options']) === $this->attr['options'];
                foreach( $this->attr['options'] as $key => $value )
                {
                    $options[] = wbFormsElementRadio::get(
                        array(
                            'type'    => 'radio',
                            'name'    => $this->attr['name'],
                            'id'      => $this->attr['name'].'_'.$value,
                            'label'   => ( $isIndexed ? $value : $key ),
                            'value'   => $value,
                        ))->render();
                }
                $this->attr['options'] = implode( "\n", $options );
                $this->attr['id']      = 'radiogroup_'.self::$number;
                wbFormsJQuery::addComponent($this->attr['id'],'buttonset');
                return $this->replaceAttr();
            }
        }   // ---------- end class wbFormsElementRadio ----------

        /**
         * form builder date element class; uses jQuery DatePicker
         *
         * @category   wblib2
         * @package    wbFormsElementDate
         * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
         * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
         */
        class wbFormsElementDate extends wbFormsElement
        {
            public function render()
            {
                $this->checkAttr();
                wbFormsJQuery::addComponent($this->attr['id'],'datepicker');
                $this->attr['type'] = 'text';
                return $this->replaceAttr();
            }   // end function render()
        }   // ---------- end class wbFormsElemetDate ----------

        /**
         * form builder button element; used for submit, reset
         *
         * @category   wblib2
         * @package    wbFormsElementButton
         * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
         * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
         */
        class wbFormsElementButton extends wbFormsElement
        {
            public $attr = array(
                'class' => 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary'
            );
            public static $tpl = '<button name="%name%" id="%id%" %tabindex%%accesskey%%class%%style%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect%>%label%</button>';
        }   // ---------- end class wbFormsElementButton ----------

    }



    if ( ! class_exists( 'wblib\wbArray', false ) )
    {
        class wbArray
        {
          	/**
             *
             *
             *
             *
             **/
            public static function ArrayUniqueRecursive( $array, $case_sensitive = false ) {
        		$set = array();
        		$out = array();
        		foreach ( $array as $key => $val ) {
        			if ( is_array($val) ) {
        			    $out[$key] = self::ArrayUniqueRecursive($val,$case_sensitive);
        			}
        			else {
        			    $seen_val = ( ( $case_sensitive === true ) ? $val : strtolower($val) );
        			    if( ! isset($set[$seen_val]) ) {
    						$out[$key] = $val;
    					}
    					$set[$seen_val] = 1;
                	}
        		}
        		return $out;
       		}   // end function ArrayUniqueRecursive()

           /**
             * Found here:
             * http://www.php.net/manual/en/function.array-change-key-case.php#107715
             **/
            public static function array_change_key_case_unicode($arr, $c = CASE_LOWER) {
                $c = ($c == CASE_LOWER) ? MB_CASE_LOWER : MB_CASE_UPPER;
                foreach ($arr as $k => $v) {
                    $ret[mb_convert_case($k, $c, "UTF-8")] = $v;
                }
                return $ret;
            }   // end function array_change_key_case_unicode()

            /**
             * sort an array
             *
             *
             *
             **/
            public static function sort ( $array, $index, $order='asc', $natsort=FALSE, $case_sensitive=FALSE )
            {
                if( is_array($array) && count($array)>0 ) {
                     foreach(array_keys($array) as $key) {
                         $temp[$key]=$array[$key][$index];
                     }
                     if(!$natsort) {
                         ($order=='asc')? asort($temp) : arsort($temp);
                     }
                     else {
                         ($case_sensitive)? natsort($temp) : natcasesort($temp);
                         if($order!='asc') {
                             $temp=array_reverse($temp,TRUE);
                         }
                     }

                     foreach(array_keys($temp) as $key) {
                         (is_numeric($key))? $sorted[]=$array[$key] : $sorted[$key]=$array[$key];
                     }
                     return $sorted;
                }
                return $array;
            }   // end function sort()

        }
    }

    /**
     * validation helper methods
     *
     * @category   wblib2
     * @package    wbValidate
     * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    if ( ! class_exists( 'wblib\wbValidate', false ) )
    {
        class wbValidate
        {
            /**
             * fixes a path by removing //, /../ and other things
             *
             * @access public
             * @param  string  $path - path to fix
             * @return string
             **/
            public static function path( $path )
            {
                // remove / at end of string; this will make sanitizePath fail otherwise!
                $path       = preg_replace( '~/{1,}$~', '', $path );
                // make all slashes forward
                $path       = str_replace( '\\', '/', $path );
                // bla/./bloo ==> bla/bloo
                $path       = preg_replace('~/\./~', '/', $path);
                // resolve /../
                // loop through all the parts, popping whenever there's a .., pushing otherwise.
                $parts      = array();
                foreach ( explode('/', preg_replace('~/+~', '/', $path)) as $part )
                {
                    if ($part === ".." || $part == '')
                        array_pop($parts);
                    elseif ($part!="")
                        $parts[] = $part;
                }
                $new_path = implode("/", $parts);
                // windows
                if ( ! preg_match( '/^[a-z]\:/i', $new_path ) )
                    $new_path = '/' . $new_path;
                return $new_path;
            }   // end function path()
        }
    }

    class wbFormsException extends \Exception {
        public function __construct($message, $code = 0) {
            wbForms::log($message,7);
            parent::__construct($message, $code);
        }
    }

}