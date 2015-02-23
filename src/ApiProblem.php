<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem;

/**
 * Object describing an API-Problem payload
 */
class ApiProblem
{
    /**
     * Description of the specific problem.
     *
     * @var string|\Exception
     */
    protected $errors = '';

    /**
     * Whether or not to include a stack trace and previous
     * exceptions when an exception is provided for the detail.
     *
     * @var bool
     */
    protected $detailIncludesStackTrace = false;

    /**
     * HTTP status for the error.
     *
     * @var int
     */
    protected $status;

    /**
     * Normalized property names for overloading
     *
     * @var array
     */
    protected $normalizedProperties = array(
        'status' => 'status',
        'errors' => 'errors',
    );

    /**
     * Status titles for common problems
     *
     * @var array
     */
    protected $problemStatusTitles = array(
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    );

    /**
     * Constructor
     *
     * Create an instance using the provided information. If nothing is
     * provided for the type field, the class default will be used;
     * if the status matches any known, the title field will be selected
     * from $problemStatusTitles as a result.
     *
     * @param int    $status
     * @param string $errors
     */
    public function __construct($status, $errors)
    {
        $this->status = $status;
        $this->errors = $errors;
    }

    /**
     * Retrieve properties
     *
     * @param  string $name
     * @return mixed
     * @throws Exception\InvalidArgumentException
     */
    public function __get($name)
    {
        $normalized = strtolower($name);
        if (in_array($normalized, array_keys($this->normalizedProperties))) {
            $prop = $this->normalizedProperties[$normalized];
            return $this->{$prop};
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Invalid property name "%s"',
            $name
        ));
    }

    /**
     * Cast to an array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'status' => $this->getStatus(),
            'errors' => $this->getErrors(),
        );
    }

    /**
     * Set the flag indicating whether an exception detail should include a
     * stack trace and previous exception information.
     *
     * @param  bool $flag
     * @return ApiProblem
     */
    public function setDetailIncludesStackTrace($flag)
    {
        $this->detailIncludesStackTrace = (bool) $flag;
        return $this;
    }

    /**
     * Retrieve the API-Problem errors
     *
     * If an exception was provided, creates the errors message from it;
     * otherwise, errors as provided is used.
     *
     * @return string
     */
    protected function getErrors()
    {
        if ($this->errors instanceof \Exception) {
            return $this->createErrorsFromException();
        }

        return $this->errors;
    }

    /**
     * Retrieve the API-Problem HTTP status code
     *
     * If an exception was provided, creates the status code from it;
     * otherwise, code as provided is used.
     *
     * @return string
     */
    protected function getStatus()
    {
        if ($this->errors instanceof \Exception) {
            $this->status = $this->createStatusFromException();
        }

        return $this->status;
    }

    /**
     * Create errors message from an exception.
     *
     * @return string
     */
    protected function createErrorsFromException()
    {
        $e = $this->errors;

        if (!$this->detailIncludesStackTrace) {
            return $e->getMessage();
        }

        $message = trim($e->getMessage());

        $previous = array();
        $e = $e->getPrevious();
        while ($e) {
            $previous[] = array(
                'code'    => (int) $e->getCode(),
                'message' => trim($e->getMessage()),
                'trace'   => $e->getTrace(),
            );
            $e = $e->getPrevious();
        }

        return $message;
    }

    /**
     * Create HTTP status from an exception.
     *
     * @return string
     */
    protected function createStatusFromException()
    {
        $e      = $this->errors;
        $status = $e->getCode();

        if (!empty($status)) {
            return $status;
        } else {
            return 500;
        }
    }
}
