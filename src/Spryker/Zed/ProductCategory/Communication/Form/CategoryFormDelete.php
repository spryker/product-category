<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\ProductCategory\Communication\Form;

use Spryker\Zed\ProductCategory\Communication\Form\Constraints\CategoryFieldNotBlank;

class CategoryFormDelete extends CategoryFormEdit
{

    const DELETE_CHILDREN = 'delete_children';

    /**
     * @return self
     */
    protected function buildFormFields()
    {
        return $this->addCheckbox(self::DELETE_CHILDREN, [
                'label' => 'Delete subcategories',
            ])
            ->addSelect2ComboBox(self::FK_PARENT_CATEGORY_NODE, [
                'label' => 'Or move them to category',
                'choices' => $this->getCategoriesWithPaths($this->locale->getIdLocale()),
                'multiple' => false,
                'constraints' => [
                    new CategoryFieldNotBlank([
                        'categoryFieldName' => self::FK_PARENT_CATEGORY_NODE,
                        'checkboxFieldName' => self::DELETE_CHILDREN,
                    ]),
                ],
            ])
            ->addHidden(self::PK_CATEGORY_NODE)
            ->addHidden(self::FK_NODE_CATEGORY);
    }

    /**
     * @return array
     */
    public function populateFormFields()
    {
        $fields = parent::populateFormFields();
        $fields[self::FK_PARENT_CATEGORY_NODE] = null;

        return $fields;
    }

}
