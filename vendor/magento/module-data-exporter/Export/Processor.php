<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DataExporter\Export;

use Magento\DataExporter\Export\Request\InfoAssembler;
use Magento\DataExporter\Model\Logging\CommerceDataExportLoggerInterface as LoggerInterface;

/**
 * Class Processor
 *
 * Processes data for given field from et_schema
 */
class Processor
{
    /**
     * @var Extractor
     */
    private $extractor;

    /**
     * @var Transformer
     */
    private $transformer;

    /**
     * @var InfoAssembler
     */
    private $infoAssembler;

    /**
     * @var string
     */
    private $rootProfileName;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Extractor $extractor
     * @param Transformer $transformer
     * @param InfoAssembler $infoAssembler
     * @param LoggerInterface $logger
     * @param string $rootProfileName
     */
    public function __construct(
        Extractor $extractor,
        Transformer $transformer,
        InfoAssembler $infoAssembler,
        LoggerInterface $logger,
        string $rootProfileName = 'Export'
    ) {
        $this->extractor = $extractor;
        $this->transformer = $transformer;
        $this->infoAssembler = $infoAssembler;
        $this->rootProfileName = $rootProfileName;
        $this->logger = $logger;
    }

    /**
     * Process data
     *
     * @param string $fieldName
     * @param array $arguments
     * @return array
     */
    public function process(string $fieldName, array $arguments = []) : array
    {
        try {
            $info = $this->infoAssembler->assembleFieldInfo($fieldName, $this->rootProfileName);
            $snapshots = $this->extractor->extract($info, $arguments);
            return $this->transformer->transform($info, $snapshots);
        } catch (\Throwable $exception) {
            $provider = empty($info) === false ? $info->getRootNode()->getField()['provider'] : '';
            // if error happened during data collecting we skip entire process
            $this->logger->error(
                \sprintf(
                    'Unable to collect data for provider %s, error: %s',
                    $provider,
                    $exception->getMessage()
                ),
                ['exception' => $exception]
            );
        }

        return [];
    }
}
