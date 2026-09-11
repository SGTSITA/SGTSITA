<?php

namespace App\Traits;

use App\Observers\AuditObserver;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::observe(AuditObserver::class);
    }
}
