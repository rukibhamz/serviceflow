<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeArticleVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'title',
        'body',
        'editor_id',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeArticle::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }
}
