<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Communication\Form\Expander;

use Generated\Shared\Transfer\ProductAbstractTransfer;
use Spryker\Zed\Gui\Communication\Form\Type\Select2ComboBoxType;
use Spryker\Zed\ProductCategory\Communication\Form\DataProvider\ProductCategoryAbstractFormDataProviderInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ProductCategoryIdsProductFormExpander implements ProductCategoryIdsProductFormExpanderInterface
{
    protected const string KEY_ID_PRODUCT_ABSTRACT = 'id_product_abstract';

    public function __construct(
        protected readonly ProductCategoryAbstractFormDataProviderInterface $productCategoryAbstractFormDataProvider,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function expand(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        $this->addCategoryIdsField($builder);

        return $builder;
    }

    protected function addCategoryIdsField(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builder->add(ProductAbstractTransfer::CATEGORY_IDS, Select2ComboBoxType::class, [
            'label' => 'categories',
            'placeholder' => 'select-category',
            'multiple' => true,
            'required' => false,
            'empty_data' => [],
            'choices' => $this->productCategoryAbstractFormDataProvider->getOptions(),
        ]);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event): void {
                $data = $event->getData();

                if (!$data || !isset($data[static::KEY_ID_PRODUCT_ABSTRACT])) {
                    return;
                }

                $data[ProductAbstractTransfer::CATEGORY_IDS] = $this->productCategoryAbstractFormDataProvider
                    ->getData($data[static::KEY_ID_PRODUCT_ABSTRACT]);
                $event->setData($data);
            },
        );

        return $builder;
    }
}
