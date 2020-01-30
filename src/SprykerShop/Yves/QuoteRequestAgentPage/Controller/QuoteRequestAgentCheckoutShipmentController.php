<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\QuoteRequestAgentPage\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerShop\Yves\QuoteRequestAgentPage\QuoteRequestAgentPageFactory getFactory()
 */
class QuoteRequestAgentCheckoutShipmentController extends QuoteRequestAgentAbstractController
{
    /**
     * @uses \SprykerShop\Yves\CheckoutPage\Plugin\Router\CheckoutPageRouteProviderPlugin::CHECKOUT_SHIPMENT
     */
    protected const ROUTE_CHECKOUT_SHIPMENT = 'checkout-shipment';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $quoteRequestReference
     *
     * @return \Spryker\Yves\Kernel\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function executeIndexAction(Request $request, string $quoteRequestReference)
    {
        $quoteTransfer = $this->getFactory()->getQuoteClient()->getQuote();

        $quoteRequestTransfer = $this->getQuoteRequestByReference($quoteRequestReference);

        return $this->getFactory()
            ->createCompanyUserImpersonator()
            ->redirectImpersonatedUserWithPreparedQuoteAndMessage($quoteRequestTransfer, $quoteTransfer, static::ROUTE_CHECKOUT_SHIPMENT);
    }
}
