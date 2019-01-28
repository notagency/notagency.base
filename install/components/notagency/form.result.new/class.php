<?php

namespace Notagency\Components;

use Notagency\Base\ComponentsBase;

if (!\Bitrix\Main\Loader::includeModule('notagency.base')) return false;

class FormResultNew extends ComponentsBase
{
	protected $cacheTemplate = false;
	protected $needModules = [
		'form'
	];

	protected $formId = null;
	protected $permissions = null;
	protected $isSuccess = false;
	protected $errors = [];
	protected $requestData = [];


	/**
	 * @inheritdoc onPrepareComponentParams
	 */
	public function onPrepareComponentParams($arParams)
	{
		if (isset($arParams['RESULT_ID'])) {
			unset($arParams['RESULT_ID']);
		}
		return $arParams;
	}

	/**
	 * @inheritdoc executeProlog
	 */
	protected function executeProlog()
	{
		if (!$this->needCache()) {
			$this->arParams['CACHE_TYPE'] = 'N';
		}
		if (!empty($this->arParams['WEB_FORM_CODE'])) {
			$this->formId = $this->getFormId($this->arParams['WEB_FORM_CODE']);
		}
		if (!$this->formId) {
			throw new \Exception(sprintf('Web form doesn\'t exists with code %s', $this->arParams['WEB_FORM_CODE']));
		}
		$this->permissions = \CForm::GetPermission($this->formId);
		$this->handlePost();
		$this->isSuccess = $this->isSuccess();
		$this->addCacheAdditionalId(json_encode($this->arParams));
		$this->addCacheAdditionalId($this->permissions);
	}

	/**
	 * @inheritdoc executeMain
	 */
	protected function executeMain()
	{
		if (!$this->checkPermissions()) {
			return;
		}
		if ($this->initFormData()) {
			$this->formatResult();
		} else {
			throw new \Exception('Internal error');
		}

		if ($this->isFormSubmitted()) {
			$this->arResult['FIELD_ERRORS'] = $this->errors;
			$this->arResult['REQUEST_DATA'] = $this->requestData;
		}
		if ($this->request->getQuery('success')) {
			$this->arResult['SUCCESS'] = $this->isSuccess;
		}
	}

	/**
	 * Нужно ли кеширование формы
	 * @return bool
	 */
	protected function needCache()
	{
		$webFormId = $this->request->getPost('WEB_FORM_ID');
		$success = $this->request->getQuery('success');
		return empty($webFormId) && empty($success);
	}

	/**
	 * Проверяет был ли сабмит формы
	 */
	protected function isFormSubmitted()
	{
		return $this->request->getPost('WEB_FORM_ID') == $this->formId;
	}

	/**
	 * Возвращает ID формы по символьному коду
	 * @param $code
	 * @return int|false
	 */
	protected function getFormId($code)
	{
		if ($form = \CForm::GetBySID($code)->Fetch()) {
			return $form['ID'];
		} else {
			return false;
		}
	}

	/**
	 * Проверяет права доступа к форме
	 * @return bool
	 */
	protected function checkPermissions()
	{
		if ($this->permissions < 10) {
			$this->arResult['ERRORS']['ACCESS_DENIED'] = 'Форма недоступна';
			return false;
		}
		return true;
	}

	/**
	 * Обрабатывает POST запрос
	 */
	protected function handlePost()
	{
		if (!$this->isFormSubmitted() && !check_bitrix_sessid()) {
			return;
		}

		$this->requestData= $this->request->getPostList()->toArray();
		if ($this->checkErrors($this->requestData)) {
			// add result
			if ($resultId = $this->addResult()) {
				// send email notifications
				$this->sendEmails($resultId);
				$url = $GLOBALS['APPLICATION']->GetCurPageParam('WEB_FORM_ID=' . $this->formId . '&RESULT_ID=' . $resultId . '&success=yes',
					['formresult', 'strFormNote', 'WEB_FORM_ID', 'RESULT_ID', 'success']
				);
				LocalRedirect($url);
				die();
			} else {
				$this->arResult['ERRORS']['ADD_TO_DB'] = $GLOBALS['strError'];
			}
		}
	}

	/**
	 * Добавляет результат
	 */
	protected function addResult()
	{
		$resultId = \CFormResult::Add($this->formId, $this->requestData);
		return $resultId;
	}

	/**
	 * Отправляет почтовые уведомления
	 */
	protected function sendEmails($resultId)
	{
		\CFormResult::Mail($resultId);
	}

	/**
	 * Валидируем POST данные
	 * @param array $values
	 * @return bool
	 */
	protected function checkErrors(array $values)
	{
		$this->errors = \CForm::Check($this->formId, $values, false, 'Y', 'Y');
		return !is_array($this->errors) || count($this->errors) <= 0;
	}

	/**
	 * Проверяем признак того что форма была отправлена успешно
	 */
	protected function isSuccess()
	{
		$success = $this->request->getQuery('success');
		return !empty($success) && $this->request->getQuery('WEB_FORM_ID') == $this->formId;
	}

	/**
	 * Получаем данные формы
	 */
	protected function initFormData()
	{
		return \CForm::GetDataByID($this->formId,
			$this->arResult['RAW']['arForm'],
			$this->arResult['RAW']['arQuestions'],
			$this->arResult['RAW']['arAnswers'],
			$this->arResult['RAW']['arDropDown'],
			$this->arResult['RAW']['arMultiSelect'],
			'N'
		);
	}

	/**
	 * Форматируем результат
	 */
	public function formatResult()
	{

		$this->arResult['FORM'] = [
			'ID' => $this->arResult['RAW']['arForm']['ID'],
			'CODE' => $this->arParams['FORM_CODE'],
			'NAME' => $this->arParams['FORM_NAME'] ? $this->arParams['FORM_NAME'] : $this->arResult['RAW']['arForm']['NAME'],
			'DESCRIPTION' => $this->arResult['RAW']['arForm']['DESCRIPTION'],
			'SUBMIT_CAPTION' => $this->arResult['RAW']['arForm']['BUTTON'],
			'QUESTIONS' => [],
		];

		foreach ($this->arResult['RAW']['arQuestions'] as $questionRaw) {
			
			$fieldCode = $questionRaw['SID'];
			$question = [
				'CAPTION' => $questionRaw['TITLE_TYPE'] == 'html' ? $questionRaw['TITLE'] : nl2br(htmlspecialcharsbx($questionRaw['TITLE'])),
				'IS_HTML_CAPTION' => $questionRaw['TITLE_TYPE'] == 'html' ? 'Y' : 'N',
				'REQUIRED' => $questionRaw['REQUIRED'] == 'Y' ? 'Y' : 'N',
				'IS_INPUT_CAPTION_IMAGE' => intval($questionRaw['IMAGE_ID']) > 0 ? 'Y' : 'N',
				'ANSWERS' => [],
				'VALIDATORS' => [],
			];

			$resValidator = \CFormValidator::GetList($questionRaw['ID'], [], $by="C_SORT", $order="ASC");
			while($arValidator = $resValidator->fetch()){
				$arExt = [];
				if(!empty($arValidator['PARAMS']['EXT'])){
					$arExt = explode(',', $arValidator['PARAMS']['EXT']);
				}elseif($arValidator['PARAMS']['EXT_CUSTOM']){
					$arExt = explode(',', $arValidator['PARAMS']['EXT_CUSTOM']);
				}
				
				if(!empty($arExt)){
					$arValidator['PARAMS']['AR_EXT'] = $arExt;
				}
				
				$question['VALIDATORS'][] = $arValidator;
			}
			
			$answers = $this->arResult['RAW']['arAnswers'][$fieldCode];
			if (is_array($answers)) {
				foreach ($answers as $answer) {
					switch ($answer['FIELD_TYPE']) {
						case 'radio':
							$answer['FIELD_NAME'] = $this->getFieldName($answer['FIELD_TYPE'], $fieldCode);
							if (isset($this->requestData[$answer['FIELD_NAME']])) {
								$answer['CHECKED'] = (int) $this->requestData[$answer['FIELD_NAME']] === (int) $answer['ID'];
							} else {
								$answer['CHECKED'] = preg_match('/selected|checked/', $answer['FIELD_PARAM']);
							}
							break;
						case 'checkbox':
							$answer['FIELD_NAME'] = $this->getFieldName($answer['FIELD_TYPE'], $fieldCode, true);
							$fieldNameRequest = $this->getFieldName($answer['FIELD_TYPE'], $fieldCode);
							if (is_array($this->requestData[$fieldNameRequest])) {
								$answer['CHECKED'] = in_array($answer['ID'], $this->requestData[$fieldNameRequest]);
							} else {
								$answer['CHECKED'] = preg_match('/selected|checked/', $answer['FIELD_PARAM']);
							}
							break;
						case 'dropdown':
							$answer['FIELD_NAME'] = $this->getFieldName($answer['FIELD_TYPE'], $fieldCode);
							if (isset($this->requestData[$answer['FIELD_NAME']])) {
								$answer['SELECTED'] = (int) $this->requestData[$answer['FIELD_NAME']] === (int) $answer['ID'];
							} else {
								$answer['SELECTED'] = preg_match('/selected|checked/', $answer['FIELD_PARAM']);
							}
							break;
						case 'multiselect':
							$answer['FIELD_NAME'] = $this->getFieldName($answer['FIELD_TYPE'], $fieldCode, true);
							$fieldNameRequest = $this->getFieldName($answer['FIELD_TYPE'], $fieldCode);
							if (is_array($this->requestData[$fieldNameRequest])) {
								$answer['SELECTED'] = in_array($answer['ID'], $this->requestData[$fieldNameRequest]);
							} else {
								$answer['SELECTED'] = preg_match('/selected|checked/', $answer['FIELD_PARAM']);
							}
							break;
						case 'text':
						case 'hidden':
						case 'password':
						case 'email':
						case 'url':
						case 'textarea':
						case 'date':
							$answer['FIELD_NAME'] = $this->getFieldName($answer['FIELD_TYPE'], $answer['ID']);
							if (isset($this->requestData[$answer['FIELD_NAME']])){
								$answer['VALUE'] = $this->requestData[$answer['FIELD_NAME']];
							}
							break;
						case 'image':
						case 'file':
							$answer['FIELD_NAME'] = $this->getFieldName($answer['FIELD_TYPE'], $answer['ID']);
							break;
					}
					$question['ANSWERS'][] = $answer;
				}
			}

			if ($question['IS_INPUT_CAPTION_IMAGE'] == 'Y') {
				$question['IMAGE'] = [
					'ID' => $question['IMAGE_ID'],
				];
				$image = \CFile::GetFileArray($question['IMAGE_ID']);
				$question['IMAGE']['URL'] = $image['SRC'];
				$question['IMAGE']['HTML_CODE'] = \CFile::ShowImage($question['IMAGE_ID']);

				// check image file existence and assign image data
				if (substr($image['SRC'], 0, 1) == '/') {
					$size = \CFile::GetImageSize($_SERVER['DOCUMENT_ROOT'] . $image['SRC']);
					if (is_array($size)) {
						list($question['IMAGE']['WIDTH'], $question['IMAGE']['HEIGHT'], $question['IMAGE']['TYPE'], $question['IMAGE']['ATTR']) = $size;
					}
				} else {
					$question['IMAGE']['WIDTH'] = $image['WIDTH'];
					$question['IMAGE']['HEIGHT'] = $image['HEIGHT'];
					$question['IMAGE']['TYPE'] = false;
					$question['IMAGE']['ATTR'] = false;
				}
			}

			$this->arResult['FORM']['QUESTIONS'][$fieldCode] = $question;
		}
	}

	protected function getFieldName($fieldType, $identity, $multipleField = false)
	{
		return sprintf('form_%s_%s%s', $fieldType, $identity, $multipleField ? '[]' : '');
	}
}