<?php
namespace reading\response;

use reading\Request;
use reading\Response;
use reading\Session;
use reading\Url;

class Redirect extends Response
{

    protected $options = [];

    // URL参数
    protected $params = [];

    public function __construct($data = '', $code = 302, array $header = [], array $options = [])
    {
        parent::__construct($data, $code, $header, $options);
        $this->cacheControl('no-cache,must-revalidate');
    }

    /**
     * 处理数据
     * @access protected
     * @param mixed $data 要处理的数据
     * @return mixed
     */
    protected function output($data)
    {
        $this->header['Location'] = $this->getTargetUrl();
        return;
    }

    /**
     * 获取跳转地址
     * @return string
     */
    public function getTargetUrl()
    {
        return (strpos($this->data, '://') || 0 === strpos($this->data, '/')) ? $this->data : '/';
    }

    public function params($params = [])
    {
        $this->params = $params;
        return $this;
    }

    /**
     * 记住当前url后跳转
     * @return $this
     */
    public function remember()
    {
        Session::set('redirect_url', Request::instance()->url());
        return $this;
    }

    /**
     * 跳转到上次记住的url
     * @return $this
     */
    public function restore()
    {
        if (Session::has('redirect_url')) {
            $this->data = Session::get('redirect_url');
            Session::delete('redirect_url');
        }
        return $this;
    }
}
