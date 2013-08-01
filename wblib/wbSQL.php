<?php

/**
 *
 *          _     _  _ _
 *         | |   | |(_) |
 *    _ _ _| |__ | | _| |__
 *   | | | |  _ \| || |  _ \
 *   | | | | |_) ) || | |_) )
 *   \___/|____/ \_)_|____/
 *
 *
 *   @category     wblib
 *   @package      wbSQL
 *   @author       BlackBird Webprogrammierung
 *   @copyright    (c) 2013 BlackBird Webprogrammierung
 *   @license      GNU LESSER GENERAL PUBLIC LICENSE Version 3
 *
 **/

namespace wblib;

/**
 * SQL abstraction class
 *
 * @category   wblib
 * @package    wbSQL
 * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
 * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
 */
if ( ! class_exists( 'wbSQL', false ) )
{
    class wbSQL {

        /**
         * array of named instances
         **/
        public static $instances = array();
        /**
         * logger
         **/
        private static $analog   = NULL;
        /**
         * log level
         **/
        public static $loglevel  = 0;
        /**
         * default driver
         **/
        public static $driver    = 'MySQL';
        /**
         * array of options
         **/
        public        $options   = array();

        // private to make sure that constructor can only be called
        // using getInstance()
        private function __construct() {
            
        }    // end function __construct()

        // no cloning!
        private function __clone() {}

        /**
         * Create an instance (i.e. a database connection)
         *
         * First argument may be an array of options that will be passed to
         * the connect() method of the driver.
         *
         * If you need to have more than one connection, you may pass an
         * optional connection name (default: 'default')
         *
         * 'connection_name' => 'myconnection'
         *
         * @access public
         * @param  array   $options    - OPTIONAL; options to be passed to connect()
         * @return object
         **/
        public static function getInstance( $options = array() )
        {
            $connection = isset($options['connection_name'])
                        ? $options['connection_name']
                        : 'default';
            if ( !array_key_exists( $connection, self::$instances ) ) {
                self::log(sprintf('creating new instance with name [%s]',$connection),7);
                self::log(var_export($options,1),7);
                self::$instances[$connection] = self::__connect($options);
            }
            return self::$instances[$connection];
        }   // end function getInstance()

        /**
         * private method to establish a database connection, using the
         * appropriate driver
         *
         * @access private
         * @param  array   $options
         * @return object
         **/
        private static function __connect( $options )
        {
            $driver = isset( $options['driver'] )
                    ? $options['driver']
                    : self::$driver;
            try {
                $classname = 'wblib\\'.$driver;
                return new $classname($options);
            } catch (wbSQLException $e) {
                self::log($e->getMessage);
                echo $e->getMessage();
            }
        }   // end function __connect()

        /**
         * accessor to Analog (if installed)
         *
         * Note: Log messages are ignored if no Analog is available!
         *
         * @access private
         * @param  string   $message
         * @param  integer  $level
         * @return
         **/
        private static function log($message, $level = 3)
        {
            if($level<>self::$loglevel) return;
            if( !self::$analog && !self::$analog == -1)
            {
                if(file_exists(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php'))
                {
                    include_once(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php');
                    wblib_init_3rdparty(dirname(__FILE__).'/debug/','wbSQL',self::$loglevel);
                    self::$analog = true;
                }
                else
                {
                    self::$analog = -1;
                }
            }
            if ( self::$analog )
                \Analog::log($message,$level);
        }   // end function log()
        
    }

    class wbSQLException extends \Exception {}
}

interface wbSQL_DriverInterface
{
    function getDSN           ();
    function getDriverOptions ();
    function search           ( $options );
    function insert           ( $options );
    function update           ( $options );
    function replace          ( $options );
    function delete           ( $options );
    function truncate         ( $options );
    function min              ( $fieldname, $options );
    function group_by         ( $group_by );
    function limit            ( $limit );
    function map_tables       ( $tables   , $options );
    function order_by         ( $order_by );
    function parse_join       ( $tables   , $options );
    function parse_where      ( $where );
    function max              ( $fieldname, $options );
    function showTables       ();
}   // end interface wbSQL_DriverInterface


/**
 * default SQL driver class
 *
 * @category   wblib
 * @package    wbSQL
 * @copyright  Copyright (c) 2013 BlackBird Webprogrammierung
 * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
 */
if ( ! class_exists( 'wbSQLDriver', false ) )
{
    class wbSQL_Driver extends \PDO
    {

        protected $dsn                  = NULL;
        protected $host                 = "localhost";
        protected $port                 = NULL;
        protected $user                 = NULL;
        protected $pass                 = NULL;
        protected $dbname               = "mydb";
        protected $prefix               = NULL;
        protected $timeout              = 5;

        /**
         * error stack
         **/
        protected $errors               = array();
        protected $lasterror            = NULL;
        /**
         * statement properties
         **/
        protected $statement            = NULL;
        protected $lastInsertID         = NULL;
        /**
         * logger
         **/
        private static $analog          = NULL;
        /**
         * log level
         **/
        public static $loglevel         = 7;

// ----- Operators used in WHERE-clauses -----
        protected $operators  = array(
            '='  => '=',
            'eq' => '=',
            'ne' => '<>',
            '==' => '=',
            '!=' => '<>',
            '=~' => 'REGEXP',
            '!~' => 'NOT REGEXP',
            '~~' => 'LIKE'
        );

// ----- Conjunctions used in WHERE-clauses -----
        protected $conjunctions = array(
            'and'  => 'AND',
            'AND'  => 'AND',
            'OR'   => 'OR',
            'or'   => 'OR',
            '&&'   => 'AND',
            '\|\|' => 'OR',
        );

// ----- Known options for constructor -----
        protected $_options = array(
            array(
                'name' => 'dsn',
                'type' => 'string',
            ),
            array(
                'name' => 'host',
                'type' => 'string',
            ),
            array(
                'name' => 'port',
                'type' => 'integer',
            ),
            array(
                'name' => 'user',
                'type' => 'string',
            ),
            array(
                'name' => 'pass',
                'type' => 'plaintext',
            ),
            array(
                'name' => 'dbname',
                'type' => 'string',
            ),
            array(
                'name' => 'timeout',
                'type' => 'integer',
            ),
            array(
                'name' => 'prefix',
                'type' => 'string',
            ),
        );

        // ----- SQL Injection checks -----
        // Signature 1 - detects single-quote and double-dash
        const PCRE_SQL_QUOTES = '/(\%27)|(\')|(%2D%2D)|(\-\-)/i';

        // Signature 2 - detects typical SQL injection attack, such as 1'or some_boolean_expression
        const PCRE_SQL_TYPICAL = "/\w*(\%27)|'(\s|\+)*((\%6F)|o|(\%4F))((\%72)|r|(\%52))/i";

        //Signature 3 - detects use of union - good guarantee of an attack
        const PCRE_SQL_UNION = "/((\%27)|')(\s|\+)*union/i";

        //Signature 4 - detects calling of an MS SQL stored or extended procedures
        const PCRE_SQL_STORED = '/exec(\s|\+)+(s|x)p\w+/i';

        /**
         * constructor
         *
         * @access public
         * @param  array  $options
         * @return void
         **/
        public function __construct( $options = array() ) {
            $this->__initialize($options);
            // ... create PDO object
            if ( $this->pass == '' ) {
                parent::__construct( $this->dsn, $this->user, $this->getDriverOptions() );
            }
            else {
                parent::__construct( $this->dsn, $this->user, $this->pass, $this->getDriverOptions() );
            }
        }   // end function __construct()

        /**
         * Create valid DSN and store it for later use
         *
         * @access public
         * @return void
         **/
        public function getDSN() {
            if ( empty( $this->dsn ) ) {
                $this->dsn = $this->driver.':host='.$this->host.';dbname='.$this->dbname;
                if ( isset( $this->port ) ) {
                    $this->dsn .= ';port='.$this->port;
                }
            }
            return $this->dsn;
        }   // end function getDSN()

        /**
         * driver classes may return an array of options passed to PDO
         *
         * by default, nothing is returned
         *
         * @access public
         * @return array
         **/
        public function getDriverOptions() {
        }   // end function getDriverOptions()

/*******************************************************************************
 * SQL BUILDER
 ******************************************************************************/

        /**
         * perform a search
         *
         * Usage example:
         *
         * $data = $dbh->search(array(
         *    'tables' => 'myTable',
         *    'fields' => array( 'id', 'content' ),
         *    'where'  => 'id == ? && content ne ?',
         *    'params' => array( '5', NULL )
         * ));
         *
         * Use isError() and getError() for error handling!
         *
         * @access public
         * @param  array   $options
         * @return mixed   array of result or false
         **/
        public function search ( $options )
        {
            $this->setError( NULL ); // reset error stack
            $this->statement = NULL; // reset statement

            if ( ! isset( $options['tables'] ) )
            {
                $this->setError('no tables!','fatal');
                return NULL;
            }

            $tables = $this->map_tables( $options['tables'], $options );

            $fields = isset( $options['fields'] )
                    ? $options['fields']
                    : '*';

            $where  = isset( $options['where'] )
                    ? $this->parse_where( $options['where'] )
                    : NULL;

            $order  = isset( $options['order_by'] )
                    ? $this->order_by( $options['order_by'] )
                    : NULL;

            $limit  = isset( $options['limit'] )
                    ? $this->limit( $options['limit'] )
                    : NULL;

            $params = isset( $options['params'] ) && is_array( $options['params'] )
                    ? $this->params( $options['params'] )
                    : NULL;

            $group  = isset( $options['group_by'] )
                    ? $this->group_by($options['group_by'])
                    : NULL;

    		// any errors so far?
    		if ( $this->isError() ) {
    		    // let the caller handle the error, just return false here
    		    $this->setError('unable to prepare the statement!','fatal');
                return false;
    		}

            // create the statement
            $this->statement
                = "SELECT "
                . (
                      is_array( $fields )
                    ? implode( ', ', $fields )
                    : $fields
                  )
                . " FROM $tables $where $group $order $limit";

            self::log('executing statement (interpolated for debugging)',7);
            self::log(self::interpolateQuery($this->statement,$params),7);

            // create statement handle
            $stmt   = $this->prepare( $this->statement );

            if ( ! is_object( $stmt ) )
            {
                $error_info = '['.implode( "] [", $this->errorInfo() ).']';
                $this->setError( 'prepare() ERROR: '.$error_info, 'fatal'  );
                return false;
            }

            if ( $stmt->execute( $params ) )
            {
                self::log( 'returning ['.$stmt->rowCount().'] results', 7 );
                return $stmt->fetchAll( \PDO::FETCH_ASSOC );
            }
            else
            {
                if ( $stmt->errorInfo() )
                {
                    $error = '['.implode( "] [", $stmt->errorInfo() ).']';
                }
                $this->setError( $error, 'fatal' );
                return false;
            }
            
        }   // end function search()



        public function insert ( $options ) {}
        public function update ( $options ) {}
        public function delete ( $options ) {}
        public function replace ( $options ) {}
        public function truncate ( $options ) {}

/*******************************************************************************
 * ERROR HANDLING
 ******************************************************************************/

        /**
         *
         *
         * @access public
         * @return boolean
         **/
        public function isError() {
            return isset( $this->lasterror );
        }   // end function isError()

        /**
         * Accessor to last error
         *
         * @access public
         * @param  boolean $fullstack - return the full error stack; default false
         * @return mixed   array if $fullstack is set, string otherwise
         **/
        public function getError( $fullstack = false ) {
            if ( $fullstack )
                return $this->errors;
            return $this->lasterror;
        }   // end function getError()

/*******************************************************************************
 * METHODS THAT ARE VERY SIMILAR IN ALL DRIVERS (STILL OVERLOADABLE)
 ******************************************************************************/

        /**
         * parse where conditions
         *
         * @access protected
         * @param  mixed     $where - array or scalar
         * @return mixed     parsed WHERE statement or NULL
         *
         **/
        public function parse_where( $where ) {
            self::log( var_export($where,1), 7 );
            if ( is_array( $where ) )
                $where = implode( ' AND ', $where );
            // replace conjunctions
            $string = $this->replaceConj( $where );
            // replace operators
            $string = $this->replaceOps( $string );
            if ( ! empty( $string ) ) {
                self::log( $string, 7 );
                return ' WHERE '.$string;
            }
            return NULL;
        }   // end function parse_where()

        /**
         * Replace operators in string
         *
         * @access protected
         * @param  string    $string - string to convert
         * @return string
         *
         **/
        protected function replaceOps( $string ) {
            $reg_exp = implode( '|', array_keys( $this->operators ) );
            reset( $this->operators );
            self::log(sprintf('replacing (%s) from: [%s]', $reg_exp, $string), 7);
            return preg_replace( "/(\s{1,})($reg_exp)(\s{1,})/eisx", '" ".$this->operators["\\2"]." "', $string );
        }   // end function replaceOps()

        /**
         * Replace conjunctions in string
         *
         * @access protected
         * @param  string    $string - string to convert
         * @return string
         *
         **/
        protected function replaceConj( $string )
        {
             $reg_exp = implode( '|', array_keys( $this->conjunctions ) );
             self::log(sprintf('replacing (%s) from string [%s]', $reg_exp, $string), 7);
             return preg_replace(
                          "/(\s{1,})($reg_exp)(\s{1,})/eisx",
                          '"\\1".$this->conjunctions["\\2"]."\\3"',
                          $string
                      );
        }   // end function replaceConj()

        /**
         * put error on error stack and set $lasterror
         *
         * @access private
         * @param  string  $error
         * @param  string  $level
         * @return void
         **/
        protected function setError( $error, $level = 'error' )
        {
            self::log(sprintf('setError(%s)',$error),7);
            $this->lasterror = $error;
            // push onto error stack
            if ( $error != NULL )
            	$this->errors[]  = $error;
        }   // end function setError()

        /**
         * accessor to Analog (if installed)
         *
         * Note: Log messages are ignored if no Analog is available!
         *
         * @access private
         * @param  string   $message
         * @param  integer  $level
         * @return
         **/
        protected static function log($message, $level = 3)
        {
            if($level<>self::$loglevel) return;
            if( !self::$analog && !self::$analog == -1)
            {
                if(file_exists(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php'))
                {
                    include_once(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php');
                    wblib_init_3rdparty(dirname(__FILE__).'/debug/',get_called_class(),self::$loglevel);
                    self::$analog = true;
                }
                else
                {
                    self::$analog = -1;
                }
            }
            if ( self::$analog )
                \Analog::log($message,$level);
        }   // end function log()

        /**
         * checks params for possible SQL injection code; uses setError() to log
         * positive matches
         *
         * @access protected
         * @param  array     $params - params to check
         * @return mixed     array of validated params or false
         **/
        protected function params( $params )
        {
            foreach ( $params as $i => $param )
            {
    			if ( ! $this->detectSQLInjection( $this->quote($param) ) ) {
    				// no escaping here; we're using PDO, remember?
    			    $params[$i] = $param;
    			}
    			else {
    				$this->setError('POSSIBLE SQL INJECTION DETECTED!', 'fatal');
    				return NULL;
    			}
            }
            self::log('PARAMS: '.var_export($params,1), 7);
            return $params;
        }   // end function params()

	    /**
	     * This method checks for typical SQL injection code
	     *
	     * @access public
         * @param  mixed $values - array of values or single value (scalar)
         * @return boolean       - returns false if no intrusion code was found
	     **/
		public function detectSQLInjection( $values )
        {
			if ( empty( $values ) )
		        return false;
			if ( is_scalar( $values ) )
			    $values = array( $values );
			foreach( $values as $value )
            {
				// check for SQL injection
				foreach(
					array( 'PCRE_SQL_TYPICAL', 'PCRE_SQL_UNION', 'PCRE_SQL_STORED' ) //'PCRE_SQL_QUOTES',
					as $constant
				) {
					if ( preg_match( constant( 'self::'.$constant ), $value ) )
                    {
		                self::log( sprintf( 'SECURITY ISSUE: suspect SQL injection -> (%s) -> [%s]',$constant,$value), 0 );
                        $this->setError('possible SQL injection!','fatal');
		                return true;
		            }
				}
			}
			// all checks passed
			return false;
		}   // end function detectSQLInjection()

        /**
         * Replaces any parameter placeholders in a query with the value of that
         * parameter. Useful for debugging. Assumes anonymous parameters from
         * $params are in the same order as specified in $query
         *
         * Source: http://stackoverflow.com/questions/210564/pdo-prepared-statements
         *
         * @access public
         * @param  string $query  The sql query with parameter placeholders
         * @param  array  $params The array of substitution parameters
         * @return string The interpolated query
         */
        public static function interpolateQuery($query, $params) {

            if ( ! is_array($params) )
                return $query;

            $keys   = array();
            $values = $params;

            # build a regular expression for each parameter
            foreach ($params as $key => $value)
            {
                if (is_string($key))
                    $keys[] = '/:'.$key.'/';
                else
                    $keys[] = '/[?]/';

                if (is_array($value))
                    $values[$key] = implode(',', $value);

                if (is_null($value))
                    $values[$key] = 'NULL';
            }
            // Walk the array to see if we can add single-quotes to strings
            array_walk($values, create_function('&$v, $k', 'if (!is_numeric($v) && $v!="NULL") $v = "\'".$v."\'";'));
            $query = preg_replace($keys, $values, $query, 1, $count);
            return $query;
        }   // end function interpolateQuery()

        /**
         * initialize database class:
         *
         * - load driver defaults
         * - overwrite defaults with given options (if any)
         * - get valid DSN for DB connection
         *
         **/
        private final function __initialize($options) {
            foreach ( $this->_options as $opt ) {
                $key  = $opt['name'];
                $type = $opt['type'];
                if ( isset( $options[$key] ) && ! empty( $options[$key] ) ) {
                    $this->$key = $options[$key];
                }
            }
            $this->getDSN();
            return true;
        }   // end function __initialize()

    }
}

if ( ! class_exists( 'MySQL', false ) )
{
    class MySQL extends wbSQL_Driver implements wbSQL_DriverInterface
    {
        protected $port   = 3306;
        protected $driver = 'mysql';
        /**
         * log level
         **/
        public static $loglevel = 7;

        /**
         *
         * @access protected
         * @return
         **/
        public function group_by($group_by) {
            return ' GROUP BY '.$group_by;
        }   // end function group_by()
        

        /**
         *
         * @access protected
         * @return
         **/
        public function limit($limit) {
            return ' LIMIT '.$limit;
        }   // end function limit()

        /**
         * adds prefix to table names, handles joins
         *
         * @access protected
         * @param  mixed     $tables    - array of tables or single table name
         * @param  array     $options
         * @return string
         **/
        public function map_tables( $tables, $options = array() )
        {
            if ( is_array( $tables ) )
            {
                // join(s) defined?
                if ( isset( $options['join'] ) ) {
                    return $this->parse_join( $tables, $options );
                }
                else
                {
                    foreach ( $tables as $i => $t_name )
                    {
                        if (
                             ! empty( $this->prefix )
                             &&
                             substr_compare( $t_name, $this->prefix, 0, strlen($this->prefix), true )
                        ) {
                            $t_name = $this->prefix . $t_name;
                        }
                        $tables[$i] = $t_name . ( isset( $options['__is_delete'] ) ? '' : ' as t' . ($i+1) );
                    }
                    return implode( ', ', $tables );
                }
            }
            else
            {
                return $this->prefix . $tables . ( ( isset( $options['__is_insert'] ) || isset( $options['__is_delete'] ) ) ? NULL : ' as t1' );
            }
        }   // end function map_tables()

        /**
         * returns correct order by syntax
         *
         * @access protected
         * @return string
         **/
        public function order_by( $order_by )
        {
            return ' ORDER BY '.$order_by;
        }   // end function order_by()

        /**
         * parse join statement
         *
         *
         *
         **/
        public function parse_join( $tables, $options = array() )
        {

            $jointype = ' LEFT JOIN ';
            $join     = $options['join'];

            self::log('tables: '.var_export($tables,1),7);
            self::log('options: '.var_export($options,1),7);

            if ( ! is_array( $tables ) )
                $tables = array( $tables );

            if ( count( $tables ) > 2 && ! is_array( $join ) )
            {
                $this->setError( '$tables count > 2 and $join is not an array', 'fatal' );
                return NULL;
            }

            if ( ! is_array( $join ) )
                $join = array( $join );

            if ( count( $join ) <> ( count( $tables ) - 1 ) )
            {
                $this->setError( 'table count <> join count', 'fatal' );
                return;
            }

            $join_string = $this->prefix . $tables[0] . ' AS t1 ';

            foreach ( $join as $index => $item )
            {
                $join_string .= ( isset($options['jointype']) ? $options['jointype'] : $jointype )
                             .  $this->prefix.$tables[ $index + 1 ]
                             .  ' AS t'.($index+2).' ON '
                             .  $item;
            }

            self::log(sprintf('join string before replacing ops/conj: [%s]',$join_string),7);

            $join = $this->replaceConj( $this->replaceOps( $join_string ) );

            self::log(sprintf('returning parsed join: [%s]',$join),7);

            return $join;

        }   // end function parse_join()

        /**
         * show tables
         *
         * @access public
         * @return array
         **/
    	public function showTables()
        {
    	    $data   = $this->query('SHOW TABLES');
    	    $tables = array();
    		while( $result = $data->fetch() )
         		$tables[] = $result[0];
    		return $tables;
    	}   // end function showTables()

        /**
         * Get the max value of a given field
         *
         * @access public
         * @param  string   $fieldname - field to check
         * @param  array    $options   - additional options (where-Statement, for example)
         * @return mixed
         *
         **/
        public function max( $fieldname, $options = array() ) {
            $data = $this->search(
                array_merge(
                    $options,
                    array(
                        'limit'  => 1,
                        'fields' => "max($fieldname) as maximum",
                    )
                )
            );
            if ( isset( $data ) && is_array( $data ) && count( $data ) > 0 ) {
                return $data[0]['maximum'];
            }
            return NULL;
        }   // end function max()

        /**
         * Get the min value of a given field
         *
         * @access public
         * @param  string   $fieldname - field to check
         * @param  array    $options   - additional options (where-Statement, for example)
         * @return mixed
         *
         **/
        public function min( $fieldname, $options = array() ) {
            $data = $this->search(
                array_merge(
                    $options,
                    array(
                        'limit'  => 1,
                        'fields' => "min($fieldname) as minimum",
                    )
                )
            );
            if ( isset( $data ) && is_array( $data ) && count( $data ) > 0 ) {
                return $data[0]['minimum'];
            }
            return NULL;
        }   // end function min()

    }
}