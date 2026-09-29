/**
 * Copyright (c) 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

'use strict';

require('./main');
var initFormattedNumber = require('ZedGuiModules/libs/formatted-number-input');
var tableAccess = require('ZedGuiModules/libs/table/table-access');

var productOrder = {};
var allProductsTable;
var productCategoryTable;

function removeActionHandler() {
    var $link = $(this);
    var tableHandler = $link.data('action') === 'select' ? allProductsTable : productCategoryTable;

    if (tableHandler) {
        tableHandler.removeSelectedProduct($link.data('id'));
    }

    return false;
}

// TODO fix later, see here: https://spryker.atlassian.net/browse/CD-446
function ProductSelector() {
    var productSelector = {};
    var selectedProducts = {};

    /**
     * @param {number} idProduct - ID of the product.
     * @param {Array} row - Row the product is shown with in the table of the selection.
     */
    productSelector.addProductToSelection = function (idProduct, row) {
        selectedProducts[idProduct] = row;
    };

    productSelector.removeProductFromSelection = function (idProduct) {
        delete selectedProducts[idProduct];
    };

    productSelector.isProductSelected = function (idProduct) {
        return selectedProducts.hasOwnProperty(idProduct);
    };

    productSelector.clearAllSelections = function () {
        selectedProducts = {};
    };

    productSelector.getSelected = function () {
        return selectedProducts;
    };

    /**
     * @returns {Array} Rows of every selected product, the table of the selection is built from them.
     */
    productSelector.getRows = function () {
        return Object.keys(selectedProducts).map(function (idProduct) {
            return selectedProducts[idProduct];
        });
    };

    return productSelector;
}

function TableHandler(sourceTable, destinationTable, checkBoxNamePrefix, labelCaption, labelId, action, formFieldId) {
    var tableHandler = {
        checkBoxNamePrefix: checkBoxNamePrefix,
        labelId: labelId,
        labelCaption: labelCaption,
        action: action,
        formFieldId: formFieldId,
        sourceTable: sourceTable,
        destinationTable: destinationTable,
    };

    var destinationTableProductSelector = new ProductSelector();
    var sourceHandle = null;
    var destinationHandle = null;

    tableHandler.selectAll = function () {
        if (!sourceHandle) {
            return;
        }

        $('input[type="checkbox"]', sourceHandle.raw().rows().nodes().toArray())
            .prop('checked', true)
            .each(function () {
                var info = JSON.parse($(this).attr('data-info'));
                tableHandler.addSelectedProduct(info.id, info.sku, info.name);
            });
    };

    tableHandler.deSelectAll = function () {
        if (!sourceHandle) {
            return;
        }

        $('input[type="checkbox"]', sourceHandle.raw().rows().nodes().toArray())
            .prop('checked', false)
            .each(function () {
                tableHandler.removeSelectedProduct(JSON.parse($(this).attr('data-info')).id);
            });
    };

    tableHandler.addSelectedProduct = function (idProduct, sku, name) {
        if (destinationTableProductSelector.isProductSelected(idProduct)) {
            return;
        }

        destinationTableProductSelector.addProductToSelection(
            idProduct,
            tableHandler.buildSelectionRow(idProduct, sku, name),
        );

        tableHandler.renderSelection();
        tableHandler.updateSelectedProductsLabelCount();
    };

    tableHandler.removeSelectedProduct = function (idProduct) {
        if (destinationTableProductSelector.isProductSelected(idProduct)) {
            destinationTableProductSelector.removeProductFromSelection(idProduct);
            tableHandler.renderSelection();
            $('#' + tableHandler.getCheckBoxNamePrefix() + idProduct).prop('checked', false);
        }

        tableHandler.updateSelectedProductsLabelCount();
    };

    /**
     * @param {number} idProduct - ID of the product.
     * @param {string} sku - SKU of the product.
     * @param {string} name - Name of the product.
     *
     * @returns {Array} Row of the table of the selection, its last cell holding the button which undoes it.
     */
    tableHandler.buildSelectionRow = function (idProduct, sku, name) {
        return [
            idProduct,
            decodeURIComponent((sku + '').replace(/\+/g, '%20')),
            decodeURIComponent((name + '').replace(/\+/g, '%20')),
            '<div><a data-id="' +
                idProduct +
                '" data-action="' +
                tableHandler.getAction() +
                '" href="#" class="btn btn-xs remove-item">Remove</a></div>',
        ];
    };

    /**
     * The table of the selection is a view over it and is rebuilt from it, so that it can be filled
     * whenever the plugin gets round to creating it: both of these tables sit in a tab which is closed
     * while the page loads, and are created only once it is opened.
     */
    tableHandler.renderSelection = function () {
        if (!destinationHandle) {
            return;
        }

        destinationHandle.raw().clear().rows.add(destinationTableProductSelector.getRows()).draw();
    };

    tableHandler.getSelector = function () {
        return destinationTableProductSelector;
    };

    tableHandler.updateSelectedProductsLabelCount = function () {
        $('#' + tableHandler.getLabelId()).text(
            labelCaption + ' (' + Object.keys(this.getSelector().getSelected()).length + ')',
        );
        var productIds = Object.keys(this.getSelector().getSelected());
        var s = productIds.join(',');
        var field = $('#' + tableHandler.getFormFieldId());
        field.attr('value', s);
    };

    tableHandler.getCheckBoxNamePrefix = function () {
        return tableHandler.checkBoxNamePrefix;
    };

    tableHandler.getLabelId = function () {
        return tableHandler.labelId;
    };

    tableHandler.getAction = function () {
        return tableHandler.action;
    };

    tableHandler.getLabelCaption = function () {
        return tableHandler.labelCaption;
    };

    tableHandler.getFormFieldId = function () {
        return tableHandler.formFieldId;
    };

    tableHandler.getSourceTable = function () {
        return tableHandler.sourceTable;
    };

    tableHandler.getDestinationTable = function () {
        return tableHandler.destinationTable;
    };

    /**
     * @returns {Object|null} Handle of the source table, absent while the plugin is still to create it.
     */
    tableHandler.getSourceHandle = function () {
        return sourceHandle;
    };

    tableAccess.requestTable(sourceTable[0], function (handle) {
        sourceHandle = handle;
    });

    tableAccess.requestTable(destinationTable[0], function (handle) {
        destinationHandle = handle;

        handle.created().then(function () {
            tableHandler.renderSelection();
        });
    });

    return tableHandler;
}

$(document).ready(function () {
    var $allProducts = $('#product-table');
    var $productCategory = $('#product-category-table');

    if (!$allProducts.length) {
        return;
    }

    allProductsTable = new TableHandler(
        $allProducts,
        $('#selectedProductsTable'),
        'all_products_checkbox_',
        'Products to be assigned',
        'assigned-tab-label',
        'select',
        'assign_form_products_to_be_assigned',
    );

    $('#selectedProductsTable, #deselectedProductsTable').on('click', '.remove-item', removeActionHandler);

    $allProducts.on('change', '.all-products-checkbox', function () {
        var $checkbox = $(this);
        var info = $.parseJSON($checkbox.attr('data-info'));

        if ($checkbox.prop('checked')) {
            allProductsTable.addSelectedProduct(info.id, info.sku, info.name);

            return;
        }

        allProductsTable.removeSelectedProduct(info.id);
    });

    tableAccess.requestTable($allProducts[0], function (handle) {
        handle.on('draw', function () {
            var selector = allProductsTable.getSelector();

            handle
                .raw()
                .rows()
                .data()
                .each(function (data) {
                    var idProduct = parseInt(data[0]);

                    if (selector.isProductSelected(idProduct)) {
                        $('#' + allProductsTable.getCheckBoxNamePrefix() + idProduct).prop('checked', true);
                    }
                });
        });
    });

    $('.prcat-select-all a').on('click', function () {
        allProductsTable.selectAll();

        return false;
    });

    if (!$productCategory.length) {
        return;
    }

    productCategoryTable = new TableHandler(
        $productCategory,
        $('#deselectedProductsTable'),
        'product_category_checkbox_',
        'Products to be deassigned',
        'deassigned-tab-label',
        'deselect',
        'assign_form_products_to_be_de_assigned',
    );

    /**
     * Deassignment is the mirror image of assignment: every product in the category starts out checked,
     * so clearing a checkbox stages a removal rather than undoing a selection.
     */
    productCategoryTable.deSelectAll = function () {
        var sourceHandle = productCategoryTable.getSourceHandle();

        if (!sourceHandle) {
            return;
        }

        $('input[type="checkbox"]', sourceHandle.raw().rows().nodes().toArray())
            .prop('checked', false)
            .each(function () {
                var info = JSON.parse($(this).attr('data-info'));
                productCategoryTable.addSelectedProduct(info.id, info.sku, info.name);
            });
    };

    productCategoryTable.removeSelectedProduct = function (idProduct) {
        var selector = productCategoryTable.getSelector();

        if (selector.isProductSelected(idProduct)) {
            selector.removeProductFromSelection(idProduct);
            productCategoryTable.renderSelection();
            $('#' + productCategoryTable.getCheckBoxNamePrefix() + idProduct).prop('checked', true);
        }

        productCategoryTable.updateSelectedProductsLabelCount();
    };

    $productCategory.on('change', '.product_category_checkbox', function () {
        var $checkbox = $(this);
        var info = $.parseJSON($checkbox.attr('data-info'));

        if ($checkbox.prop('checked')) {
            productCategoryTable.removeSelectedProduct(info.id);
            allProductsTable.removeSelectedProduct(info.id);

            return;
        }

        productCategoryTable.addSelectedProduct(info.id, info.sku, info.name);
    });

    $productCategory.on('change', '.product_category_order', function () {
        var $input = $(this);
        var info = $.parseJSON($input.attr('data-info'));
        var unformattedInputClassName = $input.attr('data-target');

        if (unformattedInputClassName) {
            productOrder[info.id] = $('.' + unformattedInputClassName).val();
        } else {
            productOrder[info.id] = $input.val();
        }

        $('#assign_form_product_order').attr('value', JSON.stringify(productOrder));
    });

    tableAccess.requestTable($productCategory[0], function (handle) {
        handle.on('draw', function () {
            var selector = productCategoryTable.getSelector();

            initFormattedNumber($productCategory[0]);

            handle
                .raw()
                .rows()
                .data()
                .each(function (data) {
                    var idProduct = parseInt(data[0]);

                    if (selector.isProductSelected(idProduct)) {
                        $('#' + productCategoryTable.getCheckBoxNamePrefix() + idProduct).prop('checked', false);
                    }

                    if (productOrder.hasOwnProperty(idProduct)) {
                        $('#product_category_order_' + idProduct).val(parseInt(productOrder[idProduct]) || 0);
                    }
                });
        });
    });

    $('.prcat-deselect-all a').on('click', function () {
        productCategoryTable.deSelectAll();

        return false;
    });
});
