<?php

namespace App\Repositories;

use App\Models\Contact;
use Illuminate\Support\Facades\Cache;

class ContactRepository
{
    const CACHE_TIME = 300; // Cache 5 phút (vì contact thay đổi liên tục)

    /**
     * Lấy tất cả Contact - ✅ ĐÃ CACHE
     */
    public function getAll()
    {
        return Cache::remember('contacts_all', self::CACHE_TIME, function () {
            return Contact::orderBy('CreatedAt', 'desc')->get();
        });
    }

    /**
     * Tìm Contact theo ID - ✅ ĐÃ CACHE
     */
    public function getById($id)
    {
        return Cache::remember("contact_detail_{$id}", self::CACHE_TIME, function () use ($id) {
            return Contact::findOrFail($id);
        });
    }

    /**
     * Tạo mới Contact - ✅ XÓA CACHE
     */
    public function create(array $data)
    {
        $contact = Contact::create($data);
        $this->clearCache();
        return $contact;
    }

    /**
     * Cập nhật Contact - ✅ XÓA CACHE
     */
    public function update($id, array $data)
    {
        $contact = Contact::findOrFail($id);
        $contact->update($data);
        $this->clearCache($id);
        return $contact;
    }

    /**
     * Xóa Contact - ✅ XÓA CACHE
     */
    public function delete($id)
    {
        $contact = Contact::findOrFail($id);
        $result = $contact->delete();
        $this->clearCache($id);
        return $result;
    }

    /**
     * ✅ Xóa Cache liên quan đến Contact
     */
    public function clearCache($id = null)
    {
        Cache::forget('contacts_all');
        if ($id) {
            Cache::forget("contact_detail_{$id}");
        }
    }
}