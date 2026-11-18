<?php

namespace App\Support;

class BookingStatus
{
    public const PENDING = 'Pending';

    public const ACCEPTED = 'Accepted';

    public const CONFIRMED = 'Confirmed';

    public const CHECKED_IN = 'Checked-in';

    public const CHECKED_OUT = 'Checked-out';

    public const COMPLETED = 'Completed';

    public const CANCELLED = 'Cancelled';

    public const REJECTED = 'Rejected';

    /**
     * Canonical lifecycle used by business rules and future workflows.
     * History records that still say "Checked-out" are accepted as legacy values
     * and mapped onto the terminal Completed state for compatibility.
     *
     * @return array<int, string>
     */
    public const CANONICAL_LIFECYCLE = [
        self::PENDING,
        self::ACCEPTED,
        self::CONFIRMED,
        self::CHECKED_IN,
        self::COMPLETED,
    ];

    public static function canonical(string $status): string
    {
        return $status === self::CHECKED_OUT ? self::COMPLETED : $status;
    }

    public static function isCanonicalLifecycleStatus(string $status): bool
    {
        return in_array(self::canonical($status), self::CANONICAL_LIFECYCLE, true);
    }

    public static function isActive(string $status): bool
    {
        return in_array(self::canonical($status), [
            self::PENDING,
            self::ACCEPTED,
            self::CONFIRMED,
            self::CHECKED_IN,
        ], true);
    }

    public static function isTerminal(string $status): bool
    {
        return self::canonical($status) === self::COMPLETED;
    }

    /**
     * @return array<int, string>
     */
    public static function completedStatuses(): array
    {
        return [self::COMPLETED, self::CHECKED_OUT];
    }

    public static function isLegacyCheckedOut(string $status): bool
    {
        return $status === self::CHECKED_OUT;
    }
}
