<?php

return [
    App\Providers\AppServiceProvider::class,

    /*
    * Domain Service Providers...
    */
    App\Domain\Geo\GeoServiceProvider::class,
    App\Domain\User\UserServiceProvider::class,
    App\Domain\Feed\FeedServiceProvider::class,
    App\Domain\Admin\AdminServiceProvider::class,
    App\Domain\Member\MemberServiceProvider::class,
];
