<?php

namespace App\Enums\Networks;

enum NetworkStatusEnum: int
{
    case INACTIVE = 0; // not active yet / expired automatically
    case ACTIVE = 1; // currently active
    case DEACTIVATED = 2; // explicitly turned off by admin/user
    case EXTENDED = 3; // grace/extended period
    case SUSPENDED = 4; // temporarily blocked (e.g., abuse, payment hold)

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::DEACTIVATED => 'Deactivated',
            self::EXTENDED => 'Extended',
            self::SUSPENDED => 'Suspended',
        };
    }

    public static function fromValue(int $value): ?self
    {
        return self::tryFrom($value);
    }

    public function boolValue(): bool
    {
        return $this === self::ACTIVE;
    }

    // Convenience checks
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this === self::INACTIVE;
    }

    public function isDeactivated(): bool
    {
        return $this === self::DEACTIVATED;
    }

    public function isExtended(): bool
    {
        return $this === self::EXTENDED;
    }

    public function isSuspended(): bool
    {
        return $this === self::SUSPENDED;
    }

    // For selects (value => label)
    public static function options(): array
    {
        return [
            self::INACTIVE->value => self::INACTIVE->label(),
            self::ACTIVE->value => self::ACTIVE->label(),
            self::DEACTIVATED->value => self::DEACTIVATED->label(),
            self::EXTENDED->value => self::EXTENDED->label(),
            self::SUSPENDED->value => self::SUSPENDED->label(),
        ];
    }
}
