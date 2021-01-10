<?php

namespace App\Models;

use DOMDocument;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
     * Set the lesson's audio.
     *
     * @param  string  $value
     * @return void
     */
    public function setAudioAttribute($value)
    {
        $this->attributes['audio'] = $this->setIframeURL($value);
    }

    /**
     * Set the lesson's video.
     *
     * @param  string  $value
     * @return void
     */
    public function setVideoAttribute($value)
    {
        $this->attributes['video'] = $this->setIframeURL($value);
    }

    /**
     * Set attribute as iframe src.
     *
     * @param  string  $value
     * @return string
     */
    private function setIframeURL($value)
    {
        $dom = new DOMDocument();
        $dom->loadHTML(str_replace('&', '&amp;', $value));
        $iframe = $dom->getElementsByTagName('iframe');
        $url = '';

        if (count($iframe)) {
            $url = html_entity_decode($iframe[0]->getAttribute('src'));
        }

        return $url;
    }
}
