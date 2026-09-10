<?php

namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    /**
     * Aplica el scope de empresa a una consulta de Eloquent.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (CompanyContext::check()) {
            if ($model instanceof \App\Models\User) {
                $builder->where(function (Builder $q) use ($model) {
                    $q->where($model->qualifyColumn('empresa_id'), CompanyContext::getId())
                        ->orWhereNull($model->qualifyColumn('empresa_id'));
                });
            } else {
                $builder->where($model->qualifyColumn('empresa_id'), CompanyContext::getId());
            }
        }
    }
}
