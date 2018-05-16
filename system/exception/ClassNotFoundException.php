<?php
namespace reading\exception;

/**
 * Class: NotFoundException
 *
 * @see \RuntimeException
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-10-12
 */
class ClassNotFoundException extends \RuntimeException
{
    protected $class;
    public function __construct($message, $class = '')
    {
        $this->message = $message;
        $this->class   = $class;
    }

    /**
     * 获取类名
     * @access public
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }
}
