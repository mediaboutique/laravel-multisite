<?php

namespace MediaBoutique\Multisite\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Multisite
{
    public function scopeHost(Builder $query, string $host): Builder;
}
