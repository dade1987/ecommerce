<?php

declare(strict_types=1);

namespace Modules\Xot\Traits;

/**
 * Trait Updater.
 * https://dev.to/hasanmn/automatically-update-createdby-and-updatedby-in-laravel-using-bootable-traits-28g9.
 */
trait Updater
{
    /**
     * bootUpdater function.
     */
    protected static function bootUpdater(): void
    {
        /*
         * During a model create Eloquent will also update the updated_at field so
         * need to have the updated_by field here as well.
         */
        static::creating(
            function ($model) {
                if (null !== auth()->user()) {
                    $model->created_by = auth()->user()->handle ?? '';
                    $model->updated_by = auth()->user()->handle ?? '';
                }
            }
        );

        /*
         * updating.
         */
        static::updating(
            function ($model) {
                $model->updated_by = auth()->user()->handle ?? '';
            }
        );
    }

    // end function boot
}// end trait Updater