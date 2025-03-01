<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusElasticsearchPlugin\PropertyBuilder;

use BitBag\SyliusElasticsearchPlugin\Formatter\StringFormatterInterface;
use BitBag\SyliusElasticsearchPlugin\PropertyNameResolver\ConcatedNameResolverInterface;
use Elastica\Document;
use FOS\ElasticaBundle\Event\PostTransformEvent;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductAttributeValue;

final class AttributeBuilder extends AbstractBuilder
{
    /** @var ConcatedNameResolverInterface */
    private $attributeNameResolver;

    /** @var StringFormatterInterface */
    private $stringFormatter;

    public function __construct(
        ConcatedNameResolverInterface $attributeNameResolver,
        StringFormatterInterface $stringFormatter
    ) {
        $this->attributeNameResolver = $attributeNameResolver;
        $this->stringFormatter = $stringFormatter;
    }

    public function consumeEvent(PostTransformEvent $event): void
    {
        $this->buildProperty(
            $event,
            ProductInterface::class,
            function (ProductInterface $product, Document $document): void {
                $this->resolveProductAttributes($product, $document);
            }
        );
    }

    private function resolveProductAttributes(ProductInterface $product, Document $document): void
    {
        foreach ($product->getAttributes() as $productAttribute) {
            $attribute = $productAttribute->getAttribute();
            if (!$attribute) {
                continue;
            }

            $this->processAttribute($attribute, $productAttribute, $document);
        }
    }

    private function resolveProductAttribute(
        array $attributeConfiguration,
        $attributeValue,
        ProductAttributeValue $productAttribute
    ): array {
        if ('select' === $productAttribute->getAttribute()->getType()) {
            $choices = $attributeConfiguration['choices'];
            if (is_array($attributeValue)) {
                foreach ($attributeValue as $i => $item) {
                    $attributeValue[$i] = $choices[$item][$productAttribute->getLocaleCode()] ?? $item;
                }
            } else {
                $attributeValue = $choices[$attributeValue][$productAttribute->getLocaleCode()] ?? $attributeValue;
            }
        }

        $attributes = [];
        if (is_array($attributeValue)) {
            foreach ($attributeValue as $singleElement) {
                $attributes[] = $this->stringFormatter->formatToLowercaseWithoutSpaces((string) $singleElement);
            }
        } else {
            $attributeValue = is_string($attributeValue) ? $this->stringFormatter->formatToLowercaseWithoutSpaces($attributeValue) : $attributeValue;
            $attributes[] = $attributeValue;
        }

        return $attributes;
    }

    private function processAttribute(
        AttributeInterface $attribute,
        ProductAttributeValue $productAttribute,
        Document $document
    ): void {
        $attributeCode = $attribute->getCode();
        $attributeConfiguration = $attribute->getConfiguration();

        $value = $productAttribute->getValue();
        $documentKey = $this->attributeNameResolver->resolvePropertyName($attributeCode);
        $code = \sprintf('%s_%s', $documentKey, $productAttribute->getLocaleCode());

        $values = $this->resolveProductAttribute(
            $attributeConfiguration,
            $value,
            $productAttribute
        );

        $document->set($code, $values);
    }
}
