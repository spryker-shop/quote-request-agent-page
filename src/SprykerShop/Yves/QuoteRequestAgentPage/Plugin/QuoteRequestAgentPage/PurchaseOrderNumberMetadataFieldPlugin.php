<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\QuoteRequestAgentPage\Plugin\QuoteRequestAgentPage;

use Spryker\Yves\Kernel\AbstractPlugin;
use SprykerShop\Yves\QuoteRequestAgentPageExtension\Dependency\Plugin\QuoteRequestAgentFormMetadataFieldPluginInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

/**
 * @method \SprykerShop\Yves\QuoteRequestAgentPage\QuoteRequestAgentPageFactory getFactory()
 */
class PurchaseOrderNumberMetadataFieldPlugin extends AbstractPlugin implements QuoteRequestAgentFormMetadataFieldPluginInterface
{
    /**
     * @uses \Spryker\Zed\QuoteRequest\Business\Validator\QuoteRequestMetadataValidator::KEY_PURCHASE_ORDER_NUMBER
     *
     * @var string
     */
    protected const FIELD_METADATA_PURCHASE_ORDER_NUMBER = 'purchase_order_number';

    /**
     * @var string
     */
    protected const LABEL_METADATA_PURCHASE_ORDER_NUMBER = 'quote_request_page.quote_request.metadata.label.purchase_order_number';

    /**
     * @var int
     */
    protected const MAX_LENGTH_NUMBER = 128;

    /**
     * {@inheritDoc}
     * - Adds purchase order number to metadata for QuoteRequestAgent form.
     *
     * @api
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    public function buildForm(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        $builder->add(static::FIELD_METADATA_PURCHASE_ORDER_NUMBER, TextType::class, [
            'label' => static::LABEL_METADATA_PURCHASE_ORDER_NUMBER,
            'required' => false,
            'constraints' => [
                new Length([
                    'max' => static::MAX_LENGTH_NUMBER,
                ]),
            ],
        ]);

        return $builder;
    }
}
