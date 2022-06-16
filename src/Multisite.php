<?php

namespace MediaBoutique\Multisite;

use Illuminate\Support\Facades\Cache;
use MediaBoutique\Multisite\Contracts\MultisiteModel;
use Illuminate\Support\Str;
use Exception;

class Multisite
{
    protected ?string $host = null;

    protected ?string $alias = null;

    protected ?MultisiteModel $site = null;

    public function __construct()
    {
        $model = config('multisite.model');

        $alias = config('multisite.alias');

        if (!$model || !class_exists($model)) {
            throw new Exception("Model {$model} not found!");
        }

        if (!in_array(MultisiteModel::class, class_implements($model))) {
            throw new Exception("Model {$model} doesn\'t implement Multisite contract!");
        }

        if (!$alias) {
            throw new Exception("No alias provided!");
        }
    }

    public function init(string $host): self
    {
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
}
