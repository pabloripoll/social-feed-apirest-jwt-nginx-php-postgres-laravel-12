<?php

return [
    App\Providers\AppServiceProvider::class,

    /*
    * Domain Service Providers...
    */
    App\Modules\Geo\GeoServiceProvider::class,
    App\Modules\User\UserServiceProvider::class,
    App\Modules\Feed\FeedServiceProvider::class,
    App\Modules\Admin\AdminServiceProvider::class,
    App\Modules\Member\MemberServiceProvider::class,
];
