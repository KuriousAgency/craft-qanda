<?php
/**
 * QandA plugin for Craft CMS 3.x
 *
 * Question & Answers
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\qanda\controllers;

use kuriousagency\qanda\QandA;
use kuriousagency\qanda\elements\Question;

use craft\commerce\Plugin as Commerce;
use craft\commerce\elements\Product;

use Craft;
use craft\web\Controller;
use yii\base\Exception;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @author    Kurious Agency
 * @package   QandA
 * @since     0.0.1
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['save'];

    // Public Methods
    // =========================================================================

    public function actionIndex(): Response
	{
		// Craft::dd(Question::find()->relatedIds(':notempty:')->one());
		return $this->renderTemplate('qanda/index');
	}

	public function actionEdit(int $id = null): Response
	{
		if ($id) {
			$question = QandA::$plugin->service->getQuestionById($id);
			if (!$question) {
				throw new Exception(Craft::t('qanda', 'No question exists with the ID “{id}”.', ['id' => $id]));
			}
		} else {
			$question = new Question();
		}

		return $this->renderTemplate('qanda/edit', [
			'question' => $question,
		]);
	}

	public function actionSettings(): Response
	{
	
		$settings = QandA::$plugin->getSettings();

		// Craft::dd($settings);

		return $this->renderTemplate('qanda/settings',[
			'settings' => $settings
		]);
	}

	public function actionSaveSettings()
	{
        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
		$fieldLayout->type = Question::class;
		Craft::$app->getFields()->saveLayout($fieldLayout);
		$questions = Question::find()->anyStatus()->all();
		foreach ($questions as $question) {
			$question->fieldLayoutId = $fieldLayout->id;
			Craft::$app->getElements()->saveElement($question);
		}

		// save plugin settings
		$plugin = Craft::$app->getPlugins()->getPlugin('qanda');
		$settings = Craft::$app->getRequest()->getBodyParam('settings', []);
		
		if (!Craft::$app->getPlugins()->savePluginSettings($plugin, $settings)) {
            Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t save plugin settings.'));

            // Send the plugin back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'plugin' => $plugin
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('app', 'Plugin settings saved.'));

        return $this->redirectToPostedUrl();

	}
	
	public function actionSave()
	{
		$this->requirePostRequest();

		$request = Craft::$app->getRequest();
		$sendEmail = false;
		
		$id = $request->getBodyParam('id');
		if ($id) {
			$model = QandA::$plugin->service->getQuestionById($id);

			if (!$model) {
				throw new Exception(Craft::t('reviews', 'No question exists with the ID “{id}”.', ['id' => $id]));
			}
		} else {
			$model = new Question();
		}

		$answer = $request->getBodyParam('answer');

		if($answer && ($model->answer != $answer)) {
			$sendEmail = true;
		}

		$model->question = $request->getBodyParam('question', $model->question);
		$model->answer = $answer ? $answer : $model->answer;
		$model->email = $request->getBodyParam('email', $model->email);
		$model->firstName = $request->getBodyParam('firstName', $model->firstName);
		$model->lastName = $request->getBodyParam('lastName', $model->lastName);
		$model->enabled = $request->getBodyParam('enabled', $model->enabled);
		$model->customerId = $request->getBodyParam('customerId', $model->customerId);
		$model->setFieldValuesFromRequest('fields');	

		if (!$model->customerId && $request->isSiteRequest) {
			if ($user = Craft::$app->getUser()->getIdentity()) {
				$customer = Commerce::getInstance()->getCustomers()->getCustomerByUserId($user->id);
				$model->customerId = $customer->id;
			} else if ($user = Craft::$app->getUsers()->getUserByUsernameOrEmail($model->email)) {
				$customer = Commerce::getInstance()->getCustomers()->getCustomerByUserId($user->id);
				$model->customerId = $customer->id;
			}
		}

		if (!Craft::$app->getElements()->saveElement($model)) {
			Craft::$app->getSession()->setError(Craft::t('qanda', 'Couldn’t save question.'));
			//Craft::dd($model->getErrors());

            Craft::$app->getUrlManager()->setRouteParams([
                'question' => $model
			]);
			
			return null;
		}

		if($sendEmail) {
			QandA::$plugin->service->sendEmail($model);
		}		

		Craft::$app->getSession()->setNotice(Craft::t('qanda', 'Question saved.'));

        return $this->redirectToPostedUrl($model);
	}

	public function actionDelete()
	{
		$this->requirePostRequest();

		$id = Craft::$app->getRequest()->getRequiredParam('id');
		$question = QandA::$plugin->service->getQuestionById($id);

		if (!$question) {
			throw new Exception(Craft::t('qanda', 'No question exists with the ID “{id}”.', ['id' => $id]));
		}

		if (!Craft::$app->getElements()->deleteElement($question)) {
			if (Craft::$app->getRequest()->getAcceptsJson()) {
                return $this->asJson(['success' => false]);
            }

            Craft::$app->getSession()->setError(Craft::t('qanda', 'Couldn’t delete question.'));
            Craft::$app->getUrlManager()->setRouteParams([
                'question' => $question
			]);
			
			return null;
		}

		if (Craft::$app->getRequest()->getAcceptsJson()) {
            return $this->asJson(['success' => true]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('qanda', 'Question deleted.'));
        return $this->redirectToPostedUrl($question);
	}
}
