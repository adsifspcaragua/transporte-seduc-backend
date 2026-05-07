<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\User\UserService;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index()
    {
        return $this->userService->index();
    }

    public function store(StoreUserRequest $request)
    {
        return $this->userService->store($request->validated());
    }

    public function show(string $id)
    {
        return $this->userService->show($id);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        return $this->userService->update($request->validated(), $user);
    }

    public function destroy(string $id)
    {
        return $this->userService->destroy($id);
    }
}
