<?php


namespace fize\framework;

use fize\session\Session as FizeSession;


class Session extends FizeSession
{

    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }
}