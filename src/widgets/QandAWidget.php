<?php
/**
 * QandA plugin for Craft CMS 3.x
 *
 * Question & Answers
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\qanda\widgets;

use kuriousagency\qanda\QandA;
use kuriousagency\qanda\assetbundles\qandawidgetwidget\QandAWidgetWidgetAsset;

use Craft;
use craft\base\Widget;

/**
 * QandA Widget
 *
 * @author    Kurious Agency
 * @package   QandA
 * @since     0.0.1
 */
class QandAWidget extends Widget
{

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $message = 'Hello, world.';

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('qand-a', 'QandAWidget');
    }

    /**
     * @inheritdoc
     */
    public static function iconPath()
    {
        return Craft::getAlias("@kuriousagency/qanda/assetbundles/qandawidgetwidget/dist/img/QandAWidget-icon.svg");
    }

    /**
     * @inheritdoc
     */
    public static function maxColspan()
    {
        return null;
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules = array_merge(
            $rules,
            [
                ['message', 'string'],
                ['message', 'default', 'value' => 'Hello, world.'],
            ]
        );
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'qand-a/_components/widgets/QandAWidget_settings',
            [
                'widget' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getBodyHtml()
    {
        Craft::$app->getView()->registerAssetBundle(QandAWidgetWidgetAsset::class);

        return Craft::$app->getView()->renderTemplate(
            'qand-a/_components/widgets/QandAWidget_body',
            [
                'message' => $this->message
            ]
        );
    }
}
