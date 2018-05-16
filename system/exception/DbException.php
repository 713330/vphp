<?php
namespace reading\exception;

use reading\Exception;

/**
 * Database相关异常处理类
 *
 * @see Exception
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-10-12
 */
class DbException extends Exception
{
    /**
     * DbException constructor.
     * @param string    $message
     * @param array     $config
     * @param string    $sql
     * @param int       $code
     */
    public function __construct($message, array $config, $sql, $code = 10500)
    {
        $this->message = $message;
        $this->code    = $code;

        $this->setData('Database Status', [
            'Error Code'    => $code,
            'Error Message' => $message,
            'Error SQL'     => $sql,
        ]);

        $this->setData('Database Config', $config);
    }

}
