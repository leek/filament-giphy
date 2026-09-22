<?php

declare(strict_types=1);

namespace Leek\FilamentGiphy;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\HasToolbarButtons;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Schemas\Components\View;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Icons\Heroicon;
use Leek\FilamentGiphy\TipTap\GiphyExtension;

class GiphyRichContentPlugin implements HasToolbarButtons, RichContentPlugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getTipTapPhpExtensions(): array
    {
        return [
            app(GiphyExtension::class),
        ];
    }

    public function getTipTapJsExtensions(): array
    {
        return [
            FilamentAsset::getScriptSrc('rich-content-plugins/giphy', 'leek/filament-giphy'),
        ];
    }

    public function getEditorTools(): array
    {
        if (! $this->settings()->apiKey()) {
            return [];
        }

        return [
            RichEditorTool::make('giphy')
                ->label('GIF')
                ->icon(Heroicon::Gif)
                ->action(),
        ];
    }

    public function getEditorActions(): array
    {
        if (! $this->settings()->apiKey()) {
            return [];
        }

        return [
            Action::make('giphy')
                ->modalHeading('Search GIFs')
                ->modalWidth(Width::FiveExtraLarge)
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->fillForm(fn (): array => [
                    'id' => null,
                    'src' => null,
                    'alt' => null,
                    'width' => null,
                    'height' => null,
                    'username' => null,
                    'profileUrl' => null,
                    'sourceUrl' => null,
                ])
                ->schema([
                    View::make('filament-giphy::picker')
                        ->viewData(fn (): array => [
                            'config' => $this->pickerConfig(),
                            'scriptUrl' => FilamentAsset::getScriptSrc('rich-content-plugins/giphy', 'leek/filament-giphy'),
                        ]),
                    Hidden::make('id'),
                    Hidden::make('src'),
                    Hidden::make('alt'),
                    Hidden::make('width'),
                    Hidden::make('height'),
                    Hidden::make('username'),
                    Hidden::make('profileUrl'),
                    Hidden::make('sourceUrl'),
                ])
                ->action(function (array $arguments, array $data, RichEditor $component): void {
                    $id = $data['id'] ?? null;
                    $src = $data['src'] ?? null;

                    if (! is_string($id) || $id === '') {
                        return;
                    }

                    if (! is_string($src) || ! str_starts_with($src, 'https://')) {
                        return;
                    }

                    $component->runCommands([
                        EditorCommand::make('insertContent', arguments: [[
                            'type' => 'giphy',
                            'attrs' => [
                                'id' => $id,
                                'src' => $src,
                                'alt' => is_string($data['alt'] ?? null) && $data['alt'] !== '' ? $data['alt'] : 'GIF',
                                'width' => $this->nullableInt($data['width'] ?? null),
                                'height' => $this->nullableInt($data['height'] ?? null),
                                'username' => $this->nullableString($data['username'] ?? null),
                                'profileUrl' => $this->nullableString($data['profileUrl'] ?? null),
                                'sourceUrl' => $this->nullableString($data['sourceUrl'] ?? null),
                            ],
                        ]]),
                    ], editorSelection: is_array($arguments['editorSelection'] ?? null) ? $arguments['editorSelection'] : null);
                }),
        ];
    }

    public function getEnabledToolbarButtons(): array
    {
        if (! $this->settings()->apiKey()) {
            return [];
        }

        return ['giphy'];
    }

    public function getDisabledToolbarButtons(): array
    {
        return [];
    }

    public function rating(): string
    {
        return $this->settings()->rating();
    }

    public function gridRendition(): string
    {
        return $this->settings()->gridRendition();
    }

    public function insertedRendition(): string
    {
        return $this->settings()->insertedRendition();
    }

    public function language(): ?string
    {
        return $this->settings()->language();
    }

    /**
     * @return array<string, mixed>
     */
    public function pickerConfig(): array
    {
        $settings = $this->settings();

        return [
            'apiKey' => $settings->apiKey(),
            'rating' => $settings->rating(),
            'gridRendition' => $settings->gridRendition(),
            'insertedRendition' => $settings->insertedRendition(),
            'language' => $settings->language(),
            'customerId' => $settings->customerId(),
        ];
    }

    protected function settings(): GiphySettings
    {
        return GiphySettings::current();
    }

    protected function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    protected function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return $value;
    }
}
