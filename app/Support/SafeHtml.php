<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Cleans HTML that comes from an outside API so it is safe to print with {!! !!}.
 *
 * The HTML is parsed into a DOM, so broken or unclosed tags can never leak out
 * and break the page layout. Only basic text tags are kept and every attribute
 * is dropped (no links, scripts, styles or event handlers).
 */
class SafeHtml
{
    private const INLINE_TAGS = ['strong', 'b', 'em', 'i', 'u'];

    private const BLOCK_TAGS = ['p', 'ul', 'ol', 'li', 'h4'];

    private const DROP_TAGS = ['script', 'style', 'iframe', 'object', 'embed', 'noscript', 'template', 'svg', 'form', 'img', 'video', 'audio'];

    public static function clean(?string $html): string
    {
        $html = str_ireplace(['&nbsp;', "\u{A0}"], ' ', (string) $html);

        if (trim(strip_tags($html)) === '' || in_array(trim(strip_tags($html)), ['-', '–', '—'], true)) {
            return '';
        }

        // Plain text: escape it and keep the line breaks.
        if ($html === strip_tags($html)) {
            return nl2br(e(trim($html), false));
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8"?><div id="safe-html-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('safe-html-root');

        return $root ? trim(self::renderChildren($root)) : '';
    }

    public static function text(?string $html): string
    {
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', str_replace("\u{A0}", ' ', $text)) ?? '');
    }

    private static function renderChildren(DOMNode $parent): string
    {
        $output = '';
        $orphanItems = '';
        $parentTag = $parent instanceof DOMElement ? strtolower($parent->tagName) : '';
        $insideList = in_array($parentTag, ['ul', 'ol'], true);

        foreach ($parent->childNodes as $child) {
            $isListItem = $child instanceof DOMElement && strtolower($child->tagName) === 'li';
            $isBlankText = $child->nodeType === XML_TEXT_NODE && trim($child->textContent) === '';

            // Collect <li> that have no <ul>/<ol> around them and wrap them in one <ul>.
            if (! $insideList && $isListItem) {
                $orphanItems .= self::renderNode($child);

                continue;
            }

            if ($orphanItems !== '' && ! $isBlankText) {
                $output .= '<ul>'.$orphanItems.'</ul>';
                $orphanItems = '';
            }

            $output .= self::renderNode($child);
        }

        if ($orphanItems !== '') {
            $output .= '<ul>'.$orphanItems.'</ul>';
        }

        return $output;
    }

    private static function renderNode(DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE || $node->nodeType === XML_CDATA_SECTION_NODE) {
            return e($node->textContent);
        }

        if (! $node instanceof DOMElement) {
            return '';
        }

        $tag = strtolower($node->tagName);

        if (in_array($tag, self::DROP_TAGS, true)) {
            return '';
        }

        if ($tag === 'br') {
            return '<br>';
        }

        $inner = self::renderChildren($node);

        if (preg_match('/^h[1-6]$/', $tag)) {
            // A heading that wraps whole paragraphs or lists is just a wrapper: unwrap it.
            return self::hasBlockContent($inner) ? $inner : self::wrap('h4', $inner);
        }

        if ($tag === 'p') {
            return self::hasBlockContent($inner) ? $inner : self::wrap('p', $inner);
        }

        if (in_array($tag, ['ul', 'ol', 'li'], true) || in_array($tag, self::INLINE_TAGS, true)) {
            return self::wrap($tag, $inner);
        }

        // div, span, a, font, section, table parts ... keep only their content.
        return $inner;
    }

    private static function wrap(string $tag, string $inner): string
    {
        if (trim(str_replace('<br>', '', $inner)) === '') {
            return '';
        }

        return '<'.$tag.'>'.$inner.'</'.$tag.'>';
    }

    private static function hasBlockContent(string $html): bool
    {
        return (bool) preg_match('#<('.implode('|', self::BLOCK_TAGS).')>#', $html);
    }
}
