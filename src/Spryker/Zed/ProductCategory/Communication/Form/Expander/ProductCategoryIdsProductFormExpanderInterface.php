<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Communication\Form\Expander;

use Symfony\Component\Form\FormBuilderInterface;

interface ProductCategoryIdsProductFormExpanderInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function expand(FormBuilderInterface $builder, array $options): FormBuilderInterface;
}
