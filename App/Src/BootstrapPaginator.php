<?php
namespace App\Src
{
	use Diana\Core\Std\String;
	use Diana\Core\Persistence\Sql\BaseModel;
	use Diana\Core\Mvc\View;

	class BootstrapPaginator
	{
		protected $_iPages;
		protected $_iCurrentPage;
		protected $_iLastEntryId;
		protected $_iFirstEntryId;
		protected $_iFirstPageId;
		protected $_iTotalItems;
		protected $_view;
		protected $_sController;
		protected $_sAction;
		protected $_iItemsPerPage;
		protected $_arAdditionalParams;
		protected $_arDataModels;
		protected $_arAllIdsMdl;

		public function __construct(BaseModel $mdl, &$arDataModels, $iDsPerPage, View $view, String $sController, String $sAction, array $arAdditionalParams = null)
		{
			$this->_arDataModels = $arDataModels;
			$this->_iLastEntryId = $arDataModels[count($arDataModels) - 1]->getId();

			$this->_iItemsPerPage = $iDsPerPage;
			$this->_view = $view;
			$this->_sController = $sController;
			$this->_sAction = $sAction;

			if (isset($arAdditionalParams) && is_array($arAdditionalParams) && array_key_exists('countWhere', $arAdditionalParams))
			{
				if (is_array($arAdditionalParams['countWhere'])
					&& array_key_exists('criteria', $arAdditionalParams['countWhere'])
					&& array_key_exists('joinModel', $arAdditionalParams['countWhere']))
				{
					$sJoinMdl = 'join' . $arAdditionalParams['countWhere']['joinModel'];
					$mdl->$sJoinMdl();
					$mdl->addWhereClause($arAdditionalParams['countWhere']['criteria']);
				}

				elseif (is_array($arAdditionalParams['countWhere']) && is_array($arAdditionalParams['countWhere'][0]))
				{
					$mdl->addWhereClause($arAdditionalParams['countWhere']);
				}
			}

			$this->_arAllIdsMdl = $mdl->fetchAll(array(
														'columns' => array(
																			new String(SQL_ESC
																						. $mdl->getTableName()
																						. SQL_ESC . '.' . SQL_ESC
																						. 'id' . SQL_ESC)
																		)
													));
			$this->_iTotalItems = count($this->_arAllIdsMdl);
			$this->_iPages = ceil($this->_iTotalItems / $iDsPerPage * 1.0);

			if ($this->_iPages == 1)
			{
				$this->_iCurrentPage = 1;
			}

			else
			{
				$iItemsCounter = $this->_iTotalItems;
				for ($iPage = $this->_iPages; $iPage > 0; $iPage--)
				{
					if ($iItemsCounter <= $this->_iLastEntryId)
					{
						$this->_iCurrentPage = (int)$iPage;
						break;
					}

					$iItemsCounter -= $iDsPerPage;
				}
			}

			$this->_iFirstEntryId = $arDataModels[0]->getId();

			if (array_filled($arAdditionalParams))
			{
				$this->_arAdditionalParams = $arAdditionalParams;
			}
		}

		public function getPages()
		{
			return $this->_iPages;
		}

		public function getCurrentPage()
		{
			return $this->_iCurrentPage;
		}

		public function getLastEntryId()
		{
			return $this->_iLastEntryId;
		}

		public function getFirstEntryId()
		{
			return $this->_iFirstEntryId;
		}

		public function getTotalItems()
		{
			return $this->_iTotalItems;
		}

		public function __toString()
		{
			$strBefore = '<nav>' . PHP_EOL
			. '<ul class="pagination">' . PHP_EOL;
			$strMid = '';
			$strAfter = '</ul>';

			//$this->_iItemsPerPage = $this->_iTotalItems * $this->_iPages / $this->_iPages;
			$iCntDataMdls = count($this->_arDataModels);
			$iCntAllIds = count($this->_arAllIdsMdl);
			for ($iPage = 1; $iPage <= $this->_iPages; $iPage++)
			{
				if ($this->_iCurrentPage === 1 && $iPage === 1)
				{

					$iFirstId = $this->_arDataModels[0]->getId();

					if ($iCntDataMdls < $this->_iItemsPerPage)
					{
						$iLastId = $this->_arDataModels[$iCntDataMdls - 1]->getId();
					}

					else
					{
						$iLastId = $this->_arDataModels[$this->_iItemsPerPage - 1]->getId();
					}
				}

				// 2nd comparsion: count is one more, so we leave it here also one more
				elseif ($iPage == $this->_iPages && $iCntAllIds < ((int)$this->_iItemsPerPage * $iPage))
				{
					$iFirstId = $this->_arAllIdsMdl[(int)$this->_iItemsPerPage * ($iPage - 1)]->getId();
					$iLastId = $this->_arAllIdsMdl[$iCntAllIds - 1]->getId();
				}

				else
				{
					$iFirstId = $this->_arAllIdsMdl[(int)$this->_iItemsPerPage * ($iPage - 1)]->getId();
					$iLastId = $this->_arAllIdsMdl[(int)$this->_iItemsPerPage * $iPage - 1]->getId();
				}

				if ($iPage === $this->_iCurrentPage)
				{
					$strMid .= '<li class="active"><a href="' . $this->_view->internalAnchor($this->_view,
																					$this->_sController,
																					$this->_sAction,
																					is_array($this->_arAdditionalParams) ?
																					array_merge(array('firstentryid' => $iFirstId, 'lastentryid' => $iLastId), $this->_arAdditionalParams) :
																					array('firstentryid' => $iFirstId, 'lastentryid' => $iLastId))
						. '">' . $iPage . '<span class="sr-only">(current)</span></a></li>' . PHP_EOL;
				}

				else
				{
					$strMid .= '<li><a href="' . $this->_view->internalAnchor($this->_view,
																		$this->_sController,
																		$this->_sAction,
																		is_array($this->_arAdditionalParams) ?
																		array_merge(array('firstentryid' => $iFirstId, 'lastentryid' => $iLastId), $this->_arAdditionalParams) :
																		array('firstentryid' => $iFirstId,
																			  'lastentryid' => $iLastId))
							. '">' . $iPage . '</a></li>' . PHP_EOL;
				}
			}

			if ($this->_iCurrentPage == 1)
			{
				$strBefore .= '<li class="disabled"><a href="#"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>' . PHP_EOL;

				if ($this->_iPages > 1)
				{
					$strAfter = '<li><a href="'
						. $this->_view->internalAnchor($this->_view,
												$this->_sController,
												$this->_sAction,
												is_array($this->_arAdditionalParams) ? array_merge(array('lastentryid' => $this->_iLastEntryId), $this->_arAdditionalParams) : array('lastentryid' => $this->_iLastEntryId))
						. '"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a>' . PHP_EOL . $strAfter;
				}

				else
				{
					$strAfter = '<li class="disabled"><a href="#"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a>' . PHP_EOL . $strAfter;
				}

			}

			elseif ($this->_iCurrentPage > 1 && $this->_iPages > $this->_iCurrentPage)
			{
				//$this->_iItemsPerPage = $this->_iTotalItems * $this->_iPages / $this->_iPages;
				$iPrevPageId = $this->_arAllIdsMdl[$this->_iItemsPerPage * $this->_iCurrentPage - $this->_iItemsPerPage - 1]->getId();
				$iNextPageId = $this->_arAllIdsMdl[$this->_iItemsPerPage * $this->_iCurrentPage]->getId();
				$strBefore .= '<li><a href="'
						. $this->_view->internalAnchor($this->_view,
												$this->_sController,
												$this->_sAction,
												is_array($this->_arAdditionalParams) ? array_merge(array('firstentryid' => $iPrevPageId), $this->_arAdditionalParams) : array('firstentryid' => $iPrevPageId))
						. '"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>' . PHP_EOL;
				$strAfter = '<li><a href="'
						. $this->_view->internalAnchor($this->_view,
												$this->_sController,
												$this->_sAction,
												is_array($this->_arAdditionalParams) ? array_merge(array('firstentryid' => $iNextPageId), $this->_arAdditionalParams) : array('firstentryid' => $iNextPageId))
						. '"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a></li>' . PHP_EOL . $strAfter;
			}

			else
			{
				$iPrevPageId = $this->_arAllIdsMdl[$this->_iItemsPerPage * $this->_iCurrentPage - $this->_iItemsPerPage - 1]->getId();
				//$this->_iItemsPerPage = $this->_iTotalItems * $this->_iPages / $this->_iPages;
				$strBefore .= '<li><a href="'
						. $this->_view->internalAnchor($this->_view,
												$this->_sController,
												$this->_sAction,
												is_array($this->_arAdditionalParams) ? array_merge(array('firstentryid' => $iPrevPageId), $this->_arAdditionalParams) : array('firstentryid' => $iPrevPageId))
						. '"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>' . PHP_EOL;
				$strAfter = '<li class="disabled"><a href="#"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a></li>' . PHP_EOL . $strAfter;
			}

			return $strBefore . $strMid . $strAfter;
		}
	}
}
?>
