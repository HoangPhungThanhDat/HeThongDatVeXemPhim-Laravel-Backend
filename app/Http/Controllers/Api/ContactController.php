<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\StoreContactRequest;
use App\Http\Resources\ContactResource;
use App\Http\Controllers\Controller;
use App\Services\ContactService;



class ContactController extends Controller
{
    protected $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    public function index()
    {
        $contacts = $this->contactService->getAll();
        return ContactResource::collection($contacts);
    }

    public function store(StoreContactRequest $request)
    {
        $contact = $this->contactService->create($request->validated());
        return new ContactResource($contact);
    }

    public function show($ContactId)
    {
        $contact = $this->contactService->getById($ContactId);
        return new ContactResource($contact);
    }

    public function update(StoreContactRequest $request, $ContactId)
    {
        $contact = $this->contactService->update($ContactId, $request->validated());
        return new ContactResource($contact);
    }

    public function destroy($ContactId)
    {
        $this->contactService->delete($ContactId);
        return response()->json(['message' => 'contact deleted successfully']);
    }
}