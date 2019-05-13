<?php

abstract class AbstractMiddleware
{
    use MiddlewareMethods;

    /**
     * Internal middleware
     * container.
     *
     * @var array
     */
    private $middleware = [];

    /**
     * Inbound request object.
     *
     * @var WP_REST_Request
     */
    private $request;

    /**
     * Request method for
     * inbound request. This
     * property is used to determine
     * correct method for route.
     *
     * @var string
     */
    private $methodToProtect;

    /**
     * Array of MiddlewareRejection
     * objects.
     *
     * @var array
     */
    private $rejections = [];


    /**
     * Hook to use to check
     * against request property.
     *
     * @var string
     */
    private $outboundHook = 'rest_post_dispatch';

    private $inboundHook = 'rest_pre_dispatch';

    public function __construct()
    {
        add_action( $this->outboundHook, [$this, 'check'] );
        add_filter( $this->inboundHook, [$this, 'setRequest'], 0, 3);
    }

    /**
     * Grab the inbound WP_REST_Request
     * and save it to a private property
     * for the `checkRoute` method to inject
     * into callbacks.  
     *
     * @param array ...$input
     * @return void
     */
    public function setRequest(...$input) : void
    {
        $this->request = $input[2];
    }

    /**
     * Compare a string of $route,
     * against an array of functions
     * to call.
     *
     * @param string $route
     * @param array $functions
     * @return void
     */
    public function guard(string $route, array $functions): Middleware
    {
        $this->middleware[$route] = [
            'callbacks' => $functions
        ];
        return $this;
    }

    /**
     * Checks the callbacks registered
     * to a given route. If the response
     * is a MiddlewareRejection, then it
     * is saved into the $rejections property
     * to be counted in the `check` method.
     *
     * @param string $route
     * @param $method
     * @param \WP_HTTP_Response $response
     * @return void
     */
    public function checkRoute(string $route, $method, \WP_HTTP_Response $response) : void
    {
        $requestMethod = $this->request->get_method();
        
        if ( $requestMethod == $method || ( is_array($method) && in_array( $requestMethod, $method ) ) ) {
            foreach ($this->middleware[$route] as $callbackGroup) {
                foreach ($callbackGroup as $callback) {
                    if ( function_exists($callback)) {
                        $result = call_user_func($callback, $this->request, $response);
                        if ( $result instanceof \MiddlewareRejection ) {
                            $this->rejections[] = $result;
                        }
                    }
                }
            }
        }
    }

    /**
     * Rejection response factory. Takes an inbound
     * WP_HTTP_Response object and sets the properties
     * as a rejection response.
     *
     * @param \WP_HTTP_Response $response
     * @param \MiddlewareRejection $MiddlewareRejection
     * @return void
     */
    public function rejectWpResponse(\WP_HTTP_Response $response, \MiddlewareRejection $MiddlewareRejection): WP_HTTP_Response
    {
        $response->set_status((int) $MiddlewareRejection->status );
        $response->set_data($MiddlewareRejection->message);
        return $response;
    }

    /**
     * Check inbound request
     * against registered middleware.
     *
     * @param \WP_HTTP_Response $input
     * @return \WP_HTTP_Response
     */
    public function check( \WP_HTTP_Response $input): \WP_HTTP_Response
    {

        /**
         * If no HTTP Method setting method
         * was used, just default to the inbound
         * request. This allows `guard()` to protect
         * all methods.
         */
        if (!$this->methodToProtect) {
            $this->methodToProtect = $this->request->get_method();
        }


        /**
         * Check route against all
         * callbacks and populate rejections
         * property.
         */
        $this->checkRoute( $input->get_matched_route(), $this->methodToProtect, $input);

        // check rejections property.
        if ( count($this->rejections) > 0) {

            // send response to response factory.
            return $this->rejectWpResponse($input, $this->rejections[0]);
        }
        return $input;
    }
}

