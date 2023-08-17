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

namespace Neklo\ProductPosition\Model\Indexer\Stock\Plugin;

class Import extends \Magento\CatalogImportExport\Model\Indexer\Stock\Plugin\Import
{
    /**{@inheritdoc} */
    public function afterImportSource(\Magento\ImportExport\Model\Import $subject, $import)
    {
        if (!$this->_stockndexerProcessor->isIndexerScheduled()) {
            $this->_stockndexerProcessor->reindexAll();
        }
        return $import;
    }
}
