<?php

namespace App\Services;

use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\Auth;

class PaymentService
{
    protected $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function getAll()
    {
        return $this->paymentRepository->getAll();
    }

    public function getById($id)
    {
        return $this->paymentRepository->getById($id);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->paymentRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        return $this->paymentRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->paymentRepository->delete($id);
    }
}
