<?php
return [
    'name' => 'Indition Package Manager',
    'services' => [
        'Response' => [
            '\core\services\responses\CliResponse'
        ],
    ],
    'svnUser' => 'hung5s',
    'svnPassword' => 'h4dd8a@g',
    'webSvn' => 'svn://10.2.30.165/home/svn/_code/indition_v4.2/web/trunk', // use when create new project
    'defaultModules' => array(
        'vcs' => 'svn://10.2.30.165/home/svn/_code/',
        'source' =>
            array(
                'root' =>
                    array(
                        'path' => 'indition_v4/core/trunk/',
                    ),
                'root/Api' =>
                    array(
                        'path' => 'indition_v4/modules/Api/module/trunk/',
                    ),
                'root/Cms' =>
                    array(
                        'path' => 'indition_v4/modules/Cms/module/trunk/',
                    ),
                'root/Gallery' =>
                    array(
                        'path' => 'indition_v4/modules/Gallery/module/trunk/',
                    ),
                'root/InditionBlog' =>
                    array(
                        'path' => 'indition_v4/modules/InditionBlog/module/trunk/',
                    ),
                'root/Messaging' =>
                    array(
                        'path' => 'indition_v4/modules/Messaging/module/trunk/',
                    ),
                'root/PressRoom' =>
                    array(
                        'path' => 'indition_v4/modules/PressRoom/module/trunk/',
                    ),
                'root/Xpress' =>
                    array(
                        'path' => 'indition_v4/modules/Xpress/module/trunk/',
                    ),
                'root/Xpress/Admin' =>
                    array(
                        'path' => 'indition_v4/modules/Admin/module/trunk/',
                    ),
                'root/Xpress/XUser' =>
                    array(
                        'path' => 'indition_v4/modules/XUser/module/trunk/',
                    ),
//                'site/Site' =>
//                    array(
//                        'path' => 'indition_v4_projects/serta2016/modules/Site/module/trunk/',
//                    ),
//                'site/config' =>
//                    array(
//                        'path' => 'indition_v4_projects/serta2016/config/trunk',
//                       
//                    ),
//                'site/themes/2016.serta.com' =>
//                    array(
//                        'path' => 'indition_v4_projects/serta2016/themes/2016.serta.com/trunk',
//                        
//                    ),
            ),
    )
];