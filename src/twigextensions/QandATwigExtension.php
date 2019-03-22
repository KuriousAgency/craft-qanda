<?php
/**
 * QandA plugin for Craft CMS 3.x
 *
 * Question & Answers
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\qanda\twigextensions;

use kuriousagency\qanda\QandA;

use Craft;

/**
 * @author    Kurious Agency
 * @package   QandA
 * @since     0.0.1
 */
class QandATwigExtension extends \Twig_Extension
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'QandA';
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('someFilter', [$this, 'someInternalFunction']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('someFunction', [$this, 'someInternalFunction']),
        ];
    }

    /**
     * @param null $text
     *
     * @return string
     */
    public function someInternalFunction($text = null)
    {
        $result = $text . " in the way";

        return $result;
    }
}
