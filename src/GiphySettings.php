<?php

declare(strict_types=1);

namespace Leek\FilamentGiphy;

use Filament\Panel;

class GiphySettings
{
    /** @var list<string> */
    public const RATINGS = ['g', 'pg', 'pg-13', 'r'];

    public function __construct(protected ?FilamentGiphyPlugin $panelPlugin = null) {}

    public static function current(): self
    {
        return new self(self::panelPlugin());
    }

    public function apiKey(): ?string
    {
        if ($this->panelPlugin?->hasOverride('api_key')) {
            return self::filledString($this->panelPlugin->getOverride('api_key'));
        }

        return self::filledString(config('filament-giphy.api_key'));
    }

    public function rating(): string
    {
        $rating = $this->panelPlugin?->hasOverride('rating')
            ? $this->panelPlugin->getOverride('rating')
            : config('filament-giphy.rating');

        return self::normalizeRating($rating);
    }

    public function gridRendition(): string
    {
        return $this->rendition('grid_rendition', 'fixed_height_small');
    }

    public function insertedRendition(): string
    {
        return $this->rendition('inserted_rendition', 'original');
    }

    public function language(): ?string
    {
        if ($this->panelPlugin?->hasOverride('language')) {
            return self::normalizeLanguage($this->panelPlugin->getOverride('language'));
        }

        $configured = config('filament-giphy.language');

        if (is_string($configured) && $configured !== '') {
            return self::normalizeLanguage($configured);
        }

        return self::normalizeLanguage(config('app.locale'));
    }

    public function customerId(): ?string
    {
        $id = auth()->id();

        if ($id === null || $id === '') {
            return null;
        }

        return (string) $id;
    }

    public static function normalizeRating(mixed $rating): string
    {
        if (! is_string($rating)) {
            return 'pg-13';
        }

        $rating = strtolower(trim($rating));

        if (! in_array($rating, self::RATINGS, true)) {
            return 'pg-13';
        }

        return $rating;
    }

    public static function normalizeLanguage(mixed $language): ?string
    {
        if (! is_string($language)) {
            return null;
        }

        $language = trim($language);

        if (preg_match('/^[a-z]{2}$/i', $language) === 1) {
            return strtolower($language);
        }

        if (preg_match('/^([a-z]{2})[_-]/i', $language, $matches) === 1) {
            return strtolower($matches[1]);
        }

        return null;
    }

    protected function rendition(string $key, string $default): string
    {
        $value = $this->panelPlugin?->hasOverride($key)
            ? $this->panelPlugin->getOverride($key)
            : config('filament-giphy.'.$key);

        if (! is_string($value) || trim($value) === '') {
            return $default;
        }

        return trim($value);
    }

    protected static function filledString(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return $value;
    }

    protected static function panelPlugin(): ?FilamentGiphyPlugin
    {
        if (! app()->bound('filament')) {
            return null;
        }

        $panel = filament()->getCurrentPanel();

        if (! $panel instanceof Panel || ! $panel->hasPlugin('giphy')) {
            return null;
        }

        $plugin = $panel->getPlugin('giphy');

        return $plugin instanceof FilamentGiphyPlugin ? $plugin : null;
    }
}
