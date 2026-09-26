<?php

namespace App\Enums\AssignType;

enum AssignTypeEnum: string
{
    case EMPLOYEE = 'Employee';
    case CLIENT = 'Client';
    case VLAN = 'Vlan';
    case BRANCH = 'Branch';

    public function label(): string
    {
        return match ($this) {
            self::EMPLOYEE => 'Employee',
            self::CLIENT => 'Client',
            self::VLAN => 'Vlan',
            self::BRANCH => 'Branch',
        };
    }

    public static function fromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }

    
    public static function options(): array
    {
        return [
            self::EMPLOYEE->value => self::EMPLOYEE->label(),
            self::CLIENT->value => self::CLIENT->label(),
            self::VLAN->value => self::VLAN->label(),
            self::BRANCH->value => self::BRANCH->label(),
        ];
    }
}
