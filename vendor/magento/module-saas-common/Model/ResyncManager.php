<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SaaSCommon\Model;

use Exception;
use Magento\DataExporter\Model\FeedInterface;
use Magento\DataExporter\Model\Indexer\FeedIndexer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\FlagManager;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\SaaSCommon\Cron\SubmitFeedInterface;
use Magento\SaaSCommon\Model\Exception\UnableSendData;
use Magento\Framework\Indexer\ActionInterface as IndexerActionFeed;

/**
 * Manager for SaaS feed re-sync functions
 *
 * @api
 */
class ResyncManager
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var SubmitFeedInterface
     */
    private $submitFeed;

    /**
     * @var FeedIndexer
     */
    private $feedIndexer;

    /**
     * @var FeedInterface
     */
    private $feedInterface;

    /**
     * @var string
     */
    private $flagName;

    /**
     * @var string
     */
    private $indexerName;

    /**
     * @var string
     */
    private $registryTableName;

    /**
     * @param IndexerActionFeed  $feedIndexer
     * @param FlagManager $flagManager
     * @param IndexerRegistry $indexerRegistry
     * @param SubmitFeedInterface $submitFeed
     * @param ResourceConnection $resourceConnection
     * @param FeedInterface $feedInterface
     * @param string $flagName
     * @param string $indexerName
     * @param string $registryTableName
     */
    public function __construct(
        IndexerActionFeed $feedIndexer,
        FlagManager $flagManager,
        IndexerRegistry $indexerRegistry,
        SubmitFeedInterface $submitFeed,
        ResourceConnection $resourceConnection,
        FeedInterface $feedInterface,
        string $flagName,
        string $indexerName,
        string $registryTableName
    ) {
        $this->feedIndexer = $feedIndexer;
        $this->flagManager = $flagManager;
        $this->indexerRegistry = $indexerRegistry;
        $this->submitFeed = $submitFeed;
        $this->resourceConnection = $resourceConnection;
        $this->feedInterface = $feedInterface;
        $this->flagName = $flagName;
        $this->indexerName = $indexerName;
        $this->registryTableName = $registryTableName;
    }

    /**
     * Execute full SaaS feed data re-generate and re-submit
     *
     * @throws \Zend_Db_Statement_Exception
     * @throws UnableSendData
     */
    public function executeFullResync(): void
    {
        $this->resetIndexedData();
        $this->resetSubmittedData();
        $this->regenerateFeedData();
        $this->submitAllToFeed();
    }

    /**
     * Execute SaaS feed data re-submit only
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function executeResubmitOnly(): void
    {
        $this->resetSubmittedData();
        $this->submitAllToFeed();
    }

    /**
     * Reset SaaS indexed feed data in order to re-generate
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function resetIndexedData(): void
    {
        $indexer = $this->indexerRegistry->get($this->indexerName);
        $indexer->invalidate();
    }

    /**
     * Reset SaaS submitted feed data in order to re-send
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function resetSubmittedData(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $registryTable = $this->resourceConnection->getTableName($this->registryTableName);
        $connection->truncateTable($registryTable);
        $this->flagManager->deleteFlag($this->flagName);
    }

    /**
     * Re-index SaaS feed data
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function regenerateFeedData(): void
    {
        $indexer = $this->indexerRegistry->get($this->indexerName);
        $indexer->reindexAll();
    }

    /**
     * Submit all items to feed
     *
     * @throws \Zend_Db_Statement_Exception
     * @throws UnableSendData
     * @throws Exception
     */
    public function submitAllToFeed(): void
    {
        $lastSyncTimestamp = $this->flagManager->getFlagData($this->flagName);
        $data = $this->feedInterface->getFeedSince($lastSyncTimestamp ? $lastSyncTimestamp : '1');
        while ($data['recentTimestamp'] !== null) {
            $result = $this->submitFeed->submitFeed($data);
            if ($result) {
                $this->flagManager->saveFlag($this->flagName, $data['recentTimestamp']);
            } else {
                throw new Exception('There is an error during feed submit action.');
            }
            $lastSyncTimestamp = $this->flagManager->getFlagData($this->flagName);
            $data = $this->feedInterface->getFeedSince($lastSyncTimestamp ? $lastSyncTimestamp : '1');
        }
    }
}
