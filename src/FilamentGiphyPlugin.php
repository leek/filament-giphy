<?php

declare(strict_types=1);

namespace Leek\FilamentGiphy;

use BackedEnum;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentGiphyPlugin implements Plugin
{
    /** @var array<string, mixed> */
    protected array $overrides = [];

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'giphy';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function apiKey(?string $apiKey): static
    {
        $this->overrides['api_key'] = $apiKey;

        return $this;
    }

    public function rating(string $rating): static
    {
        $this->overrides['rating'] = $rating;

        return $this;
    }

    public function gridRendition(string $gridRendition): static
    {
        $this->overrides['grid_rendition'] = $gridRendition;

        return $this;
    }

    public function insertedRendition(string $insertedRendition): static
    {
        $this->overrides['inserted_rendition'] = $insertedRendition;

        return $this;
    }

    public function modalIcon(string|BackedEnum $icon): static
    {
        $this->overrides['modal_icon'] = $icon;

        return $this;
    }

    public function modalIconColor(string $color): static
    {
        $this->overrides['modal_icon_color'] = $color;

        return $this;
    }

    public function language(?string $language): static
    {
        $this->overrides['language'] = $language;

        return $this;
    }

    public function hasOverride(string $key): bool
    {
        return array_key_exists($key, $this->overrides);
    }

    public function getOverride(string $key): mixed
    {
        return $this->overrides[$key] ?? null;
    }
}
