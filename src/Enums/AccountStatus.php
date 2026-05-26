<?php

namespace Sashalenz\NovapayApi\Enums;

enum AccountStatus: string
{
    case Active = 'Active';
    case Closed = 'Closed';
    case OnApproval = 'OnApproval';
    case Arrested = 'Arrested';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Активний',
            self::Closed => 'Закритий',
            self::OnApproval => 'Очікує підтвердження від ДПС',
            self::Arrested => 'Заарештований',
        };
    }

    public function statusCode(): int
    {
        return match ($this) {
            self::Active => 1,
            self::Closed => 0,
            self::OnApproval => 4,
            self::Arrested => 2,
        };
    }
}
