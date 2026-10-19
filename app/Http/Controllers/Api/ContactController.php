<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\StoreContactRequest;
use App\Http\Resources\ContactResource;
use App\Http\Controllers\Controller;
use App\Services\ContactService;

/**
 * @group Contacts
 * 
 * APIs for managing contact messages and inquiries from users.
 * These endpoints allow users to submit contact forms and admins to manage them.
 */
class ContactController extends Controller
{
    protected $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    /**
     * Get all contacts
     * 
     * Get a list of all contact messages. (Admin only)
     * 
     * @authenticated
     * 
     * @response 200 [
     *   {
     *     "ContactId": 1,
     *     "FullName": "Nguyễn Văn A",
     *     "Email": "user@example.com",
     *     "Phone": "0987654321",
     *     "Subject": "Hỏi về vé xem phim",
     *     "Message": "Tôi muốn hỏi về cách đặt vé online...",
     *     "Status": "Pending",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   }
     * ]
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *   "message": "Forbidden - Admin only"
     * }
     */
    public function index()
    {
        $contacts = $this->contactService->getAll();
        return ContactResource::collection($contacts);
    }

    /**
     * Submit a contact message
     * 
     * Create a new contact message. This endpoint is public and does not require authentication.
     * 
     * @unauthenticated
     * 
     * @bodyParam FullName string required The full name of the sender. Example: Nguyễn Văn A
     * @bodyParam Email string required The email address of the sender. Example: user@example.com
     * @bodyParam Phone string The phone number of the sender. Example: 0987654321
     * @bodyParam Subject string required The subject of the message. Example: Hỏi về vé xem phim
     * @bodyParam Message string required The content of the message. Example: Tôi muốn hỏi về cách đặt vé online...
     * 
     * @response 201 {
     *   "ContactId": 1,
     *   "FullName": "Nguyễn Văn A",
     *   "Email": "user@example.com",
     *   "Phone": "0987654321",
     *   "Subject": "Hỏi về vé xem phim",
     *   "Message": "Tôi muốn hỏi về cách đặt vé online...",
     *   "Status": "Pending",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "FullName": ["The full name field is required."],
     *     "Email": ["The email field is required."],
     *     "Subject": ["The subject field is required."],
     *     "Message": ["The message field is required."]
     *   }
     * }
     * @response 500 {
     *   "error": "Error message...",
     *   "file": "ContactController.php",
     *   "line": 42
     * }
     */
    public function store(StoreContactRequest $request)
    {
        try {
            $contact = $this->contactService->create($request->validated());
            return new ContactResource($contact);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Get a specific contact
     * 
     * Get detailed information about a specific contact message. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam ContactId int required The ID of the contact message. Example: 1
     * 
     * @response 200 {
     *   "ContactId": 1,
     *   "FullName": "Nguyễn Văn A",
     *   "Email": "user@example.com",
     *   "Phone": "0987654321",
     *   "Subject": "Hỏi về vé xem phim",
     *   "Message": "Tôi muốn hỏi về cách đặt vé online...",
     *   "Status": "Pending",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Contact not found"
     * }
     */
    public function show($ContactId)
    {
        $contact = $this->contactService->getById($ContactId);
        return new ContactResource($contact);
    }

    /**
     * Update a contact message
     * 
     * Update the status or details of a contact message. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam ContactId int required The ID of the contact message. Example: 1
     * @bodyParam FullName string The full name. Example: Nguyễn Văn A
     * @bodyParam Email string The email address. Example: user@example.com
     * @bodyParam Phone string The phone number. Example: 0987654321
     * @bodyParam Subject string The subject. Example: Hỏi về vé xem phim
     * @bodyParam Message string The message content. Example: Tôi muốn hỏi về cách đặt vé online...
     * @bodyParam Status string The status. Enum: Pending, Resolved, Rejected. Example: Resolved
     * 
     * @response 200 {
     *   "ContactId": 1,
     *   "FullName": "Nguyễn Văn A",
     *   "Email": "user@example.com",
     *   "Phone": "0987654321",
     *   "Subject": "Hỏi về vé xem phim",
     *   "Message": "Tôi muốn hỏi về cách đặt vé online...",
     *   "Status": "Resolved",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Contact not found"
     * }
     */
    public function update(StoreContactRequest $request, $ContactId)
    {
        $contact = $this->contactService->update($ContactId, $request->validated());
        return new ContactResource($contact);
    }

    /**
     * Delete a contact message
     * 
     * Delete a contact message from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam ContactId int required The ID of the contact message. Example: 1
     * 
     * @response 200 {
     *   "message": "Contact deleted successfully"
     * }
     * @response 404 {
     *   "message": "Contact not found"
     * }
     */
    public function destroy($ContactId)
    {
        $this->contactService->delete($ContactId);
        return response()->json(['message' => 'Contact deleted successfully']);
    }
}