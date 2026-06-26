<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WebSetting extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'app_name',
        'app_tagline',
        'logo_path',
        'favicon_path',
        'site_description',
        'primary_color',
        'secondary_color',
    ];

    public function logoUrl(): ?string
    {
        return $this->assetUrl($this->logo_path);
    }

    public function faviconUrl(): ?string
    {
        return $this->assetUrl($this->favicon_path);
    }

    private function assetUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return Storage::disk('public')->url(ltrim($path, '/'));
    }
}
