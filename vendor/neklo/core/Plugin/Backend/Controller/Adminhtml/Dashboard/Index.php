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

namespace Neklo\Core\Plugin\Backend\Controller\Adminhtml\Dashboard;

use Closure;
use Magento\Backend\Controller\Adminhtml\Dashboard\Index as DashboardAction;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Neklo\Core\Model\Feed;

class Index
{
    /**
     * @var Auth
     */
    private Auth $auth;

    /**
     * @var Feed
     */
    private Feed $feed;

    /**
     * @param Auth $auth
     * @param Feed $feed
     */
    public function __construct(
        Auth $auth,
        Feed $feed
    ) {
        $this->auth = $auth;
        $this->feed = $feed;
    }

    /**
     * Around Execute Plugin
     *
     * @param DashboardAction $subject
     * @param Closure $proceed
     * @return Page
     * @throws FileSystemException
     * @throws RuntimeException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(DashboardAction $subject, Closure $proceed): Page
    {
        if ($this->auth->isLoggedIn()) {
            $this->feed->checkUpdate();
        }

        return $proceed();
    }
}
