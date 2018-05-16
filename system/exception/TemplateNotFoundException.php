<?php
namespace reading\exception;

/**
 * Class: TemplateNotFoundException
 *
 * @see \RuntimeException
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-10-12
 */
class TemplateNotFoundException extends \RuntimeException
{
    protected $template;

    public function __construct($message, $template = '')
    {
        $this->message  = $message;
        $this->template = $template;
    }

    /**
     * 获取模板文件
     * @access public
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
