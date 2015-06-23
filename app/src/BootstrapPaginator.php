<?php
class BootstrapPaginator
{
	protected $_mdl;
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

	public function __construct(BaseModel $mdl, &$arDataModels, $iDsPerPage, View $view, String $sController, String $sAction, array $arAdditionalParams = null)
	{
		$this->_iLastEntryId = $arDataModels[count($arDataModels) - 1]->getId();
		$this->_iTotalItems = $iTotalItems;
		$this->_iItemsPerPage = $iDsPerPage;
		$this->_view = $view;
		$this->_sController = $sController;
		$this->_sAction = $sAction;
		$this->_mdl = $mdl;

		if (isset($arAdditionalParams) && is_array($arAdditionalParams) && array_key_exists('countWhere', $arAdditionalParams))
		{
			if (is_array($arAdditionalParams['countWhere'])
				&& array_key_exists('criteria', $arAdditionalParams['countWhere'])
				&& array_key_exists('joinModel', $arAdditionalParams['countWhere']))
			{
				$sJoinMdl = 'join' . $arAdditionalParams['countWhere']['joinModel'];
				$this->_mdl->$sJoinMdl();
				$this->_mdl->addWhereClause($arAdditionalParams['countWhere']['criteria']);
			}

			elseif (is_array($arAdditionalParams['countWhere']) && is_array($arAdditionalParams['countWhere'][0]))
			{
				$this->_mdl->addWhereClause($arAdditionalParams['countWhere']);
			}
		}

		$this->_mdl->fetch(array(
								  'aggregate' => array(
													   '*' => 'count'
													   )
								  ));
		$iTotalItems = $this->_mdl->getAggregate();
		$this->_iPages = ceil($iTotalItems / $iDsPerPage * 1.0);
		$iItemsCounter = $iTotalItems;

		for ($iPage = $this->_iPages; $iPage > 0; $iPage--)
		{
			if ($iItemsCounter <= $this->_iLastEntryId)
			{
				$this->_iCurrentPage = (int)$iPage;
				break;
			}

			$iItemsCounter -= $iDsPerPage;
		}

		$this->_iFirstEntryId = $arDataModels[0]->getId();
		$this->_iFirstPageId = $this->_iFirstEntryId / $this->_iCurrentPage;

		if ($arAdditionalParams != null && is_array($arAdditionalParams) && count($arAdditionalParams) > 0)
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

	public function getFirstPageId()
	{
		return $this->_iFirstPageId;
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
		for ($iPage = 1; $iPage <= $this->_iPages; $iPage++)
		{
			$iFirstId = $this->_iCurrentPage === 1 && $iPage === 1 ? 1 : (int)$this->_iItemsPerPage * ($iPage - 1) + 1;
			$iLastId = $iFirstId + $this->_iItemsPerPage - 1;

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
			$strBefore .= '<li><a href="'
					. $this->_view->internalAnchor($this->_view,
											$this->_sController,
											$this->_sAction,
											is_array($this->_arAdditionalParams) ? array_merge(array('firstentryid' => $this->_iFirstEntryId - $this->_iItemsPerPage), $this->_arAdditionalParams) : array('firstentryid' => $this->_iFirstEntryId - $this->_iItemsPerPage))
					. '"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>' . PHP_EOL;
			$strAfter = '<li><a href="'
					. $this->_view->internalAnchor($this->_view,
											$this->_sController,
											$this->_sAction,
											is_array($this->_arAdditionalParams) ? array_merge(array('lastentryid' => $this->_iLastEntryId), $this->_arAdditionalParams) : array('lastentryid' => $this->_iLastEntryId))
					. '"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a></li>' . PHP_EOL . $strAfter;
		}

		else
		{
			//$this->_iItemsPerPage = $this->_iTotalItems * $this->_iPages / $this->_iPages;
			$strBefore .= '<li><a href="'
					. $this->_view->internalAnchor($this->_view,
											$this->_sController,
											$this->_sAction,
											is_array($this->_arAdditionalParams) ? array_merge(array('firstentryid' => $this->_iFirstEntryId - $this->_iItemsPerPage), $this->_arAdditionalParams) : array('firstentryid' => $this->_iFirstEntryId - $this->_iItemsPerPage))
					. '"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>' . PHP_EOL;
			$strAfter = '<li class="disabled"><a href="#"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a></li>' . PHP_EOL . $strAfter;
		}

		return $strBefore . $strMid . $strAfter;
	}
}
?>
