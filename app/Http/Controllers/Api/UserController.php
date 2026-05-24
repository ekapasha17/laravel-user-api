<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\GetUsersRequest;
use App\Http\Resources\UserListResource;
use App\Http\Resources\UserResource;
use App\Queries\UserListQuery;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UserListQuery $userListQuery,
    ) {}

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function index(GetUsersRequest $request): JsonResponse
    {
        $users = $this->userListQuery->get($request->validated());

        return response()->json([
            'page' => $users->currentPage(),
            'users' => UserListResource::collection($users),
        ]);
    }
}
