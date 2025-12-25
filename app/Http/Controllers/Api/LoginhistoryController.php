<?php

namespace App\Http\Controllers\Api;

use App\Models\Loginhistory;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLoginhistoryRequest;
use App\Http\Resources\LoginhistoryResource;
use App\Http\Controllers\Controller;
use App\Services\LoginhistoryService;


class LoginhistoryController extends Controller
{
    protected $loginhistoryService;

    public function __construct(LoginhistoryService $loginhistoryService)
    {
        $this->loginhistoryService = $loginhistoryService;
    }

    public function index()
    {
        $loginhistorys = $this->loginhistoryService->getAll();
        return LoginhistoryResource::collection($loginhistorys);
    }

    public function store(StoreLoginhistoryRequest $request)
    {
        $loginhistory = $this->loginhistoryService->create($request->validated());
        return new LoginhistoryResource($loginhistory);
    }

    public function show($LoginId)
    {
        $loginhistory = $this->loginhistoryService->getById($LoginId);
        return new LoginhistoryResource($loginhistory);
    }

    public function update(StoreLoginhistoryRequest $request, $LoginId)
    {
        $loginhistory = $this->loginhistoryService->update($LoginId, $request->validated());
        return new LoginhistoryResource($loginhistory);
    }

    public function destroy($LoginId)
    {
        $this->loginhistoryService->delete($LoginId);
        return response()->json(['message' => 'loginhistory deleted successfully']);
    }
}
