<?php

use Filament\Panel;
use Leek\FilamentGiphy\FilamentGiphyPlugin;
use Leek\FilamentGiphy\GiphyRichContentPlugin;

beforeEach(function () {
    filament()->setCurrentPanel(null);
});

it('defaults the content rating to pg-13', function () {
    expect(GiphyRichContentPlugin::make()->rating())->toBe('pg-13');
});

it('resolves an unknown rating to pg-13', function () {
    config(['filament-giphy.rating' => 'not-a-rating']);

    expect(GiphyRichContentPlugin::make()->rating())->toBe('pg-13');
});

it('accepts the ratings GIPHY allows', function (string $rating) {
    config(['filament-giphy.rating' => $rating]);

    expect(GiphyRichContentPlugin::make()->rating())->toBe($rating);
})->with(['g', 'pg', 'pg-13', 'r']);

it('lets a panel rating override config', function () {
    config(['filament-giphy.rating' => 'r']);

    $panel = Panel::make()
        ->id('admin')
        ->plugin(FilamentGiphyPlugin::make()->rating('g'));

    filament()->setCurrentPanel($panel);

    expect(GiphyRichContentPlugin::make()->rating())->toBe('g');
});

it('hides the GIF tool when the API key is empty', function () {
    config(['filament-giphy.api_key' => null]);

    $plugin = GiphyRichContentPlugin::make();

    expect($plugin->getEditorTools())->toBe([])
        ->and($plugin->getEnabledToolbarButtons())->toBe([]);
});

it('enables the GIF tool when an API key is configured', function () {
    config(['filament-giphy.api_key' => 'test-key']);

    $plugin = GiphyRichContentPlugin::make();
    $tools = $plugin->getEditorTools();

    expect($tools)->toHaveCount(1)
        ->and($tools[0]->getName())->toBe('giphy')
        ->and($plugin->getEnabledToolbarButtons())->toBe(['giphy']);
});

it('uses the small fixed-height grid rendition and the original inserted rendition', function () {
    $plugin = GiphyRichContentPlugin::make();

    expect($plugin->gridRendition())->toBe('fixed_height_small')
        ->and($plugin->insertedRendition())->toBe('original');
});

it('lets a panel rendition override config', function () {
    config([
        'filament-giphy.grid_rendition' => 'fixed_height_small',
        'filament-giphy.inserted_rendition' => 'original',
    ]);

    $panel = Panel::make()
        ->id('admin')
        ->plugin(
            FilamentGiphyPlugin::make()
                ->gridRendition('fixed_width')
                ->insertedRendition('downsized'),
        );

    filament()->setCurrentPanel($panel);

    $plugin = GiphyRichContentPlugin::make();

    expect($plugin->gridRendition())->toBe('fixed_width')
        ->and($plugin->insertedRendition())->toBe('downsized');
});
