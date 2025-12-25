<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class News extends Model
{
    protected $table = 'news';
    protected $primaryKey = 'NewsId';
    public $timestamps = false;

    protected $casts = [
        'UserId' => 'int',
        'CreatedAt' => 'datetime',
        'UpdatedAt' => 'datetime',
        'CreatedBy' => 'int',
        'UpdatedBy' => 'int'
    ];

    protected $fillable = [
        'UserId',
        'Title',
        'Slug',
        'Content',
        'ImageUrl',
        'Status',
        'CreatedAt',
        'UpdatedAt',
        'CreatedBy',
        'UpdatedBy'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UpdatedBy');
    }

    // Sinh slug tự động dựa vào Title
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($news) {
            if (empty($news->Slug) && !empty($news->Title)) {
                $news->Slug = Str::slug($news->Title, '-');
            }
        });

        static::updating(function ($news) {
            if (!empty($news->Title)) {
                $news->Slug = Str::slug($news->Title, '-');
            }
        });
    }
}
