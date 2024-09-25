<?php

declare(strict_types=1);

namespace Alfresco\Render;

use Alfresco\Contracts\Slotable;
use RuntimeException;
use Stringable;

class HtmlTag implements Slotable
{
    /**
     * Create a new instance.
     *
     * @param  array<string, string|bool|list<string>>  $attributes
     */
    public function __construct(
        protected string $as,
        protected array $attributes,
        protected string|Stringable $before,
        protected string|Stringable $after,
        protected ?Slotable $slot,
    ) {
        //
    }

    /**
     * Retrieve the "before" content.
     */
    public function before(): string
    {
        if ($this->isVoidTag()) {
            return "<{$this->as}{$this->attributeList()}>";
        }

        return "<{$this->as}{$this->attributeList()}>{$this->before}{$this->slot?->before()}";
    }

    /**
     * Retrieve the "after" content.
     */
    public function after(): string
    {
        if ($this->isVoidTag()) {
            return '';
        }

        return "{$this->slot?->after()}{$this->after}</{$this->as}>";
    }

    /**
     * Retrieve the tags attribute list.
     */
    protected function attributeList(): string
    {
        return with($this->attributes(), fn (array $attributes) => $attributes === []
            ? ''
            : ' '.implode(' ', $attributes));
    }

    /**
     * Retrieve the tags attributes.
     *
     * @return list<string>
     */
    protected function attributes(): array
    {
        $attributes = array_map(function (string|array|bool $value, string $key) {
            if ($value === false) {
                return false;
            }

            $key = trim($key);

            if ($value === true) {
                return $key;
            }

            if (! is_array($value)) {
                $value = explode(' ', $value);
            }

            $value = array_map(trim(...), $value);

            return $key.'="'.implode(' ', $value).'"';
        }, $this->attributes, array_keys($this->attributes));

        $attributes = array_filter($attributes, fn (string|false $value) => $value !== false);

        return array_values($attributes);
    }

    /**
     * Attach the given attributes.
     *
     * @param  array<string, string|bool|list<string>>  $attributes
     */
    public function withAttributes(array $attributes): HtmlTag
    {
        return new HtmlTag(
            as: $this->as,
            attributes: array_merge_recursive($this->attributes, $attributes),
            before: $this->before,
            after: $this->after,
            slot: $this->slot,
        );
    }

    /**
     * Wrap the given slot.
     */
    public function wrapSlot(Slotable $slot): HtmlTag
    {
        return new HtmlTag(
            as: $this->as,
            attributes: $this->attributes,
            before: $this->before,
            after: $this->after,
            slot: $slot,
        );
    }

    /**
     * Change the HTML tag.
     */
    public function as(string $as): HtmlTag
    {
        return new HtmlTag(
            as: $as,
            attributes: $this->attributes,
            before: $this->before,
            after: $this->after,
            slot: $this->slot,
        );
    }

    /**
     * Convert to a string.
     */
    public function toString(): string
    {
        if ($this->slot !== null) {
            throw new RuntimeException('Unable to render a tag with a content wrapper.');
        }

        return $this->before().$this->after();
    }

    /**
     * Convert to a string.
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Determine if the tag is considered a void tag that does not need closing.
     */
    protected function isVoidTag(): bool
    {
        return in_array($this->as, [
            'area',
            'base',
            'br',
            'col',
            'embed',
            'hr',
            'img',
            'input',
            'link',
            'meta',
            'source',
            'track',
            'wbr',
        ], true);
    }
}