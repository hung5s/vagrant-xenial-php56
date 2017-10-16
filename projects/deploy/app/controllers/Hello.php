<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 12/07/2017
 * Time: 13:54
 */

namespace app\controllers;


class Hello extends \core\Controller
{
    public function actionIndex()
    {
        $this->response->send("Hello, world");
    }
}