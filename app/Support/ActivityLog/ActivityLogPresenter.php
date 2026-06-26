<?php

namespace App\Support\ActivityLog;

use App\Models\AppConfig;
use App\Models\ImpersonationLog;
use App\Models\MasterData;
use App\Models\Menu;
use App\Models\Module;
use App\Models\ModuleAction;
use App\Models\ModuleGroup;
use App\Models\Role;
use App\Models\User;
use App\Models\WebSetting;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

class ActivityLogPresenter
{
    /** @var array<class-string, string> */
    private const SUBJECT_TYPE_LABELS = [
        User::class => 'Pengguna',
        MasterData::class => 'Master Data',
        Role::class => 'Role',
        Module::class => 'Modul',
        ModuleGroup::class => 'Grup Modul',
        ModuleAction::class => 'Aksi Modul',
        Menu::class => 'Menu',
        WebSetting::class => 'Web Setting',
        AppConfig::class => 'Konfigurasi',
        ImpersonationLog::class => 'Log Impersonate',
    ];

    public static function subjectTypeLabel(?string $subjectType): string
    {
        if ($subjectType === null || $subjectType === '') {
            return '-';
        }

        return self::SUBJECT_TYPE_LABELS[$subjectType]
            ?? class_basename($subjectType);
    }

    public static function subjectLabel(Activity $activity): ?string
    {
        if ($activity->subject_id === null) {
            return null;
        }

        $subject = $activity->subject;

        if ($subject instanceof User) {
            return $subject->name;
        }

        if ($subject instanceof MasterData) {
            return $subject->name;
        }

        if ($subject instanceof Role) {
            return $subject->displayName();
        }

        if ($subject instanceof Module) {
            return $subject->title;
        }

        if ($subject instanceof ModuleGroup) {
            return $subject->name;
        }

        if ($subject instanceof ModuleAction) {
            return $subject->action;
        }

        if ($subject instanceof Menu) {
            return $subject->label;
        }

        if ($subject instanceof WebSetting) {
            return $subject->app_name;
        }

        if ($subject instanceof Model) {
            foreach (['name', 'title', 'label', 'code'] as $attribute) {
                $value = $subject->getAttribute($attribute);

                if (is_string($value) && $value !== '') {
                    return $value;
                }
            }
        }

        return '#'.$activity->subject_id;
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(Activity $activity): array
    {
        $causer = $activity->causer;

        return [
            'id' => $activity->id,
            'description' => $activity->description,
            'event' => $activity->event,
            'subject_type' => $activity->subject_type,
            'subject_type_label' => self::subjectTypeLabel($activity->subject_type),
            'subject_id' => $activity->subject_id,
            'subject_label' => self::subjectLabel($activity),
            'causer' => $causer instanceof User ? [
                'id' => $causer->id,
                'name' => $causer->name,
                'email' => $causer->email,
            ] : null,
            'properties' => $activity->properties?->toArray(),
            'created_at' => $activity->created_at?->toIso8601String(),
        ];
    }
}
