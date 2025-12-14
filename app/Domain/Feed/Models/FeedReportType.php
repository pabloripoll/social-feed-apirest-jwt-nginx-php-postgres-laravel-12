<?php

namespace App\Domain\Feed\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedReportType extends Model
{
    /** @use HasFactory<\App\Domain\Feed\Database\Factories\FeedReportType> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'feed_report_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'level',
        'position',
        'title',
        'description',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }
}
