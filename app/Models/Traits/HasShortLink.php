<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Short link for sharing: /s/{short_code}
 * Used by Post (articles), Profile and Report. The code is made automatically
 * when an item is created, or the first time an older item's short link is needed.
 */
trait HasShortLink
{
    /** Letters/numbers that can't be confused (no 0/O, 1/l/I). */
    protected static string $shortAlphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function bootHasShortLink(): void
    {
        static::creating(function ($model) {
            if (empty($model->short_code) && static::shortLinksReady($model->getTable())) {
                $model->short_code = static::newShortCode();
            }
        });
    }

    /** Full short link, e.g. https://theunicornmagazine.com/s/K7x2Qa */
    public function getShortUrlAttribute(): string
    {
        if (empty($this->short_code) && $this->exists && static::shortLinksReady($this->getTable())) {
            try {
                $code = static::newShortCode();
                // save without touching "updated_at"
                DB::table($this->getTable())->where('id', $this->id)->whereNull('short_code')->update(['short_code' => $code]);
                $this->short_code = DB::table($this->getTable())->where('id', $this->id)->value('short_code') ?: $code;
            } catch (\Throwable $e) {
                return $this->url ?? $this->link ?? url('/');   // column not added yet
            }
        }

        return $this->short_code ? route('short', $this->short_code) : ($this->url ?? $this->link ?? url('/'));
    }

    /** True once the short_code column exists (after the database update). */
    protected static function shortLinksReady(string $table): bool
    {
        static $ready = [];

        try {
            return $ready[$table] ??= Schema::hasColumn($table, 'short_code');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** A 6-character code not used by any article, profile or report. */
    public static function newShortCode(int $length = 6): string
    {
        $alphabet = static::$shortAlphabet;
        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $taken = collect(['posts', 'profiles', 'reports'])
                ->contains(fn ($table) => DB::table($table)->where('short_code', $code)->exists());
        } while ($taken);

        return $code;
    }
}
