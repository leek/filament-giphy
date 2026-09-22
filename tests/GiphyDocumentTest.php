<?php

use Leek\FilamentGiphy\TipTap\GiphyExtension;
use Tiptap\Editor;
use Tiptap\Nodes\Document;

function giphyDocument(array $attrs): array
{
    return [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'giphy',
                'attrs' => $attrs,
            ],
        ],
    ];
}

function renderGiphy(array $attrs): string
{
    return (new Editor([
        'extensions' => [
            new Document,
            new GiphyExtension,
        ],
    ]))->setContent(giphyDocument($attrs))->getHTML();
}

function parseGiphy(string $html): array
{
    $document = (new Editor([
        'extensions' => [
            new Document,
            new GiphyExtension,
        ],
    ]))->setContent($html)->getDocument();

    return $document['content'][0]['attrs'];
}

it('round-trips a GIF block through HTML with the query string intact', function () {
    $src = 'https://media.giphy.com/media/abc/giphy.gif?cid=abc&rid=giphy';

    $html = renderGiphy([
        'id' => 'abc',
        'src' => $src,
        'alt' => 'A cat',
        'width' => 200,
        'height' => 100,
        'username' => null,
        'profileUrl' => null,
        'sourceUrl' => null,
    ]);

    $attrs = parseGiphy($html);

    expect($attrs['id'])->toBe('abc')
        ->and($attrs['src'])->toBe($src)
        ->and($attrs['alt'])->toBe('A cat')
        ->and($attrs['width'])->toBe(200)
        ->and($attrs['height'])->toBe(100);
});

it('links the creator name to the profile URL', function () {
    $html = renderGiphy([
        'id' => 'abc',
        'src' => 'https://media.giphy.com/media/abc/giphy.gif',
        'alt' => 'A cat',
        'username' => 'moodman',
        'profileUrl' => 'https://giphy.com/channel/moodman',
        'sourceUrl' => 'https://giphy.com/gifs/abc',
    ]);

    $attrs = parseGiphy($html);

    expect($html)->toContain('href="https://giphy.com/channel/moodman"')
        ->and($html)->toContain('<figcaption>')
        ->and($attrs['username'])->toBe('moodman')
        ->and($attrs['profileUrl'])->toBe('https://giphy.com/channel/moodman')
        ->and($attrs['sourceUrl'])->toBe('https://giphy.com/gifs/abc');
});

it('links the creator name to the GIF page when no profile URL is stored', function () {
    $html = renderGiphy([
        'id' => 'abc',
        'src' => 'https://media.giphy.com/media/abc/giphy.gif',
        'alt' => 'A cat',
        'username' => 'moodman',
        'profileUrl' => null,
        'sourceUrl' => 'https://giphy.com/gifs/abc',
    ]);

    expect($html)->toContain('href="https://giphy.com/gifs/abc"')
        ->and($html)->not->toContain('data-giphy-profile-url');
});

it('shows the creator name as text when no URL is stored', function () {
    $html = renderGiphy([
        'id' => 'abc',
        'src' => 'https://media.giphy.com/media/abc/giphy.gif',
        'alt' => 'A cat',
        'username' => 'moodman',
        'profileUrl' => null,
        'sourceUrl' => null,
    ]);

    expect($html)->toContain('<figcaption>moodman</figcaption>')
        ->and($html)->not->toContain('<a ');
});

it('omits the credit line when the GIF has no creator', function () {
    $html = renderGiphy([
        'id' => 'abc',
        'src' => 'https://media.giphy.com/media/abc/giphy.gif',
        'alt' => 'A cat',
        'username' => null,
    ]);

    expect($html)->not->toContain('<figcaption');
});

it('renders no image when the media URL is not https', function (string $src) {
    $html = renderGiphy([
        'id' => 'abc',
        'src' => $src,
        'alt' => 'A cat',
    ]);

    expect($html)->not->toContain('<img')
        ->and($html)->toContain('data-giphy-id="abc"');
})->with([
    'http://media.giphy.com/media/abc/giphy.gif',
    'javascript:alert(1)',
]);
