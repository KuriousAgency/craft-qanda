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
	
	public function actionSave()
	{
		$this->requirePostRequest();

		$request = Craft::$app->getRequest();
		
		$id = $request->getBodyParam('id');
		if ($id) {
			$model = QandA::$plugin->service->getQuestionById($id);

			if (!$model) {
				throw new Exception(Craft::t('reviews', 'No question exists with the ID “{id}”.', ['id' => $id]));
			}
		} else {
			$model = new Question();
		}

		$model->question = $request->getBodyParam('question', $model->question);
		$model->answer = $request->getBodyParam('answer', $model->answer);
		$model->email = $request->getBodyParam('email', $model->email);
		$model->firstName = $request->getBodyParam('firstName', $model->firstName);
		$model->lastName = $request->getBodyParam('lastName', $model->lastName);
		$model->enabled = $request->getBodyParam('enabled', $model->enabled);
		$model->customerId = $request->getBodyParam('customerId', $model->customerId);
		$model->productId = $request->getBodyParam('productId', $model->productId);

		if (!$model->customerId && $request->isSiteRequest) {
			if ($user = Craft::$app->getUser()->getIdentity()) {
				$customer = Commerce::getInstance()->getCustomers()->getCustomerByUserId($user->id);
				$model->customerId = $customer->id;
			} else if ($user = Craft::$app->getUsers()->getUserByUsernameOrEmail($model->email)) {
				$customer = Commerce::getInstance()->getCustomers()->getCustomerByUserId($user->id);
				$model->customerId = $customer->id;
			}
		}

		if (is_array($model->productId)) {
			$model->productId = $model->productId[0];
		}

		//Craft::dd($model);

		if (!Craft::$app->getElements()->saveElement($model)) {
			Craft::$app->getSession()->setError(Craft::t('qanda', 'Couldn’t save question.'));
			//Craft::dd($model->getErrors());

            Craft::$app->getUrlManager()->setRouteParams([
                'question' => $model
			]);
			
			return null;
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
