<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductCategory\Api\Backend\Exception;

use Spryker\ApiPlatform\Exception\GlueApiException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryProductsExceptionFactory
{
    protected const string MESSAGE_CATEGORY_NOT_FOUND = 'Category not found.';

    protected const string MESSAGE_PRODUCT_ASSIGNMENT_NOT_FOUND = 'Product "%s" is unknown or not assigned to category "%s".';

    protected const string ERROR_CODE_INVALID_ASSIGN_REQUEST = '400';

    protected const string MESSAGE_INVALID_ASSIGN_REQUEST = 'Provide a non-empty "skus" array in the request body.';

    protected const string ERROR_CODE_INVALID_UNASSIGN_REQUEST = '400';

    protected const string MESSAGE_INVALID_UNASSIGN_REQUEST = 'Provide either a non-empty "skus" array or "all": true in the request body — exactly one of the two.';

    protected const string ERROR_CODE_UNKNOWN_SKU = '422';

    protected const string MESSAGE_UNKNOWN_SKU = 'SKU "%s" is unknown. Nothing was assigned.';

    protected const string ERROR_CODE_UNASSIGNABLE_SKU = '422';

    protected const string MESSAGE_UNASSIGNABLE_SKU = 'SKU "%s" is unknown or not assigned to category "%s". Nothing was removed.';

    public function createCategoryNotFoundException(): NotFoundHttpException
    {
        return new NotFoundHttpException(static::MESSAGE_CATEGORY_NOT_FOUND);
    }

    public function createProductAssignmentNotFoundException(string $categoryKey, string $sku): NotFoundHttpException
    {
        return new NotFoundHttpException(
            sprintf(static::MESSAGE_PRODUCT_ASSIGNMENT_NOT_FOUND, $sku, $categoryKey),
        );
    }

    public function createInvalidAssignRequestException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            static::ERROR_CODE_INVALID_ASSIGN_REQUEST,
            static::MESSAGE_INVALID_ASSIGN_REQUEST,
        );
    }

    public function createInvalidUnassignRequestException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            static::ERROR_CODE_INVALID_UNASSIGN_REQUEST,
            static::MESSAGE_INVALID_UNASSIGN_REQUEST,
        );
    }

    /**
     * @param array<string> $unknownSkus
     */
    public function createUnknownSkusException(array $unknownSkus): GlueApiException
    {
        $errorMessages = [];
        foreach ($unknownSkus as $unknownSku) {
            $errorMessages[] = sprintf(static::MESSAGE_UNKNOWN_SKU, $unknownSku);
        }

        return $this->createUnprocessableEntityException(static::ERROR_CODE_UNKNOWN_SKU, $errorMessages);
    }

    /**
     * @param array<string> $offendingSkus
     */
    public function createUnassignableSkusException(string $categoryKey, array $offendingSkus): GlueApiException
    {
        $errorMessages = [];
        foreach ($offendingSkus as $offendingSku) {
            $errorMessages[] = sprintf(static::MESSAGE_UNASSIGNABLE_SKU, $offendingSku, $categoryKey);
        }

        return $this->createUnprocessableEntityException(static::ERROR_CODE_UNASSIGNABLE_SKU, $errorMessages);
    }

    /**
     * @param array<string> $errorMessages
     */
    protected function createUnprocessableEntityException(string $errorCode, array $errorMessages): GlueApiException
    {
        $errors = [];
        foreach ($errorMessages as $errorMessage) {
            $errors[] = [
                'code' => $errorCode,
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'detail' => $errorMessage,
            ];
        }

        return (new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $errorCode,
            $errorMessages[0] ?? '',
        ))->setErrors($errors);
    }
}
