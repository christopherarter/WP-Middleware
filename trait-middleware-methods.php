<?php 

/**
 * This trait adds the helper
 * methods to designate the
 * method to protect against.
 */
trait MiddlewareMethods
{
    /**
     * GET method setter helper.
     *
     * @param array ...$input
     * @return void
     */
    public function get(...$input) : void
    {
        $this->methodToProtect = 'GET';
        $this->guard(...$input);
    }

    /**
     * GET method setter helper.
     *
     * @param array ...$input
     * @return void
     */
    public function post(...$input) : void
    {
        $this->methodToProtect = 'POST';
        $this->guard(...$input);
    }

    /**
     * GET method setter helper.
     *
     * @param array ...$input
     * @return void
     */
    public function put(...$input) : void
    {
        $this->methodToProtect = 'PUT';
        $this->guard(...$input);
    }

    /**
     * GET method setter helper.
     *
     * @param array ...$input
     * @return void
     */
    public function patch(...$input) : void
    {
        $this->methodToProtect = 'PUT';
        $this->guard(...$input);
    }

    /**
     * GET method setter helper.
     *
     * @param array ...$input
     * @return void
     */
    public function delete(...$input) : void
    {
        $this->methodToProtect = 'DELETE';
        $this->guard(...$input);
    }

    /**
     * GET method setter helper.
     *
     * @param array ...$input
     * @return void
     */
    public function head(...$input) : void
    {
        $this->methodToProtect = 'DELETE';
        $this->guard(...$input);
    }

    public function methods(...$input) : void
    {
        $this->methodToProtect = $input[0];
        unset($input[0]);
        $this->guard(...$input);
    }
}