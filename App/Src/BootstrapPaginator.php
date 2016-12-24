<?php
namespace App\Src
{
    use Diana\Core\Std\StringType;
    use Diana\Core\Persistence\Sql\BaseModel;
    use Diana\Core\Mvc\View;

    class BootstrapPaginator
    {
        protected $iPages;
        protected $iCurrentPage;
        protected $iLastEntryId;
        protected $iFirstEntryId;
        protected $iFirstPageId;
        protected $iTotalItems;
        protected $view;
        protected $sController;
        protected $sAction;
        protected $iItemsPerPage;
        protected $arAdditionalParams;
        protected $arDataModels;
        protected $arAllIdsMdl;

        public function __construct(
                                    BaseModel $mdl,
                                    &$arDataModels,
                                    $iDsPerPage,
                                    View $view,
                                    StringType $sController,
                                    StringType $sAction,
                                    array $arAdditionalParams = null
                                    )
        {
            $this->arDataModels = $arDataModels;
            $this->iLastEntryId = $arDataModels[count($arDataModels) - 1]->getId();

            $this->iItemsPerPage = $iDsPerPage;
            $this->view = $view;
            $this->sController = $sController;
            $this->sAction = $sAction;

            if (
                isset($arAdditionalParams)
                && is_array($arAdditionalParams)
                && array_key_exists('countWhere', $arAdditionalParams)
                ) {
                if (
                    is_array($arAdditionalParams['countWhere'])
                    && array_key_exists('criteria', $arAdditionalParams['countWhere'])
                    && array_key_exists('joinModel', $arAdditionalParams['countWhere'])
                    ) {
                    $sJoinMdl = 'join' . $arAdditionalParams['countWhere']['joinModel'];
                    $mdl->$sJoinMdl();
                    $mdl->addWhereClause($arAdditionalParams['countWhere']['criteria']);
                } elseif (
                          is_array($arAdditionalParams['countWhere'])
                          && is_array($arAdditionalParams['countWhere'][0])
                          ) {
                    $mdl->addWhereClause($arAdditionalParams['countWhere']);
                }
            }

            $this->arAllIdsMdl = $mdl->fetchAll(array(
                'columns' => array(
                    new StringType(SQL_ESC
                        . $mdl->getTableName()
                        . SQL_ESC . '.' . SQL_ESC
                        . 'id' . SQL_ESC)
                    )
            ));
            $this->iTotalItems = count($this->arAllIdsMdl);
            $this->iPages = ceil($this->iTotalItems / $iDsPerPage * 1.0);

            if ($this->iPages == 1) {
                $this->iCurrentPage = 1;
            } else {
                $iItemsCounter = $this->iTotalItems;
                for ($iPage = $this->iPages; $iPage > 0; $iPage--) {
                    if ($iItemsCounter <= $this->iLastEntryId) {
                        $this->iCurrentPage = (int)$iPage;
                        break;
                    }

                    $iItemsCounter -= $iDsPerPage;
                }
            }

            $this->iFirstEntryId = $arDataModels[0]->getId();

            if (array_filled($arAdditionalParams)) {
                $this->arAdditionalParams = $arAdditionalParams;
            }
        }

        public function getPages()
        {
            return $this->iPages;
        }

        public function getCurrentPage()
        {
            return $this->iCurrentPage;
        }

        public function getLastEntryId()
        {
            return $this->iLastEntryId;
        }

        public function getFirstEntryId()
        {
            return $this->iFirstEntryId;
        }

        public function getTotalItems()
        {
            return $this->iTotalItems;
        }

        public function __toString()
        {
            $strBefore = '<nav>' . PHP_EOL
            . '<ul class="pagination">' . PHP_EOL;
            $strMid = '';
            $strAfter = '</ul>';

            //$this->iItemsPerPage = $this->iTotalItems * $this->iPages / $this->iPages;
            $iCntDataMdls = count($this->arDataModels);
            $iCntAllIds = count($this->arAllIdsMdl);
            for ($iPage = 1; $iPage <= $this->iPages; $iPage++) {
                if (
                    $this->iCurrentPage === 1
                    && $iPage === 1
                ) {
                    $iFirstId = $this->arDataModels[0]->getId();

                    if ($iCntDataMdls < $this->iItemsPerPage) {
                        $iLastId = $this->arDataModels[$iCntDataMdls - 1]->getId();
                    } else {
                        $iLastId = $this->arDataModels[$this->iItemsPerPage - 1]->getId();
                    }
                }

                // 2nd comparsion: count is one more, so we leave it here also one more
                elseif (
                        $iPage == $this->iPages
                        && $iCntAllIds < ((int)$this->iItemsPerPage * $iPage)
                ) {
                    $iFirstId = $this->arAllIdsMdl[(int)$this->iItemsPerPage * ($iPage - 1)]->getId();
                    $iLastId = $this->arAllIdsMdl[$iCntAllIds - 1]->getId();
                } else {
                    $iFirstId = $this->arAllIdsMdl[(int)$this->iItemsPerPage * ($iPage - 1)]->getId();
                    $iLastId = $this->arAllIdsMdl[(int)$this->iItemsPerPage * $iPage - 1]->getId();
                }

                if ($iPage === $this->iCurrentPage) {
                    $strMid .= '<li class="active"><a href="'
                    . $this->view->internalAnchor($this->view,
                        $this->sController,
                        $this->sAction,
                        is_array($this->arAdditionalParams)
                            ? array_merge(
                                array(
                                    'firstentryid' => $iFirstId,
                                    'lastentryid' => $iLastId
                                ),
                                $this->arAdditionalParams)
                            : array(
                                'firstentryid' => $iFirstId,
                                'lastentryid' => $iLastId
                            )
                    )
                    . '">' . $iPage . '<span class="sr-only">(current)</span></a></li>' . PHP_EOL;
                } else {
                    $strMid .= '<li><a href="'
                    . $this->view->internalAnchor(
                        $this->view,
                        $this->sController,
                        $this->sAction,
                        is_array($this->arAdditionalParams)
                            ? array_merge(
                                array('firstentryid' => $iFirstId, 'lastentryid' => $iLastId),
                                $this->arAdditionalParams
                                )
                            : array(
                                'firstentryid' => $iFirstId,
                                'lastentryid' => $iLastId
                                )
                    )
                    . '">' . $iPage . '</a></li>' . PHP_EOL;
                }
            }

            if ($this->iCurrentPage == 1) {
                $strBefore .= '<li class="disabled">'
                . '<a href="#"><span aria-hidden="true">&laquo;</span>'
                . '<span class="sr-only">Previous</span></a></li>' . PHP_EOL;

                if ($this->iPages > 1) {
                    $strAfter = '<li><a href="'
                        . $this->view->internalAnchor(
                            $this->view,
                            $this->sController,
                            $this->sAction,
                            is_array($this->arAdditionalParams)
                                ? array_merge(array('lastentryid' => $this->iLastEntryId),
                                    $this->arAdditionalParams)
                                : array('lastentryid' => $this->iLastEntryId)
                        )
                        . '"><span aria-hidden="true">&raquo;</span>'
                        . '<span class="sr-only">Next</span></a>' . PHP_EOL . $strAfter;
                } else {
                    $strAfter = '<li class="disabled"><a href="#">'
                    . '<span aria-hidden="true">&raquo;</span>'
                    . '<span class="sr-only">Next</span></a>' . PHP_EOL . $strAfter;
                }
            } elseif (
                      $this->iCurrentPage > 1
                      && $this->iPages > $this->iCurrentPage
            ) {
                $iPrevPageId = $this->arAllIdsMdl[
                                $this->iItemsPerPage
                                * $this->iCurrentPage
                                - $this->iItemsPerPage
                                - 1
                                ]->getId();
                $iNextPageId = $this->arAllIdsMdl[$this->iItemsPerPage * $this->iCurrentPage]
                                ->getId();
                $strBefore .= '<li><a href="'
                            . $this->view->internalAnchor(
                                $this->view,
                                $this->sController,
                                $this->sAction,
                                is_array($this->arAdditionalParams)
                                    ? array_merge(array('firstentryid' => $iPrevPageId),
                                        $this->arAdditionalParams)
                                    : array('firstentryid' => $iPrevPageId)
                            )
                            . '"><span aria-hidden="true">&laquo;</span>'
                            . '<span class="sr-only">Previous</span></a></li>' . PHP_EOL;
                $strAfter = '<li><a href="'
                        . $this->view->internalAnchor(
                            $this->view,
                            $this->sController,
                            $this->sAction,
                            is_array($this->arAdditionalParams)
                                ? array_merge(array('firstentryid' => $iNextPageId),
                                    $this->arAdditionalParams)
                                : array('firstentryid' => $iNextPageId)
                        )
                        . '"><span aria-hidden="true">&raquo;</span>'
                        . '<span class="sr-only">Next</span></a></li>' . PHP_EOL . $strAfter;
            } else {
                $iPrevPageId = $this->arAllIdsMdl[
                                $this->iItemsPerPage
                                * $this->iCurrentPage
                                - $this->iItemsPerPage
                                - 1
                                ]->getId();
                $strBefore .= '<li><a href="'
                        . $this->view->internalAnchor(
                            $this->view,
                            $this->sController,
                            $this->sAction,
                            is_array($this->arAdditionalParams)
                                ? array_merge(
                                    array('firstentryid' => $iPrevPageId),
                                    $this->arAdditionalParams)
                                : array('firstentryid' => $iPrevPageId)
                        )
                        . '"><span aria-hidden="true">&laquo;</span>'
                        . '<span class="sr-only">Previous</span></a></li>' . PHP_EOL;
                $strAfter = '<li class="disabled"><a href="#">'
                . '<span aria-hidden="true">&raquo;</span>'
                . '<span class="sr-only">Next</span></a></li>' . PHP_EOL . $strAfter;
            }

            return $strBefore . $strMid . $strAfter;
        }
    }
}
