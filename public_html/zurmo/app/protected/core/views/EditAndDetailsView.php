<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2015 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2015. All rights reserved".
     ********************************************************************************/

    /**
     * The base View for a module's edit and detail combination view
     */
    abstract class EditAndDetailsView extends DetailsView
    {
        /**
         * Set as either Edit or Details
         */
        protected $renderType;

        /**
         * Property to decide if a form needs to change its enctype
         * to multipart/form-data
         * @var boolean
         */
        protected $viewContainsFileUploadElement = false;

        /**
         * Accepts $renderType as Edit or Details
         */
        public function __construct($renderType, $controllerId, $moduleId, $model)
        {
            assert('$model instanceof RedBeanModel || $model instanceof CFormModel || $model instanceof ModelForm');
            assert('$renderType == "Edit" || $renderType == "Details"');
            $this->renderType = $renderType;
            parent::__construct($controllerId, $moduleId, $model);
        }

        /**
         * Override of parent function. Makes use of the ZurmoActiveForm
         * widget to provide an editable form.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            if ($this->renderType == 'Details')
            {
                return parent::renderContent();
            }
            $content  = '<div class="wrapper">';
            $content .= $this->renderTitleContent();
            $content .= $this->resolveAndRenderActionElementMenuForEdit();
            $maxCellsPresentInAnyRow = $this->resolveMaxCellsPresentInAnyRow($this->getFormLayoutMetadata());
            if ($maxCellsPresentInAnyRow > 1)
            {
                $class = "wide double-column form";
            }
            else
            {
                $class = "wide form";
            }
            $content .= '<div class="' . $class . '">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                static::getFormClassName(),
                                                                array_merge(
                                                                    array('id' => static::getFormId(),
                                                                    'htmlOptions' => $this->resolveFormHtmlOptions()),
                                                                    $this->resolveActiveFormAjaxValidationOptions()
                                                                )
                                                            );
            $content .= $formStart;
            if ($form != null && $this->renderRightSideFormLayoutForEdit($form) == null)
            {
                $class = ' full-width';
            }
            else
            {
                $class = '';
            }
            $formContent  = $this->beforeRenderingFormLayout();
            $formContent .= ZurmoHtml::tag('div', array('class' => 'left-column' . $class), $this->renderFormLayout($form));
            $formContent .= $this->renderRightSideContent($form);
            $content .= $this->renderAttributesContainerWrapperDiv($formContent);
            $content .= $this->renderAfterFormLayout($form);
            $actionElementContent = $this->renderActionElementBar(true);
            if ($actionElementContent != null)
            {
                $content .= $this->resolveAndWrapDockableViewToolbarContent($actionElementContent);
            }
            $formEnd = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= $this->renderModalContainer();
            $content .= '</div></div>';
            return $content;
        }

        public function getTitle()
        {
            if ($this->model->id > 0)
            {
                return strval($this->model);
            }
            return $this->getNewModelTitleLabel();
        }

        protected static function getFormClassName()
        {
            return 'ZurmoActiveForm';
        }

        protected function renderRightSideContent($form = null)
        {
            assert('$form == null || $form instanceof ZurmoActiveForm');
            if ($form != null)
            {
                $rightSideContent = $this->renderRightSideFormLayoutForEdit($form);
                if ($rightSideContent != null)
                {
                    $content = ZurmoHtml::tag('div', array('class' => 'right-side-edit-view-panel'), $rightSideContent);
                    $content = ZurmoHtml::tag('div', array('class' => 'right-column'), $content);
                    return $content;
                }
            }
        }

        protected function renderRightSideFormLayoutForEdit($form)
        {
        }

        protected function renderAfterFormLayout($form)
        {
            DropDownUtil::registerScripts();
        }

        protected function renderModalContainer()
        {
            return ZurmoHtml::tag('div', array('id' => ModelElement::MODAL_CONTAINER_PREFIX . '-' . $this->getFormId()), '');
        }

        protected function resolveActiveFormAjaxValidationOptions()
        {
            return array('enableAjaxValidation' => false);
        }

        public static function getDesignerRulesType()
        {
            return 'EditAndDetailsView';
        }

        protected function shouldDisplayCell($detailViewOnly)
        {
            if ($this->renderType == 'Details')
            {
                return true;
            }
            return !$detailViewOnly;
        }

        protected function shouldDisplayPanel($detailViewOnly)
        {
            if ($this->renderType == 'Details')
            {
                return true;
            }
            return !$detailViewOnly;
        }

        protected function shouldRenderToolBarElement($element, $elementInformation)
        {
            assert('$element instanceof ActionElement');
            assert('is_array($elementInformation)');
            if (!parent::shouldRenderToolBarElement($element, $elementInformation))
            {
                return false;
            }
            if (!isset($elementInformation['renderType']) ||
                (isset($elementInformation['renderType']) &&
                $elementInformation['renderType'] == $this->renderType
                )
            )
            {
                return true;
            }
            return false;
        }

        protected static function getFormId()
        {
            return 'edit-form';
        }

        protected function resolveFormHtmlOptions()
        {
            $data = array('onSubmit' => 'js:return $(this).attachLoadingOnSubmit("' . static::getFormId() . '")');
            if ($this->viewContainsFileUploadElement)
            {
                $data['enctype'] = 'multipart/form-data';
            }
            return $data;
        }

        protected function getNewModelTitleLabel()
        {
            throw new NotImplementedException();
        }

        protected function beforeRenderingFormLayout()
        {
            if ($dedupeRules = DedupeRulesFactory::createRulesByModel($this->model))
            {
                $dedupeViewClassName = $dedupeRules->getDedupeViewClassName();
                $summaryView = new $dedupeViewClassName($this->controllerId,
                    $this->moduleId,
                    $this->model,
                    array());
                return $summaryView->render();
            }
            else
            {
                return null;
            }
        }

        protected function resolveAndRenderActionElementMenuForEdit()
        {
        }

        protected function resolveElementDuringFormLayoutRender(& $element)
        {
            if ($dedupeRules = DedupeRulesFactory::createRulesByModel($this->model))
            {
                $dedupeRules->registerScriptForEditAndDetailsView($element);
            }
            parent::resolveElementDuringFormLayoutRender($element);
        }

        protected function renderAttributesContainerWrapperDiv($content)
        {
            return ZurmoHtml::tag('div', array('class' => 'attributesContainer'), $content);
        }
    }
?>
