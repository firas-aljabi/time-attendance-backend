<?php

namespace App\Statuses;

class EmployeeStatus
{
    public const ACTIVE = 1;
    public const ON_VACATION = 2;
    public const ABSENT = 3;
    public const DISMISSED = 4;

    public static array $statuses = [self::ACTIVE, self::ON_VACATION, self::ABSENT, self::DISMISSED];
}
