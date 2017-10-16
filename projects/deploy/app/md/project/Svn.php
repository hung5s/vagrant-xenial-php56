<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 14/07/2017
 * Time: 10:33
 */

namespace app\md\project;


use core\services\Response;

class Svn extends \core\Middleware
{
    /**
     * @param \app\services\SourceControl $SourceControl
     * @param \app\services\Config $Config
     */
    public function handleCheckout($SourceControl = 'svc://app/services/SourceControl', 
                                   $Config = 'svc://app/services/Config'){
        if (!$Config->isValid)
            $Config->createDefault();
        
        $SourceControl->sync();
    }
}