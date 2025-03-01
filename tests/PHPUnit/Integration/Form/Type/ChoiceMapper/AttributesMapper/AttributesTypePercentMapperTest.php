<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace PHPUnit\Integration\Form\Type\ChoiceMapper\AttributesMapper;

use BitBag\SyliusElasticsearchPlugin\Form\Type\ChoiceMapper\AttributesMapper\AttributesTypeDateMapper;
use BitBag\SyliusElasticsearchPlugin\Form\Type\ChoiceMapper\AttributesMapper\AttributesTypePercentMapper;
use BitBag\SyliusElasticsearchPlugin\Repository\ProductAttributeValueRepository;
use BitBag\SyliusElasticsearchPlugin\Repository\TaxonRepository;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\AttributeRepository;
use Tests\BitBag\SyliusElasticsearchPlugin\Integration\IntegrationTestCase;

class AttributesTypePercentMapperTest extends IntegrationTestCase
{
    /** @var ProductAttributeValueRepository */
    private $productAttributeValueRepository;

    /** @var AttributeRepository */
    private $attributeRepository;

    /** @var TaxonRepository */
    private $taxonRepository;

    /** @var AttributesTypePercentMapper */
    private $attributesType;

    public function SetUp(): void
    {
        parent::SetUp();

        $this->productAttributeValueRepository = self::$container->get('bitbag.sylius_elasticsearch_plugin.repository.product_attribute_value_repository');
        $this->attributeRepository = self::$container->get('sylius.repository.product_attribute');
        $this->taxonRepository = self::$container->get('sylius.repository.taxon');
        $this->attributesType = self::$container->get('bitbag_sylius_elasticsearch_plugin.form.mapper.type.percent');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        self::ensureKernelShutdown();
    }

    public function test_percent_mapper(): void
    {
        $this->loadFixturesFromFiles(['Type/ChoiceMapper/AttributesMapper/test_attributes_type_percent_mapper.yaml']);

        $attribute = $this->attributeRepository->findAll()[0];
        $taxon = $this->taxonRepository->findAll()[0];

        $uniqueAttributeValues = $this->productAttributeValueRepository->getUniqueAttributeValues($attribute, $taxon);

        $result = $this->attributesType->map($uniqueAttributeValues);

        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);
    }
}
