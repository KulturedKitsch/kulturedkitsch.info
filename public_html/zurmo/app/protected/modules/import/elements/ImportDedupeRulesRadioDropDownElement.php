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
     * Display radio buttons for selecting the rules for dedupe
     * @see EmailAttributeImportRules
     */
    class ImportDedupeRulesRadioDropDownElement extends RadioDropDownElement
    {
        const DO_NOT_DEDUPE = 1;

        const SKIP_ROW_ON_MATCH_FOUND = 2;

        const UPDATE_ROW_ON_MATCH_FOUND = 3;

        protected function renderControlEditable()
        {
            $content = null;
            $content .= $this->form->radioButtonList(
                $this->model,
                $this->attribute,
                $this->getDropDownArray(),
                $this->getEditableHtmlOptions()
            );
            return $content;
        }

        protected function getDropDownArray()
        {
            return array( self::DO_NOT_DEDUPE             => Zurmo::t('ImportModule', 'Do not dedupe'),
                          self::SKIP_ROW_ON_MATCH_FOUND   => Zurmo::t('ImportModule', 'When a match is found, skip row'),
                          self::UPDATE_ROW_ON_MATCH_FOUND => Zurmo::t('ImportModule', 'When a match is found, update existing record'));
        }

        protected function getEditableHtmlOptions()
        {
            $htmlOptions = parent::getEditableHtmlOptions();
            $htmlOptions['template'] =  '<div class="radio-input">{input}{label}</div>';
            $htmlOptions['separator'] = '';
            if (isset($htmlOptions['empty']))
            {
                unset($htmlOptions['empty']);
            }
            return $htmlOptions;
        }

        /**
         * We do not need two nested arrays for attribute name, so avoid ['value'] part
         * @return string
         */
        protected function getNameForSelectInput()
        {
            return $this->getEditableInputName($this->attribute);
        }
    }
?>