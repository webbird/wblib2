<?php

/**
 *          _     _  _ _     ______
 *          |   | |(_) |   (_____ \
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
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 **/

namespace wblib;

/**
 * SQL abstraction class
 *
 * @category   wblib2
 * @package    wbForms
 * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
 * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
 */
if ( ! class_exists( 'wbForms', false ) )
{
    class wbFormsBase
    {
        /**
         * logger
         **/
        private   static $analog     = NULL;
        /**
         *
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
            'formstyle'       => NULL,
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
         *
         * @access public
         * @return
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

    class wbForms extends wbFormsBase
    {

        /**
         * array of named instances (=forms)
         **/
        protected static $instances  = array();
        /**
         * log level
         **/
        protected static $loglevel  = 7;
        /**
         *
         **/
        protected static $FORMS     = array();
        /**
         *
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
echo "---calling [$method]---<br />";
print_r($args);
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }   // end function __call()

        /**
         * constructor; private to make sure that it can only be called
         * using getInstance()
         *
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
                // add breaks after each element
                'breaks'          => true,
                // form width
                'formstyle'       => 'width:800px;margin:10px auto;',
                // list of custom css files to add
                'css_files'       => array(),
                // form wrapper div class
                'wrapperclass'    => 'fbform',
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
                'required_span'   => '<span class="fbrequired" style="display:inline-block;width:15px;color:#B94A48;">*</span>',
                'blank_span'      => '<span class="fbblank" style="display:inline-block;width:15px;">&nbsp;</span>',
                'required_style'  => NULL,
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
         * Create an instance (i.e. a single form)
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

/*******************************************************************************
 *    NON STATIC METHODS
 ******************************************************************************/

        /**
         *
         * @access public
         * @return
         **/
        public function addCSS($url)
        {
            self::$config['css_files'][] = $url;
        }   // end function addCSS()

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
            $output = NULL;
            foreach(self::$FORMS[$name] as $elem)
            {
                $classname = 'wblib\wbFormsElement'.ucfirst(strtolower($elem['type']));
                if(class_exists($classname))
                {
                    $output .= $classname::get($elem)->render();
                    continue;
                }
                $output .= wbFormsElement::get($elem)->render();
            }
            // add submit button
            $output .= wbFormsElementSubmit::get(array('type'=>'submit','style'=>'float:left'))->render();
            $output .= wbFormsElementSubmit::get(array('type'=>'reset','style'=>'float:left'))->render();
            if ( $output )
            {
                if (!$return) echo   self::render($output);
                else          return self::render($output);
            }
        }   // end function getForm()

        /**
         *
         * @access public
         * @return
         **/
        public static function render($form)
        {
            if ( self::$config['formstyle'] != '' )
                self::$config['style']
                    = ( isset(self::$config['style']) && self::$config['style'] != '' )
                    ? self::$config['style'] . self::$config['formstyle']
                    : self::$config['formstyle']
                    ;
            $output = self::$tpl;
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
                        'content'=>$form,
                    )
                ),
                $output
            );
            $output .= wbFormsJQuery::getComponents();
            return $output;
        }   // end function render()
        

    }   // ----------  end class wbForms ----------

    if ( ! class_exists( 'wbFormsJQuery', false ) )
    {
        class wbFormsJQuery extends wbFormsBase
        {
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
            public static function addComponent($id,$name)
            {
                if ( ! isset(self::$ui_components[$id]) )
                    self::$ui_components[$id] = array();
                array_push( self::$ui_components[$id], 'jQuery("#'.$id.'").'.$name.'();' );
            }   // end function addComponent()

            /**
             *
             * @access public
             * @return
             **/
            public static function getComponents()
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
                    $code    = "\n\t\t"
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
                $code   .= "\n\t\t"
                        . implode(
                              "\n\t\t",
                              array_map(
                                  function ($e) { return $e[0]; },
                                  array_values(array_unique(self::$ui_components)))
                          );

                if($output||$code)
                    return $output . self::render($code);

                return NULL;
            }   // end function getComponents()
            
            /**
             *
             * @access public
             * @return
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
                'style'     => NULL,
                'tabindex'  => NULL,
                'value'     => NULL,
                // internal attributes
                'allow'     => NULL,
                'equal_to'  => NULL,
                'invalid'   => NULL,
                'missing'   => NULL,
                'required'  => false,
                'type'      => NULL,
            );
            /**
             *
             **/
            public static $add_key = array(
                'value'   , 'tabindex', 'accesskey', 'class'  , 'style'  , 'disabled',
                'readonly', 'onblur'  , 'onchange' , 'onclick', 'onfocus', 'onselect',
            );
            /**
             * default output template
             **/
            public static $tpl
                = "%required%%label%<input type=\"%type%\" name=\"%name%\" id=\"%id%\" %value%%tabindex%%accesskey%%class%%style%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect%/>\n";
            /**
             *
             **/
            protected $attr = array();

            /**
             *
             * @access public
             * @return
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
             *
             * @access public
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
            protected function generateName( $length = 10 ) {
                for(
                       $code_length = $length, $newcode = '';
                       strlen($newcode) < $code_length;
                       $newcode .= chr(!rand(0, 2) ? rand(48, 57) : (!rand(0, 1) ? rand(65, 90) : rand(97, 122)))
                );
                return $newcode;
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
             *
             * @access public
             * @return
             **/
            public function checkAttr()
            {
                if(!isset($this->attr['name']))
                    $this->attr['name'] = wbFormsElement::generateName();
                if(!isset($this->attr['id']))
                    $this->attr['id'] = $this->attr['name'];
                if(isset($this->attr['required']) && $this->attr['required'] === true)
                    $this->attr['required'] = self::$config['required_span'];
                else
                    $this->attr['required'] = self::$config['blank_span'];
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
             *
             * @access private
             * @return
             **/
            protected function replaceAttr()
            {
                $this->checkAttr();

                if(isset($this->attr['label']) && !$this instanceof wbFormsElementLabel)
                {
                    $label = new wbFormsElementLabel(array('id'=>$this->attr['id'],'label'=>self::t($this->attr['label'])));
                    $this->attr['label'] = $label->render();
                }

                $class  = get_called_class();
                $output = $class::$tpl;
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

                return $output
                    . (
                          (
                                 wbForms::$config['breaks']
                              && !$this instanceof wbFormsElementFieldset
                              && !$this instanceof wbFormsElementLegend
                              && !$this instanceof wbFormsElementLabel
                              && !$this instanceof wbFormsElementSubmit
                          )
                        ? '<br />'
                        : ''
                      );
            }   // end function replaceAttr()
            
        }   // ---------- end class wbFormsElement ----------

/*******************************************************************************
 * special field types
 ******************************************************************************/

        class wbFormsElementFieldset extends wbFormsElement
        {
            private static $is_open = false;
            public  static $tpl
               = '<fieldset %class%%style%>';
            public $attr
               = array(
                     'class' => 'ui-widget ui-widget-content ui-corner-all ui-helper-clearfix',
                     'style' => 'margin-bottom:15px;',
                 );

            /**
             *
             * @access public
             * @return
             **/
            public function open()
            {
                $close = self::close();
                self::$is_open = true;
                return $close.$this->replaceAttr();
            }   // end function open()

            /**
             *
             * @access public
             * @return
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
         * legend; also opens a fieldset
         **/
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
         * label
         **/
        class wbFormsElementLabel extends wbFormsElement
        {
            public static $tpl
                = '<label for="%id%" %class%%style%>%label%</label>';
            public $attr = array(
                'class'    => 'fblabel',
                'style'    => 'display:inline-block;min-width:250px;margin-left:15px;',
            );
        }   // ---------- end class wbFormsElementLabel ----------

        class wbFormsElementTextarea extends wbFormsElement
        {
            public static $tpl
                = '%required%%label%<textarea name="%name%" id="%id%" %tabindex%%accesskey%%class%%style%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect%>%value%</textarea>';
        }   // ---------- end class wbFormsElementTextarea ----------

        class wbFormsElementSelect extends wbFormsElement
        {
            public static $tpl
                = '%required%%label%<select name="%name%" id="%id%" %tabindex%%accesskey%%class%%style%%disabled%%readonly%%onblur%%onchange%%onclick%%onfocus%%onselect%>%options%</select>';
            public function init()
            {
                $this->attributes['options'] = NULL;
                return $this;
            }
            public function render()
            {
                $options = array();
                foreach($this->attr['options'] as $item)
                    $options[] = '<option value="">'.$item.'</option>'."\n";
                $this->attr['options'] = implode('',$options);
                return $this->replaceAttr();
            }
        }   // ---------- end class wbFormsElementSelect ----------

        /**
         * special date field; adds jQuery UI DatePicker
         **/
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
         * buttons
         **/
        class wbFormsElementSubmit extends wbFormsElement
        {
            public $attr = array(
                'class' => 'ui-button ui-widget ui-state-default ui-corner-all'
            );
        }   // ---------- end class wbFormsElementSubmit ----------

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