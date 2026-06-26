<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use LogsModelActivity;

    public const ACTIONS = ['read', 'list', 'delete', 'assign', 'show', 'create', 'edit', 'update', 'notify'];

    public const ACTION_LABELS = [
        'read' => 'Read',
        'list' => 'List',
        'delete' => 'Delete',
        'assign' => 'Assign',
        'show' => 'Show',
        'create' => 'Create',
        'edit' => 'Edit',
        'update' => 'Update',
        'notify' => 'Notifikasi',
    ];

    public const LAYOUT_TABLE_BASE = 'table_base';

    public const LAYOUT_FORM_BASE = 'form_base';

    public const LAYOUT_TYPES = [
        self::LAYOUT_TABLE_BASE,
        self::LAYOUT_FORM_BASE,
    ];

    protected $fillable = [
        'module_group_id',
        'title',
        'code',
        'layout_type',
        'route_name',
        'icon',
        'description',
        'is_active',
        'show_in_sidebar',
        'has_notifications',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_in_sidebar' => 'boolean',
            'has_notifications' => 'boolean',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ModuleGroup::class, 'module_group_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ModuleAction::class);
    }

    public function enabledActions(): HasMany
    {
        return $this->actions()->where('is_enabled', true);
    }

    public function scopeVisibleInSidebar(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('show_in_sidebar', true)
            ->whereNotNull('route_name')
            ->where('route_name', '!=', '');
    }

    public static function actionPreset(string $layoutType): array
    {
        return match ($layoutType) {
            self::LAYOUT_FORM_BASE => ['list', 'read', 'create', 'update'],
            default => ['list', 'show', 'create', 'edit', 'update', 'delete'],
        };
    }

    public function layoutTypeLabel(): string
    {
        return match ($this->layout_type) {
            self::LAYOUT_FORM_BASE => 'Form base',
            default => 'Table base',
        };
    }

    public function isFormBase(): bool
    {
        return $this->layout_type === self::LAYOUT_FORM_BASE;
    }

    public static function isBuiltInAction(string $action): bool
    {
        return in_array($action, self::ACTIONS, true);
    }

    public static function slugFromLabel(string $label): string
    {
        return (string) str($label)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->limit(50, '');
    }

    public static function actionLabel(string $action, ?string $customLabel = null): string
    {
        if ($customLabel !== null && $customLabel !== '') {
            return $customLabel;
        }

        return self::ACTION_LABELS[$action] ?? str($action)->replace('_', ' ')->title();
    }

    public static function permissionSuffixForAction(string $action): string
    {
        return $action;
    }

    public function permissionNameForAction(string $action): string
    {
        return $this->code.'.'.self::permissionSuffixForAction($action);
    }

    public function permissionNames(): array
    {
        return $this->enabledActions
            ->map(fn (ModuleAction $action) => $this->permissionNameForAction($action->action))
            ->unique()
            ->values()
            ->all();
    }

    public function sidebarPermissionName(): ?string
    {
        if ($this->enabledActions->contains(fn (ModuleAction $action) => $action->action === 'list')) {
            return $this->permissionNameForAction('list');
        }

        return $this->permissionNames()[0] ?? null;
    }
}
