<?php

namespace App\Services;

use App\Repositories\AuditlogRepository;

class AuditlogService
{
    protected $auditlogRepository;

    public function __construct(AuditlogRepository $auditlogRepository)
    {
        $this->auditlogRepository = $auditlogRepository;
    }

    public function getAll()
    {
        return $this->auditlogRepository->getAll();
    }

    public function getById($id)
    {
        return $this->auditlogRepository->getById($id);
    }

    public function create(array $data)
    {
        return $this->auditlogRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->auditlogRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->auditlogRepository->delete($id);
    }
}
