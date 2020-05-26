<?php
/**
 * QandA plugin for Craft CMS 3.x
 *
 * Question & Answers
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\qanda;

use kuriousagency\qanda\services\QandAService as Service;
use kuriousagency\qanda\variables\QandAVariable;
//use kuriousagency\qanda\twigextensions\QandATwigExtension;
use kuriousagency\qanda\elements\Question;
use kuriousagency\qanda\fields\Questions as QuestionsField;
//use kuriousagency\qanda\widgets\QandAWidget as QandAWidgetWidget;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Elements;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\services\Dashboard;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class QandA
 *
 * @author    Kurious Agency
 * @package   QandA
 * @since     0.0.1
 *
 * @property  QandAServiceService $qandAService
 */
class QandA extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var QandA
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
	public $schemaVersion = '0.0.1';
	
	public $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
		self::$plugin = $this;
		
		$this->setComponents([
			'service' => Service::class,
		]);

        //Craft::$app->view->registerTwigExtension(new QandATwigExtension());

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['qanda'] = 'qanda/default/index';
				$event->rules['qanda/new'] = 'qanda/default/edit';
				$event->rules['qanda/<id:\d+>'] = 'qanda/default/edit';
            }
        );

        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = Question::class;
            }
        );

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = QuestionsField::class;
            }
        );
        /*
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = QandAWidgetWidget::class;
            }
        );*/

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->attachBehavior('qanda', QandAVariable::class);
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'qanda',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
