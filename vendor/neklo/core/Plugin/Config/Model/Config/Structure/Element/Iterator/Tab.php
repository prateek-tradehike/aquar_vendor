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

namespace Neklo\Core\Plugin\Config\Model\Config\Structure\Element\Iterator;

use Magento\Config\Model\Config\Structure\Element\Iterator\Tab as TabIterator;

class Tab
{
    /**
     * Before Plugin
     *
     * @param TabIterator $subject
     * @param array       $elements
     * @param string      $scope
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetElements(TabIterator $subject, array $elements, string $scope): array
    {
        $children = [];
        foreach ($elements as $elementName => $element) {
            if ($element['id'] !== 'neklo' || ! isset($element['children'])) {
                continue;
            }

            $sectionList = $element['children'];
            usort($sectionList, [$this, '_sort']);
            foreach ($sectionList as $section) {
                $children[$section['id']] = $section;
            }

            $elements[$elementName]['children'] = $children;
            break;
        }

        return [$elements, $scope];
    }

    /**
     * Custom Sort
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    private function _sort(array $a, array $b): int
    {
        if ($a['id'] == 'neklo_core') {
            return 1;
        }

        if ($b['id'] == 'neklo_core') {
            return -1;
        }

        return strcasecmp($a['label'], $b['label']);
    }
}
