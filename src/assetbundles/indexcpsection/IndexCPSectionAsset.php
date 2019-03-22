<?php
/**
 * QandA plugin for Craft CMS 3.x
 *
 * Question & Answers
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\qanda\assetbundles\indexcpsection;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Kurious Agency
 * @package   QandA
 * @since     0.0.1
 */
class IndexCPSectionAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@kuriousagency/qanda/assetbundles/indexcpsection/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Index.js',
        ];

        $this->css = [
            'css/Index.css',
        ];

        parent::init();
    }
}
