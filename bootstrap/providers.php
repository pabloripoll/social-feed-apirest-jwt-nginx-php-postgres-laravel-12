<?php

return [
    App\Providers\AppServiceProvider::class,

    /*
    * Domain Service Providers...
    */
    App\Domain\Post\PostServiceProvider::class,
    App\Domain\Admin\AdminServiceProvider::class,
    App\Domain\Member\MemberServiceProvider::class,
];
