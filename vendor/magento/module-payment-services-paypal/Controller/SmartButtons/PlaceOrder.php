<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaymentServicesPaypal\Controller\SmartButtons;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\PaymentServicesPaypal\Model\SmartButtons\Checkout;
use Exception;

class PlaceOrder implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var Checkout
     */
    private $checkout;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param MessageManagerInterface $messageManager
     * @param Checkout $checkout
     * @param UrlInterface $url
     */
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        MessageManagerInterface $messageManager,
        Checkout $checkout,
        UrlInterface $url
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->messageManager = $messageManager;
        $this->checkout = $checkout;
        $this->url = $url;
    }

    /**
     * @return ResponseInterface
     */
    public function execute() : ResponseInterface
    {
        try {
            $this->checkout->validateQuote();
            $this->checkout->placeOrder();
            $this->response->setRedirect($this->url->getUrl($this->checkout->getSuccessPageUri()));
        } catch (LocalizedException $e) {
            $this->processException(
                $e,
                $e->getMessage()
            );
        } catch (Exception $e) {
            $this->processException(
                $e,
                __('We can\'t process the order right now. Please try again later.')
            );
        }
        return $this->response;
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request) :? InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request) :? bool
    {
        return true;
    }

    /**
     * @param Exception $exception
     * @param string $message
     */
    private function processException(Exception $exception, string $message) : void
    {
        $this->messageManager->addExceptionMessage(
            $exception,
            $message
        );
        $this->response->setRedirect($this->url->getUrl('*/*/review'));
    }
}
