<?php

namespace MediaBoutique\Multisite;

use MediaBoutique\Multisite\Contracts\MultisiteModel;

use Exception;

class Multisite
{
    protected ?string $host = null;

    protected ?string $alias = null;

    protected $site = null;

    public function init(string $host)
    {
        $this->host = $host;

        $model = config('multisite.model');

        if (!$model || !class_exists($model)) {
            throw new Exception("Model {$model} not found!");
        }

        if (!in_array(MultisiteModel::class, class_implements($model))) {
            throw new Exception("Model {$model} doesn\'t implement Multisite contract!");
        }

        $this->site = $model::host($host)->first();

        if (!$this->site) {
            throw new Exception("Site not found!");
        }

        $site = $this->site->toArray();
        $this->alias = $site[config('multisite.alias')] ?? null;

        if (!$this->alias) {
            throw new Exception("No alias set!");
        }
    }

    public function alias(): ?string
    {
        return $this->alias;
    }

    public function model()
    {
        return $this->site;
    }

    public function active(): bool
    {
        return (!empty($this->site) && !empty($this->alias));
    }

    public function __call($name, $arguments)
    {
        if (!in_array($name, ['view', 'asset', 'route'])) {
            throw new Exception("Undefined method!");
        }
        if (!$this->site) {
            throw new Exception("Site not found!");
        }
        if (!$this->alias) {
            throw new Exception("No alias set!");
        }

        if ($name === 'view') {
            return view($this->alias . '::' . array_shift($arguments), ...$arguments);
        } elseif ($name === 'route') {
            return route($this->alias . '::' . array_shift($arguments), ...$arguments);
        } elseif ($name === 'asset') {
            return asset('sites/' . $this->alias . '/' . array_shift($arguments), ...$arguments);
        }
    }
}
