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

namespace Neklo\Core\Mail;

use Neklo\Core\Mail\Template\TransportBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface as StoreManager;


class Sender
{
    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * @var StateInterface
     */
    private StateInterface $state;

    /**
     * @var StoreManager
     */
    private StoreManager $storeManager;

    /**
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $state
     * @param StoreManager $storeManager
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        StateInterface $state,
        StoreManager $storeManager
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->state = $state;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute
     *
     * @param string $templateId
     * @param string $from
     * @param string $to
     * @param array $variables
     * @param string $area
     * @param array $templateOptions
     * @param array $attachments
     *
     * @return void
     * @throws LocalizedException
     * @throws MailException
     */
    public function execute(
        string $templateId,
        string $from,
        string $to,
        array  $variables = [],
        string $area = Area::AREA_FRONTEND,
        array  $templateOptions = [],
        array  $attachments = []
    ): void {
        try {
            $this->state->suspend();
            $transport = $this->buildTransport(
                $templateId,
                $from,
                [$to],
                $variables,
                $area,
                $templateOptions,
                $attachments
            );
            $transport->sendMessage();
        } finally {
            $this->state->resume();
        }
    }

    /**
     * Execute Multiple
     *
     * @param string $templateId
     * @param string $from
     * @param array $to
     * @param array $variables
     * @param string $area
     * @param array $templateOptions
     * @param array $attachments
     *
     * @return void
     * @throws LocalizedException
     * @throws MailException
     */
    public function executeMultiple(
        string $templateId,
        string $from,
        array  $to,
        array  $variables = [],
        string $area = Area::AREA_FRONTEND,
        array  $templateOptions = [],
        array  $attachments = []
    ): void {
        try {
            $this->state->suspend();
            $transport = $this->buildTransport(
                $templateId,
                $from,
                $to,
                $variables,
                $area,
                $templateOptions,
                $attachments
            );
            $transport->sendMessage();
        } finally {
            $this->state->resume();
        }
    }

    /**
     * Get TransportInterface
     *
     * @param string $templateId
     * @param string $sender
     * @param array $emailTo
     * @param array $variables
     * @param string $area
     * @param array $templateOptions
     * @param array $attachments
     *
     * @return TransportInterface
     * @throws LocalizedException
     * @throws MailException
     */
    private function buildTransport(
        string $templateId,
        string $sender,
        array  $emailTo,
        array  $variables = [],
        string $area = Area::AREA_FRONTEND,
        array  $templateOptions = [],
        array  $attachments = []
    ): TransportInterface {
        $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions($this->buildTemplateOptions($area, $templateOptions))
            ->setTemplateVars($variables)
            ->setFrom($sender);

        foreach ($emailTo as $email) {
            $this->transportBuilder->addTo($email);
        }
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                if (!isset($attachment['content']) || !isset($attachment['file_name'])) {
                    throw new MailException(__("Attachment could not be sent."));
                }
                $this->transportBuilder->addAttachment($attachment['content'], $attachment['file_name']);
            }
        }

        return $this->transportBuilder->getTransport();
    }

    /**
     * Build Template Options
     *
     * @param string $area
     * @param array $templateOptions
     *
     * @return array
     */
    private function buildTemplateOptions(string $area, array $templateOptions): array
    {
        return array_merge(
            [
                'area' => $area,
                'store' => $this->getCurrentStoreId(),
            ],
            $templateOptions
        );
    }

    /**
     * Get store id
     *
     * @return int
     */
    private function getCurrentStoreId(): int
    {
        try {
            $store = $this->storeManager->getStore();
            $storeId = $store->getId();
        } catch (NoSuchEntityException $e) {
            $storeId = Store::DEFAULT_STORE_ID;
        }

        return (int)$storeId;
    }
}
