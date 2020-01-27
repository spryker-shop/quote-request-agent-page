<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\QuoteRequestAgentPage\Extractor;

interface ShipmentCostExtractorInterface
{
    /**
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer[] $shipmentGroupTransfers
     *
     * @return int
     */
    public function extractTotalShipmentCosts(array $shipmentGroupTransfers): int;
}