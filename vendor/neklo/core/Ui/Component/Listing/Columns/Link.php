<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the NekloEULA that is bundled with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt
 *
 * Copyright (c)  Neklo (http://store.neklo.com/)
 */
declare(strict_types=1);

namespace Neklo\Core\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class Link extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (!empty($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item = $this->prepareDataItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Prepare Data Item
     *
     * @param array $item
     *
     * @return array
     */
    private function prepareDataItem(array $item): array
    {
        $indexField = $this->getData('config/linkId');
        $linkField = $this->getData('config/link');
        $linkTemplate = $this->getData('config/linkTemplate');
        $linkPlaceholder = $this->getData('config/linkPlaceholder');
        if (isset($item[$indexField], $linkField, $linkTemplate)) {
            $item[$linkField] = $this->context->getUrl(sprintf($linkTemplate, $item[$indexField]));
        } elseif ($linkPlaceholder) {
            $item[$this->getName()] = $linkPlaceholder;
        }

        return $item;
    }
}
