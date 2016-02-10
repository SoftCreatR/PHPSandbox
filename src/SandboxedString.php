<?php
    /** Sandboxed string class declaration
     * @package PHPSandbox
     */
    namespace PHPSandbox;
    /**
     * Sandboxed string class for PHP Sandboxes.
     *
     * This class wraps sandboxed strings to intercept and check callable invocations
     *
     * @namespace PHPSandbox
     *
     * @author  Elijah Horton <elijah@corveda.com>
     * @version 2.0
     */
    class SandboxedString implements \ArrayAccess, \IteratorAggregate {
        /**
         * @var string
         */
        private $value;
        /**
         * @var PHPSandbox
         */
        private $sandbox;
        /** Constructs the SandboxedString
         * @param   string      $value          Original string value
         * @param   PHPSandbox  $sandbox        The current sandbox instance to test against
         */
        public function __construct($value, PHPSandbox $sandbox){
            $this->value = $value;
            $this->sandbox = $sandbox;
        }
        /** Returns the original string value
         * @return string
         */
        public function __toString(){
            return strval($this->value);
        }
        /** Checks the string value against the sandbox function whitelists and blacklists for callback violations
         * @return mixed|null
         */
        public function __invoke(){
            if($this->sandbox->checkFunc($this->value)){
                $name = strtolower($this->value);
                if((in_array($name, PHPSandbox::$defined_funcs) && $this->sandbox->overwrite_defined_funcs)
                    || (in_array($name, PHPSandbox::$sandboxed_string_funcs) && $this->sandbox->overwrite_sandboxed_string_funcs)
                    || (in_array($name, PHPSandbox::$arg_funcs) && $this->sandbox->overwrite_func_get_args)){
                    return call_user_func_array([$this->sandbox, '_' . $this->value], func_get_args());
                }
                return call_user_func_array($name, func_get_args());
            }
            return '';
        }
        /** Set string value at specified offset
         * @param   mixed       $offset            Offset to set value
         * @param   mixed       $value             Value to set
         */
        public function offsetSet($offset, $value){
            if($offset === null){
                $this->value .= $value;
            } else {
                $this->value[$offset] = $value;
            }
        }
        /** Get string value at specified offset
         * @param   mixed       $offset            Offset to get value
         *
         * @return  string      Value to return
         */
        public function offsetGet($offset){
            return $this->value[$offset];
        }
        /** Check if specified offset exists in string value
         * @param   mixed       $offset            Offset to check
         *
         * @return  bool        Return true if offset exists, false otherwise
         */
        public function offsetExists($offset){
            return isset($this->value[$offset]);
        }
        /** Unset string value at specified offset
         * @param   mixed       $offset            Offset to unset
         */
        public function offsetUnset($offset){
            unset($this->value[$offset]);
        }
        /** Return iterator for string value
         * @return  \ArrayIterator      Array iterator to return
         */
        public function getIterator(){
            return new \ArrayIterator(str_split(strval($this->value)));
        }
    }