<?php

namespace App\Services;

use App\Repositories\BannerRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;

class BannerService
{
    protected $bannerRepository;
    protected $cloudinary;

    public function __construct(BannerRepository $bannerRepository)
    {
        $this->bannerRepository = $bannerRepository;
        
        // Khởi tạo Cloudinary để xử lý ảnh
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => config('cloudinary.secure', true),
            ],
        ]);
    }

    public function getAll()
    {
        try {
            return $this->bannerRepository->getAll();
        } catch (\Exception $e) {
            Log::error('Lỗi lấy danh sách banner: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getById($id)
    {
        try {
            $banner = $this->bannerRepository->getById($id);
            
            if (!$banner) {
                throw new \Exception('Không tìm thấy banner với ID: ' . $id);
            }
            
            return $banner;
        } catch (\Exception $e) {
            Log::error('Lỗi lấy banner: ' . $e->getMessage());
            throw $e;
        }
    }

    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            // Thêm thông tin người tạo
            if (Auth::check()) {
                $data['CreatedBy'] = Auth::user()->UserId;
                $data['UserId'] = Auth::user()->UserId;
            }
            
            $data['CreatedAt'] = now();
            $data['UpdatedAt'] = now();
            
            // Nếu ImageUrl là file path, upload lên Cloudinary
            if (isset($data['ImageUrl']) && is_string($data['ImageUrl']) && 
                strpos($data['ImageUrl'], 'cloudinary.com') === false) {
                // Kiểm tra xem có phải là file upload không
                if (isset($data['ImageFile']) && $data['ImageFile']) {
                    $result = $this->uploadToCloudinary($data['ImageFile'], 'banners');
                    if ($result) {
                        $data['ImageUrl'] = $result;
                    }
                }
            }
            
            $banner = $this->bannerRepository->create($data);
            
            DB::commit();
            Log::info('Tạo banner thành công: ' . $banner->BannerId);
            
            return $banner;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo banner: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();
        try {
            $banner = $this->bannerRepository->getById($id);
            
            if (!$banner) {
                throw new \Exception('Không tìm thấy banner với ID: ' . $id);
            }
            
            // Thêm thông tin người cập nhật
            if (Auth::check()) {
                $data['UpdatedBy'] = Auth::user()->UserId;
            }
            
            $data['UpdatedAt'] = now();
            
            // Nếu có ảnh mới, xóa ảnh cũ và upload ảnh mới
            if (isset($data['ImageFile']) && $data['ImageFile']) {
                // Xóa ảnh cũ trên Cloudinary
                if ($banner->ImageUrl && strpos($banner->ImageUrl, 'cloudinary.com') !== false) {
                    $this->deleteFromCloudinary($banner->ImageUrl);
                }
                
                // Upload ảnh mới
                $result = $this->uploadToCloudinary($data['ImageFile'], 'banners');
                if ($result) {
                    $data['ImageUrl'] = $result;
                }
            }
            
            $banner = $this->bannerRepository->update($id, $data);
            
            DB::commit();
            Log::info('Cập nhật banner thành công: ' . $id);
            
            return $banner;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật banner: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $banner = $this->bannerRepository->getById($id);
            
            if (!$banner) {
                throw new \Exception('Không tìm thấy banner với ID: ' . $id);
            }
            
            // Xóa ảnh trên Cloudinary
            if ($banner->ImageUrl && strpos($banner->ImageUrl, 'cloudinary.com') !== false) {
                $this->deleteFromCloudinary($banner->ImageUrl);
            }
            
            $result = $this->bannerRepository->delete($id);
            
            DB::commit();
            Log::info('Xóa banner thành công: ' . $id);
            
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi xóa banner: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Upload ảnh lên Cloudinary
     */
    protected function uploadToCloudinary($file, $folder)
    {
        try {
            $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => $folder,
                'transformation' => [
                    'width' => 1200,
                    'height' => 500,
                    'crop' => 'fill',
                    'quality' => 'auto:best',
                    'fetch_format' => 'auto'
                ]
            ]);
            
            return $result['secure_url'] ?? null;
        } catch (\Exception $e) {
            Log::error('Upload ảnh lên Cloudinary thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Xóa ảnh trên Cloudinary
     */
    protected function deleteFromCloudinary($url)
    {
        try {
            $publicId = $this->extractPublicId($url);
            if ($publicId) {
                $this->cloudinary->uploadApi()->destroy($publicId);
                Log::info('Xóa ảnh Cloudinary thành công: ' . $publicId);
            }
        } catch (\Exception $e) {
            Log::error('Xóa ảnh Cloudinary thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy public_id từ URL Cloudinary
     */
    protected function extractPublicId($url)
    {
        preg_match('/\/upload\/v\d+\/(.+)\.\w+$/', $url, $matches);
        return $matches[1] ?? null;
    }
}