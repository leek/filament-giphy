# Filament GIPHY

Search GIPHY from a Filament rich text field and insert the GIF into the document.

The browser calls GIPHY. The package does not proxy or cache those requests. The saved document keeps the GIF id, the exact media URL GIPHY returned, the title, and the creator.

## Installation

```bash
composer require leek/filament-giphy
php artisan vendor:publish --tag=filament-giphy-config
php artisan filament:assets
```

Set the key in `.env`:

```env
GIPHY_API_KEY=your-key
```

GIPHY requires the search call to come from the browser, so the key is visible on the page. Treat it as a public key. A beta key allows 100 calls per hour, and every author on that key shares the limit.

## Usage

Opt in on each rich text field. Add the same plugin to the renderer when the field stores TipTap JSON.

```php
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Leek\FilamentGiphy\GiphyRichContentPlugin;

RichEditor::make('content')
    ->plugins([
        GiphyRichContentPlugin::make(),
    ]);

RichContentRenderer::make($record->content)
    ->plugins([
        GiphyRichContentPlugin::make(),
    ]);
```

An empty key hides the toolbar button.

## Panel overrides

Register the panel plugin when one panel needs a different key, rating, rendition, or picker icon. Fields outside a panel, and panels without the plugin, use the config file.

```php
use Leek\FilamentGiphy\FilamentGiphyPlugin;

$panel->plugin(
    FilamentGiphyPlugin::make()
        ->rating('pg-13')
        ->gridRendition('fixed_width')
        ->insertedRendition('original')
        ->modalIcon('heroicon-o-gif')
        ->modalIconColor('primary'),
);
```

The default rating is `pg-13`. Allowed values are `g`, `pg`, `pg-13`, and `r`. Anything else resolves to `pg-13`.

The picker modal shows the `o-gif` Heroicon in `primary`. Set `modal_icon` and `modal_icon_color` in the config, or use the panel methods above, to match your icon set.

## What gets stored

A click copies the configured rendition's `url` into a `giphy` block. The default inserted rendition is `original`. The grid uses `fixed_width`, then `fixed_height` when that preview is missing, and lays the previews out at their own aspect ratio. A missing inserted rendition inserts nothing. The package does not rewrite the URL's query string.

The block renders a `figure` holding only the GIF. An `https` media URL becomes the image. Any other scheme renders no image. The creator's name and links are kept as `data-giphy-*` attributes, not shown.

## GIPHY

The picker shows GIPHY's result order and sends the configured rating on the request. Search runs against `https://api.giphy.com/v1/gifs/search`. An empty query uses `https://api.giphy.com/v1/gifs/trending`.

GIPHY's current integration rules say a standard integration should not store media URLs. This package stores them so a saved HTML or JSON document can show the GIF later. A production key review can fail for that reason.
