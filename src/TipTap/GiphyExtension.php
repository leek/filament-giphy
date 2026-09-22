<?php

declare(strict_types=1);

namespace Leek\FilamentGiphy\TipTap;

use DOMElement;
use Tiptap\Core\Node;

class GiphyExtension extends Node
{
    public static $name = 'giphy';

    public function parseHTML(): array
    {
        return [
            [
                'tag' => 'figure[data-giphy-id]',
            ],
        ];
    }

    public function addAttributes(): array
    {
        return [
            'id' => [
                'default' => null,
                'parseHTML' => fn (DOMElement $DOMNode): ?string => $this->attribute($DOMNode, 'data-giphy-id'),
            ],
            'src' => [
                'default' => null,
                'parseHTML' => function (DOMElement $DOMNode): ?string {
                    $image = $DOMNode->getElementsByTagName('img')->item(0);

                    if (! $image instanceof DOMElement) {
                        return null;
                    }

                    return $this->attribute($image, 'src');
                },
            ],
            'alt' => [
                'default' => null,
                'parseHTML' => function (DOMElement $DOMNode): ?string {
                    $image = $DOMNode->getElementsByTagName('img')->item(0);

                    if (! $image instanceof DOMElement) {
                        return null;
                    }

                    return $this->attribute($image, 'alt');
                },
            ],
            'width' => [
                'default' => null,
                'parseHTML' => fn (DOMElement $DOMNode): ?int => $this->dimension($DOMNode, 'width'),
            ],
            'height' => [
                'default' => null,
                'parseHTML' => fn (DOMElement $DOMNode): ?int => $this->dimension($DOMNode, 'height'),
            ],
            'username' => [
                'default' => null,
                'parseHTML' => fn (DOMElement $DOMNode): ?string => $this->attribute($DOMNode, 'data-giphy-username'),
            ],
            'profileUrl' => [
                'default' => null,
                'parseHTML' => fn (DOMElement $DOMNode): ?string => $this->attribute($DOMNode, 'data-giphy-profile-url'),
            ],
            'sourceUrl' => [
                'default' => null,
                'parseHTML' => fn (DOMElement $DOMNode): ?string => $this->attribute($DOMNode, 'data-giphy-source-url'),
            ],
        ];
    }

    public function renderHTML($node, $HTMLAttributes = []): array
    {
        $attributes = $node->attrs ?? (object) [];
        $id = $this->stringValue($attributes->id ?? null);
        $src = $this->stringValue($attributes->src ?? null);
        $alt = $this->stringValue($attributes->alt ?? null) ?? 'GIF';
        $username = $this->stringValue($attributes->username ?? null);
        $profileUrl = $this->stringValue($attributes->profileUrl ?? null);
        $sourceUrl = $this->stringValue($attributes->sourceUrl ?? null);
        $width = $this->intValue($attributes->width ?? null);
        $height = $this->intValue($attributes->height ?? null);

        $figure = [
            'data-giphy-id' => $id,
            'data-giphy-username' => $username,
            'data-giphy-profile-url' => $profileUrl,
            'data-giphy-source-url' => $sourceUrl,
        ];

        $html = '<figure'.$this->attributes($figure).'>';

        if (is_string($src) && str_starts_with($src, 'https://')) {
            $html .= '<img'.$this->attributes([
                'src' => $src,
                'alt' => $alt,
                'width' => $width,
                'height' => $height,
            ]).'>';
        }

        if (is_string($username)) {
            $html .= '<figcaption>'.$this->credit($username, $profileUrl, $sourceUrl).'</figcaption>';
        }

        $html .= '</figure>';

        return [
            'content' => $html,
        ];
    }

    protected function credit(string $username, ?string $profileUrl, ?string $sourceUrl): string
    {
        $href = $profileUrl ?? $sourceUrl;
        $name = $this->escape($username);

        if (! is_string($href) || ! str_starts_with($href, 'https://')) {
            return $name;
        }

        return '<a href="'.$this->escape($href).'" rel="noopener noreferrer">'.$name.'</a>';
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function attributes(array $attributes): string
    {
        $rendered = '';

        foreach ($attributes as $name => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $rendered .= ' '.$name.'="'.$this->escape((string) $value).'"';
        }

        return $rendered;
    }

    protected function attribute(DOMElement $element, string $name): ?string
    {
        if (! $element->hasAttribute($name)) {
            return null;
        }

        return $this->stringValue($element->getAttribute($name));
    }

    protected function dimension(DOMElement $element, string $name): ?int
    {
        $image = $element->getElementsByTagName('img')->item(0);

        if (! $image instanceof DOMElement) {
            return null;
        }

        return $this->intValue($image->getAttribute($name));
    }

    protected function stringValue(mixed $value): ?string
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

    protected function intValue(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    protected function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
