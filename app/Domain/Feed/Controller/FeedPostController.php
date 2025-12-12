<?php

namespace App\Domain\Feed\Controller;

use App\Domain\Feed\Models\FeedCategory;
use App\Domain\Feed\Models\FeedPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\Feed\Requests\FeedPostEditRequest;
use App\Domain\Feed\Resources\FeedPostResource;
use App\Domain\Member\Models\Member;

class FeedPostController
{

}
