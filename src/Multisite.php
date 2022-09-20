<?php

namespace MediaBoutique\Multisite;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use MediaBoutique\Multisite\Contracts\MultisiteModel;

class Multisite
{
    protected ?string $host = null;

    protected ?string $alias = null;

    protected ?MultisiteModel $site = null;

    public function init(string $host): self
    {
        $this->check();

        $this->host = $host;

        $model = config('multisite.model');

        $alias = config('multisite.alias');

        if (config('multisite.cache.enabled')) {
            $site = Cache::remember("multisite_host:" . Str::slug($host) . "_site", (60 * 60), function () use ($model, $host) {
                return $model::host($host)->first();
            });
        } else {
            $site = $model::host($host)->first();
        }
        if ($site) {
            $this->site = $site;
            $this->alias = $site->{$alias};
        }

        return $this;
    }

    public function site(): ?MultisiteModel
    {
        return $this->site;
    }

    public function alias(): ?string
    {
        return $this->alias;
    }

    public function installed(): bool
    {
        if (!config('multisite')) {
            return false;
        }

        $model = config('multisite.model');

        $alias = config('multisite.alias');

        if (!$model || !class_exists($model)) {
            return false;
        }

        if (!in_array(MultisiteModel::class, class_implements($model))) {
            return false;
        }

        if (!Schema::hasTable((new $model())->getTable())) {
            return false;
        }

        if (!$alias) {
            return false;
        }

        return true;
    }

    public function active(): bool
    {
        return (!empty($this->site) && !empty($this->alias));
    }

    public function __call($name, $arguments)
    {
        if (!in_array($name, ['view', 'asset', 'mix', 'route'])) {
            throw new Exception("Undefined method: {$name}");
        }

        if ($name === 'view') {
            return view($this->alias . '::' . array_shift($arguments), ...$arguments);
        } elseif ($name === 'route') {
            return route($this->alias . '::' . array_shift($arguments), ...$arguments);
        } elseif (in_array($name, ['asset', 'mix'])) {
            return asset('assets/sites/' . $this->alias . '/' . array_shift($arguments), ...$arguments);
        }
    }

    protected function check()
    {
        $model = config('multisite.model');

        $alias = config('multisite.alias');

        if (!$model || !class_exists($model)) {
            throw new Exception("Model {$model} not found!");
        }

        if (!in_array(MultisiteModel::class, class_implements($model))) {
            throw new Exception("Model {$model} doesn\'t implement Multisite contract!");
        }

        if (!Schema::hasTable((new $model())->getTable())) {
            throw new Exception("Table " . (new $model())->getTable() . " not found!");
        }

        if (!$alias) {
            throw new Exception("No alias provided!");
        }
    }
}
