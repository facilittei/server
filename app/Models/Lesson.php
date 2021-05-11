<?php

namespace App\Models;

use DOMDocument;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Config;

class Lesson extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'chapter_id',
        'title',
        'description',
        'duration',
        'video',
        'audio',
        'doc',
        'position',
        'is_published',
    ];

    /**
     * The chapter.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * The comments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    /**
     * Format lesson with a chapter on top of a query result.
     *
     * @param array $result
     * @return array
     */
    public static function formatResultWithChapter($result)
    {
        $lessons = [];
        foreach ($result as $res) {
            $lessons[] = [
                'id' => $res->lesson_id,
                'title' => $res->lesson_title,
                'chapter' => [
                    'id' => $res->chapter_id,
                    'title' => $res->chapter_title,
                ],
            ];
        }
        return $lessons;
    }

    /**
     * Set the lesson's video.
     *
     * @param  string  $value
     * @return void
     */
    public function setVideoAttribute($value)
    {
        $components = parse_url($value);

        if (!$components) {
            return;
        }
        
        $params = null;

        if (!$components || !isset($components['query']) ) {
            return;
        }

        parse_str($components['query'], $params);

        if (!$params || !isset($params['v'])) {
            return;
        }

        $this->attributes['video'] = config('video.youtube') . $params['v'];
    }
}
