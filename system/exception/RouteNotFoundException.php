<?php
namespace reading\exception;

/**
 * Class: RouteNotFoundException
 *
 * @see HttpException
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-10-12
 */
class RouteNotFoundException extends HttpException
{

    public function __construct()
    {
        parent::__construct(404);
    }

}
