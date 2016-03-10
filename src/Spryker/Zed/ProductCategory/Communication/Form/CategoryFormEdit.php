<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Communication\Form;

use Spryker\Zed\Gui\Communication\Form\Type\Select2ComboBoxType;
use Symfony\Component\Form\FormBuilderInterface;

class CategoryFormEdit extends CategoryFormAdd
{

    const FIELD_META_TITLE = 'meta_title';
    const FIELD_META_DESCRIPTION = 'meta_description';
    const FIELD_META_KEYWORDS = 'meta_keywords';
    const FIELD_CATEGORY_IMAGE_NAME = 'category_image_name';
    const FIELD_CATEGORY_ROBOTS = 'robots';
    const FIELD_CATEGORY_CANONICAL = 'canonical';
    const FIELD_CATEGORY_ALTERNATE_TAG = 'alternate_tag';

    const CATEGORY_IS_ACTIVE = 'is_active';
    const CATEGORY_IS_IN_MENU = 'is_in_menu';
    const CATEGORY_IS_CLICKABLE = 'is_clickable';
    const CATEGORY_NODE_IS_MAIN = 'is_main';

    const EXTRA_PARENTS = 'extra_parents';

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this
            ->addNameField($builder)
            ->addCategoryKeyField($builder)
            ->addMetaTitleField($builder)
            ->addMetaDescriptionField($builder)
            ->addMetaKeywordsField($builder)
            ->addCategoryIsActiveField($builder)
            ->addCategoryIsInMenuField($builder)
            ->addCategoryIsClickableField($builder)
            ->addCategoryNodeField($builder, $options[self::OPTION_PARENT_CATEGORY_NODE_CHOICES])
            ->addExtraParentsField($builder, $options[self::OPTION_PARENT_CATEGORY_NODE_CHOICES])
            ->addPkCategoryNodeField($builder)
            ->addFkNodeCategoryField($builder)
            ->addProductsToBeAssignedField($builder)
            ->addProductsToBeDeassignedField($builder)
            ->addProductsOrderField($builder)
            ->addProductCategoryPreconfigField($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addMetaTitleField(FormBuilderInterface $builder)
    {
        $builder
            ->add(self::FIELD_META_TITLE, 'text', [
                'label' => 'Meta Title',
            ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addMetaDescriptionField(FormBuilderInterface $builder)
    {
        $builder
            ->add(self::FIELD_META_DESCRIPTION, 'textarea', [
                'label' => 'Meta Description',
            ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addMetaKeywordsField(FormBuilderInterface $builder)
    {
        $builder
            ->add(self::FIELD_META_KEYWORDS, 'textarea', [
                'label' => 'Meta Keywords',
            ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addCategoryIsActiveField(FormBuilderInterface $builder)
    {
        $builder
            ->add(self::CATEGORY_IS_ACTIVE, 'checkbox', [
                'label' => 'Active',
            ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addCategoryIsInMenuField(FormBuilderInterface $builder)
    {
        $builder
            ->add(self::CATEGORY_IS_IN_MENU, 'checkbox', [
                'label' => 'Show in Menu',
            ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addCategoryIsClickableField(FormBuilderInterface $builder)
    {
        $builder
            ->add(self::CATEGORY_IS_CLICKABLE, 'checkbox', [
                'label' => 'Clickable',
            ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $choices
     *
     * @return $this
     */
    protected function addExtraParentsField(FormBuilderInterface $builder, array $choices)
    {
        $builder
            ->add(self::EXTRA_PARENTS, new Select2ComboBoxType(), [
                'label' => 'Additional Parents',
                'choices' => $choices,
                'multiple' => true
            ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addFkNodeCategoryField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_FK_NODE_CATEGORY, 'hidden');

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addProductsToBeAssignedField(FormBuilderInterface $builder)
    {
        $builder
            ->add('products_to_be_assigned', 'hidden', [
                'attr' => [
                    'id' => 'products_to_be_assigned',
                ],
            ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addProductsToBeDeassignedField(FormBuilderInterface $builder)
    {
        $builder
            ->add('products_to_be_de_assigned', 'hidden', [
                'attr' => [
                    'id' => 'products_to_be_de_assigned',
                ],
            ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addProductsOrderField(FormBuilderInterface $builder)
    {
        $builder
            ->add('product_order', 'hidden', [
                'attr' => [
                    'id' => 'product_order',
                ],
            ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addProductCategoryPreconfigField(FormBuilderInterface $builder)
    {
        $builder
            ->add('product_category_preconfig', 'hidden', [
                'attr' => [
                    'id' => 'product_category_preconfig',
                ],
            ]);

        return $this;
    }

}
