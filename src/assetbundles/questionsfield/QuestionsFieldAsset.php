<?php
/**
 * QandA plugin for Craft CMS 3.x
 *
 * Question & Answers
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\qanda\assetbundles\questionsfield;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Kurious Agency
 * @package   QandA
 * @since     0.0.1
 */
class QuestionsFieldAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@kuriousagency/qanda/assetbundles/questionsfield/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Questions.js',
        ];

        $this->css = [
            'css/Questions.css',
        ];

        parent::init();
    }
}
