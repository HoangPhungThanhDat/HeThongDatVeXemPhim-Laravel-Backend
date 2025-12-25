<?php

namespace App\Repositories;

use App\Models\Contact;

class ContactRepository
{
    /**
     * Lấy tất cả Contact
     */
    public function getAll()
    {
        return Contact::all();
    }

    /**
     * Tìm Contact theo ID
     */
    public function getById($id)
    {
        return Contact::findOrFail($id);
    }

    /**
     * Tạo mới Contact
     */
    public function create(array $data)
    {
        return Contact::create($data);
    }

    /**
     * Cập nhật contact
     */
    public function update($id, array $data)
    {
        $contact = Contact::findOrFail($id);
        $contact->update($data);
        return $contact;
    }

    /**
     * Xóa contact
     */
    public function delete($id)
    {
        $contact = Contact::findOrFail($id);
        return $contact->delete();
    }
}
