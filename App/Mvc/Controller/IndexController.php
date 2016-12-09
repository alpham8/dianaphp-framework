<?php
namespace App\Mvc\Controller
{
    use Diana\Core\Std\String;
    use Diana\Core\Mvc\BaseController;
    use Diana\Core\Persistence\Sql\BaseModel;
    use App\Src\BootstrapPaginator;

    class IndexController extends BaseController
    {
        public function __construct()
        {
            // needs to be called!
            parent::__construct();
            // set our template file here
            $this->setTemplate(new String(DIANA_TEMPLATES . 'standard.phtml'));
        }

        public function index()
        {
            // nothing to do here. Just for example.
            // maybe you could fetch some request paramaters with
            // $this->request->getParam(new String('mySuperCoolParam'));
        }

        /**
         * An example function which makes use of the BootstrapPaginator class.
         * Use this, if you want to paginate your view.
         *
         * @param string|array $sModel
         * @param String $sController the controller name as String instance
         * @param String $sAction the action name as String instance
         * @param array|null $arAdditonalParams the additional params if needed.
         *
         * @return void
         */
        protected function paginateView(
                                         $sModel,
                                         String $sController,
                                         String $sAction, array $arAdditonalParams = null
                                         )
        {
            $sFirstEntryId = $this->request->getParam(new String('firstentryid'));
            $sLastEntryId = $this->request->getParam(new String('lastentryid'));

            $mdl = self::crtBasePaginationMdl($sModel);

            if (
                $sLastEntryId !== null
                && $sLastEntryId instanceof String
                && !$sLastEntryId->isEmpty()
                && $sFirstEntryId === null
                ) {
                $mdl->addWhereClause(array(
                    SQL_ESC . $mdl->getTableName() . SQL_ESC . '.'
                    . SQL_ESC . 'id' . SQL_ESC => array(
                        BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_GREATER_THAN_EQUALS,
                            BaseModel::CRITERIA_VALUE => (int)$sLastEntryId->__toString()
                        )
                ));
            } elseif (
                      $sLastEntryId === null
                      && $sFirstEntryId !== null
                      && $sFirstEntryId instanceof String
                      && !$sFirstEntryId->isEmpty()
                      ) {
                $mdl->addWhereClause(array(
                    SQL_ESC . $mdl->getTableName() . SQL_ESC . '.'
                    . SQL_ESC . 'id' . SQL_ESC => array(
                        BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_GREATER_THAN_EQUALS,
                        BaseModel::CRITERIA_VALUE => (int)$sFirstEntryId->__toString()
                        )
                ));
            } elseif (
                      $sLastEntryId !== null
                      && $sLastEntryId instanceof String
                      && !$sLastEntryId->isEmpty()
                      && $sFirstEntryId instanceof String
                      && !$sFirstEntryId->isEmpty()
                      ) {
                $mdl->addWhereClause(array(
                    SQL_ESC . $mdl->getTableName() . SQL_ESC . '.'
                    . SQL_ESC . 'id' . SQL_ESC => array(
                        BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_GREATER_THAN_EQUALS,
                        BaseModel::CRITERIA_VALUE => (int)$sFirstEntryId->__toString()
                        )
                ));
            } else {
                $mdl->addWhereClause(array(
                    SQL_ESC . $mdl->getTableName() . SQL_ESC . '.'
                    . SQL_ESC . 'id' . SQL_ESC => array(
                        BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_GREATER_THAN_EQUALS,
                        BaseModel::CRITERIA_VALUE => 1
                        )
                ));
            }

            $this->view->arEntries = $mdl->fetchAll(array('limit' => self::PAGINATION_ITEMS));

            if ($this->view->arEntries) {
                $mdl = self::crtBasePaginationMdl($sModel);
                $this->view->paginator = new BootstrapPaginator(
                                                $mdl,
                                                $this->view->arEntries,
                                                self::PAGINATION_ITEMS,
                                                $this->view,
                                                $sController,
                                                $sAction,
                                                $arAdditonalParams
                                            );
            }
        }


        /**
         * A helper method which does some basic stuff for the paginator
         * in order to find the right model.
         *
         * @param string|array the model to be look for. If array, set a key 'model' to that array.
         *
         * @return BaseModel the found model.
         */
        private static function crtBasePaginationMdl(&$sModel)
        {
            if (is_array($sModel)) {
                $sModelEsc = "App\\Mvc\\Model\\" . $sModel['model'];
                $mdl = new $sModelEsc();

                if (array_key_exists('joinModel', $sModel)) {
                    foreach ($sModel['joinModel'] as $sModelJoin) {
                        $sJoin = 'join' . $sModelJoin->ucFirst();
                        $mdl->$sJoin();
                    }
                }

                if (array_key_exists('whereClause', $sModel)) {
                    foreach ($sModel['whereClause'] as $arWhere) {
                        $mdl->addWhereClause($arWhere);
                    }
                }

                if (array_key_exists('orderBy', $sModel)) {
                    $mdl->orderBy($sModel['orderBy'][0], $sModel['orderBy'][1], BaseModel::ORDER_DESC);
                }
            } else {
                $sModelLocal = "App\\Mvc\\Model\\" . $sModel;
                $mdl = new $sModelLocal();
            }

            return $mdl;
        }
    }
}
