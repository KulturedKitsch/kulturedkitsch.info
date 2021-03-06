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
     * Special Builder for handling read optimization where clause when this is a sub-select clause.
     */
    class ReadOptimizationModelWhereAndJoinBuilder extends ModelWhereAndJoinBuilder
    {
        /**
         * @param ReadOptimizationDerivedAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         */
        public function __construct(ReadOptimizationDerivedAttributeToDataProviderAdapter
                                    $modelAttributeToDataProviderAdapter,
                                    RedBeanModelJoinTablesQueryAdapter
                                    $joinTablesAdapter)
        {
            parent::__construct($modelAttributeToDataProviderAdapter, $joinTablesAdapter);
        }

        /**
         * @param $operatorType
         * @param $value
         * @param $clausePosition
         * @param $where
         * @param null $onTableAliasName
         * @param bool $resolveAsSubquery
         */
        public function resolveJoinsAndBuildWhere($operatorType, $value, & $clausePosition, & $where,
                                                  $onTableAliasName = null, $resolveAsSubquery = false)
        {
            assert('$operatorType == null');
            assert('$value == null');
            assert('is_array($where)');
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $tableAliasName = $this->resolveJoins(
                              $onTableAliasName, ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
            $this->addReadOptimizationWhereClause($where, $clausePosition, $tableAliasName);
        }

        protected function addReadOptimizationWhereClause(& $where, $whereKey, $tableAliasName)
        {
            assert('is_array($where)');
            assert('is_int($whereKey)');
            assert('is_string($tableAliasName)');
            $q                    = DatabaseCompatibilityUtil::getQuote();
            $columnWithTableAlias = self::makeColumnNameWithTableAlias($tableAliasName,
                                    $this->modelAttributeToDataProviderAdapter->getColumnName());
            $mungeTableName      = ReadPermissionsOptimizationUtil::getMungeTableName($this->modelAttributeToDataProviderAdapter->getModelClassName());
            $mungeIds            = AllPermissionsOptimizationUtil::getMungeIdsByUser(Yii::app()->user->userModel);
            $whereContent        = $columnWithTableAlias . " " . SQLOperatorUtil::getOperatorByType('equals'). " ";
            $whereContent       .= "(select securableitem_id from {$q}$mungeTableName{$q} " .
                                   "where {$q}securableitem_id{$q} = $columnWithTableAlias and {$q}munge_id{$q}" .
                                   " in ('" . join("', '", $mungeIds) . "') limit 1)";
            $where[$whereKey]    = $whereContent;
        }
    }
?>