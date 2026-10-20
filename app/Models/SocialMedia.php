<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SocialMedia extends Model
{
    use HasFactory;

    /** The built-in icon set — key must match the SVG map in the icon partial */
    public const ICONS = [
        'facebook'  => ['label' => 'Facebook',   'color' => '#1877F2'],
        'x-twitter' => ['label' => 'X (Twitter)', 'color' => '#111111'],
        'instagram' => ['label' => 'Instagram',  'color' => '#E4405F'],
        'whatsapp'  => ['label' => 'WhatsApp',   'color' => '#25D366'],
        'youtube'   => ['label' => 'YouTube',    'color' => '#FF0000'],
        'tiktok'    => ['label' => 'TikTok',     'color' => '#010101'],
        'linkedin'  => ['label' => 'LinkedIn',   'color' => '#0A66C2'],
        'telegram'  => ['label' => 'Telegram',   'color' => '#26A5E4'],
        'pinterest' => ['label' => 'Pinterest',  'color' => '#BD081C'],
        'snapchat'  => ['label' => 'Snapchat',   'color' => '#F7C900'],
        'website'   => ['label' => 'Website',    'color' => '#0d5a39'],
    ];

    protected $fillable = ['user_id', 'name', 'url', 'icon', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    /** Brand color for hover styling (falls back to theme green) */
    public function getBrandColorAttribute(): string
    {
        return self::ICONS[$this->icon]['color'] ?? '#0d5a39';
    }
}
