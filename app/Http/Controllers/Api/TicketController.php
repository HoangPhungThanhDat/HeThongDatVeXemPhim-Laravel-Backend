<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use App\Services\TicketService;

class TicketController extends Controller
{
    protected $service;

    public function __construct(TicketService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }


    //lấy tất cả 
    public function index()
    {
        return TicketResource::collection($this->service->getAll());
    }
    //thêm

    public function store(StoreTicketRequest $request)
    {
        $ticket = $this->service->create($request->validated());

        return new TicketResource($ticket);
    }
   //hiện 1
    public function show($TicketId)
    {
        $ticket = $this->service->find($TicketId);
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        return new TicketResource($ticket);
    }
  //cập nhật
    public function update(StoreTicketRequest $request, $TicketId)
    {
        $ticket = $this->service->update($TicketId, $request->validated());
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        return new TicketResource($ticket);
    }
   //xoá
    public function destroy($TicketId)
    {
        $deleted = $this->service->delete($TicketId);
        if (!$deleted) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        return response()->json(['message' => 'Ticket deleted successfully']);
    }
}
