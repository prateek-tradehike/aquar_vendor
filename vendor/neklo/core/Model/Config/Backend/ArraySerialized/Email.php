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

namespace Neklo\Core\Model\Config\Backend\ArraySerialized;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Validator\EmailAddress as EmailAddressValidator;

class Email extends ArraySerialized
{
    /**
     * Email field name
     */
    public const FIELD_EMAIL = 'email';

    /**
     * @var EmailAddressValidator
     */
    private EmailAddressValidator $emailAddressValidator;

    /**
     * @param EmailAddressValidator $emailAddressValidator
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        EmailAddressValidator $emailAddressValidator,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
        $this->emailAddressValidator = $emailAddressValidator;
    }

    /**
     * Validate before save
     *
     * @return Email
     * @throws LocalizedException
     */
    public function beforeSave(): Email
    {
        $this->filterRows();
        $this->validateRows();

        return parent::beforeSave();
    }

    /**
     * Filter
     *
     * @return void
     */
    private function filterRows(): void
    {
        $emails = $this->getData('value');
        if (is_array($emails)) {
            $uniqueEmails = [];
            foreach ($emails as $rowId => $row) {
                if (!is_array($row)) {
                    continue;
                }

                $email = $row[self::FIELD_EMAIL] ?? null;
                if (!$email) {
                    unset($emails[$rowId]);
                }

                if (in_array($email, $uniqueEmails)) {
                    unset($emails[$rowId]);
                } else {
                    $uniqueEmails[] = $email;
                }
            }

            $this->setData('value', $emails);
        }
    }

    /**
     * Validate
     *
     * @return void
     * @throws LocalizedException
     */
    private function validateRows(): void
    {
        $invalidEmails = $this->getInvalidEmails();
        if (!empty($invalidEmails)) {
            throw new LocalizedException($this->buildErrorMessage($invalidEmails));
        }
    }

    /**
     * Get Invalid Emails
     *
     * @return array
     */
    private function getInvalidEmails(): array
    {
        $invalidEmails = [];

        $emails = $this->getData('value');
        if (is_array($emails)) {
            foreach ($emails as $rowId => $row) {
                if (!is_array($row)) {
                    continue;
                }

                $email = $row[self::FIELD_EMAIL] ?? null;
                if (!$this->emailAddressValidator->isValid($email)) {
                    $invalidEmails[] = $email;
                }
            }
        }

        return $invalidEmails;
    }

    /**
     * Build error message
     *
     * @param array $invalidEmails
     *
     * @return Phrase
     */
    private function buildErrorMessage(array $invalidEmails): Phrase
    {
        $invalidEmailCount = count($invalidEmails);
        if ($invalidEmailCount > 1) {
            $errorMessage = __('Emails "%1" are invalid.', implode('", "', $invalidEmails));
        } else {
            $email = current($invalidEmails);
            $errorMessage = __('Email "%1" is invalid.', $email);
        }

        return $errorMessage;
    }
}
