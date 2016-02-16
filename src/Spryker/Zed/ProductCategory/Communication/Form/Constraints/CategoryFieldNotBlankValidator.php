<?php

/**
 * (c) Spryker Systems GmbH copyright protected.
 */

namespace Spryker\Zed\ProductCategory\Communication\Form\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlankValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CategoryFieldNotBlankValidator extends NotBlankValidator
{

    /**
     * @param string $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     *
     * @throws \Symfony\Component\Validator\Exception\UnexpectedTypeException
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        if (!($constraint instanceof CategoryFieldNotBlank)) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\Category');
        }

        if ($this->shouldValidateCategory($constraint)) {
            parent::validate($value, $constraint);
        }
    }

    /**
     * @param \Spryker\Zed\ProductCategory\Communication\Form\Constraints\CategoryFieldNotBlank $constraint
     *
     * @return bool
     */
    protected function shouldValidateCategory(CategoryFieldNotBlank $constraint)
    {
        $isNotCategorySelected = !$this->isCategorySelected($constraint->getCategoryFieldName());
        $isNotSubcategoryChecked = !$this->isSubcategoriesChecked($constraint->getCheckboxFieldName());

        return $isNotSubcategoryChecked && $isNotCategorySelected;
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    protected function isCategorySelected($fieldName)
    {
        return $this->context->getRoot()
            ->get($fieldName)
            ->getData() !== null;
    }

    /**
     * @param string $checkboxName
     *
     * @return bool
     */
    protected function isSubcategoriesChecked($checkboxName)
    {
        return (bool)$this->context->getRoot()
            ->get($checkboxName)
            ->getData();
    }

}
