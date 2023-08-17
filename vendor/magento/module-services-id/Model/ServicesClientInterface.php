<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ServicesId\Model;

/**
 * Interface for SaaS service calls
 *
 * @api
 */
interface ServicesClientInterface
{
    /**
     * Execute call to SaaS service
     *
     * @param string $method
     * @param string $uri
     * @param string|null $data
     * @param array $headers
     * @param string|null $environmentOverride
     * @return array
     */
    public function request(
        string $method,
        string $uri,
        ?string $data = null,
        array $headers = [],
        ?string $environmentOverride = null
    ) : array;
}
