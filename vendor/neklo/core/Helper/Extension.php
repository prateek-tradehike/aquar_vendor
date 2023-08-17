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

namespace Neklo\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Extension extends AbstractHelper
{
    /**
     * @var array
     */
    private array $moduleConfigList = [];

    /**
     * @var array
     */
    private array $moduleList = [];

    /**
     *
     * @var ModuleList
     */
    private ModuleList $magentoModuleList;

    /**
     * @var ComponentRegistrarInterface
     */
    private ComponentRegistrarInterface $componentRegistrar;

    /**
     * @var ReadFactory
     */
    private ReadFactory $readFactory;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param ModuleList $magentoModuleList
     * @param Context $context
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory $readFactory
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleList $magentoModuleList,
        Context $context,
        ComponentRegistrarInterface $componentRegistrar,
        ReadFactory $readFactory,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->magentoModuleList = $magentoModuleList;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->serializer = $serializer;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Get Modules List
     *
     * @return array
     */
    public function getModuleList(): array
    {
        if (!count($this->moduleList)) {
            $moduleList = [];
            foreach ($this->getModuleConfigList() as $moduleCode => $moduleConfig) {
                $moduleList[$moduleCode] = [
                    'name'    => $moduleConfig['name'] ?: $moduleCode,
                    'version' => $moduleConfig['setup_version'],
                ];
            }

            $this->moduleList = $moduleList;
        }

        return $this->moduleList;
    }

    /**
     * Get Module Config List
     *
     * @return array
     */
    public function getModuleConfigList(): array
    {
        if (!count($this->moduleConfigList)) {
            $moduleConfigList = $this->magentoModuleList->getAll();

            ksort($moduleConfigList);
            $moduleList = [];
            foreach ($moduleConfigList as $moduleCode => $moduleConfig) {
                if (!$this->canShowExtension($moduleCode, $moduleConfig)) {
                    continue;
                }
                if (empty($moduleConfig['setup_version'])) {
                    $moduleConfig['setup_version'] = $this->getVersionFromComposer($moduleConfig['name']);
                }
                $moduleList[strtolower($moduleCode).'_m2'] = $moduleConfig;
            }

            $this->moduleConfigList = $moduleList;
        }

        return $this->moduleConfigList;
    }

    /**
     * Check Extension
     *
     * @param string $code
     * @param array $config
     *
     * @return bool
     */
    private function canShowExtension(string $code, array $config): bool
    {
        /** @todo check logic and remove if unnecessary */
        if (!$code || !$config) {
            return false;
        }

        if (!$this->isNekloExtension($code)) {
            return false;
        }

        return true;
    }

    /**
     * Check is Neklo ext
     *
     * @param string $code
     *
     * @return bool
     */
    private function isNekloExtension(string $code): bool
    {
        return (str_contains($code, 'Neklo_'));
    }

    /**
     * You know it is for declarative scheme
     *
     * @param string $name
     * @return string
     */
    public function getVersionFromComposer(string $name): string
    {
        $version = '';

        try {
            $path = $this->componentRegistrar->getPath(
                ComponentRegistrar::MODULE,
                $name
            );
            $directoryRead = $this->readFactory->create($path);
            $composerJson = $directoryRead->readFile('composer.json');
            $data = $this->serializer->unserialize($composerJson);
            if (isset($data['version'])) {
                return (string)$data['version'];
            }
        } catch (FileSystemException|ValidatorException $e) {
            $this->logger->log(LogLevel::WARNING, __('Can not read composer.json for module %1', $name));
        }
        $this->logger->log(LogLevel::WARNING, __('Version for module %1 can not be detected', $name));
        return $version;
    }
}
