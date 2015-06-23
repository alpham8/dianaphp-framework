<?php
require_once_file(ROOT_PATH . 'app/src/Clanscript20Controller.php');
require_once_file(EPHP_MODEL . 'JsonModel.php');
require_once_file(EPHP_MODEL . 'ProfileModel.php');

class AjaxController extends Clanscript20Controller
{
	protected $_jsonMdl;

	public function __construct()
	{
		parent::__construct();
		$this->_view->setTemplate(null);
		$this->_jsonMdl = new JsonModel();
	}

	public function preExec()
	{
		$bAllowed = parent::preExec();

		if ($bAllowed)
		{
			$this->_response->setDatatype(new String('json'));
		}

		return $bAllowed;
	}

	public function registerform()
	{
		// TODO implementation...
	}

	public function getCaptcha()
	{
		header('Content-Type: image/png');
		//$sCharset = 'ABCDEFGHJKLMNPRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
		$sCharset = 'ABCDEFGHJKLMNPRSTUVWXYZ23456789';
		$sCaptchaText = '';
		$len = rand(3, 10);
		$arTtfs = array(ROOT_PATH . 'app/src/gloriahallelujah.ttf',
						ROOT_PATH . 'app/src/Pacifico.ttf',
						ROOT_PATH . 'app/src/SigmarOne.ttf');

		while(strlen($sCaptchaText) < $len)
		{
			$sCaptchaText .= substr($sCharset,(rand()%(strlen($sCharset))),1);
		}

		$img = imagecreate(300, 100);
		$background = imagecolorallocate( $img, 0, 0, 255 );

		$text_colour = imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255));
		$iFontSize = rand(12, 25);
		$iAngle = rand(0, 5);
		$iXFontPos = rand(5, 30);
		$iYFontPos = 50;

		for ($j = 0; $j < strlen($sCaptchaText); $j++)
		{
			imagettftext($img, $iFontSize, $iAngle, $iXFontPos, $iYFontPos, $text_colour, $arTtfs[rand(0, 2)], substr($sCaptchaText, $j, 1));
			$iXFontPos += $iFontSize + 2;
			$text_colour = imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255));
		}

		$iLines = rand(3, 10);

		for ($i = 0; $i < $iLines; $i++)
		{
			$line_colour = imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255));
			imageline($img, rand(0, 30), rand(0, 100), rand(30, 200), rand(0, 100), $line_colour);
		}

		imagepng($img);
		imagedestroy($img);
		$this->_session->set(new String('sCaptcha'), new String($sCaptchaText));
		exit(0);
	}

	public function _errorHandler(Exception $ex)
	{
		$sError = new String('An Exception has been thrown: ('
			. $ex->getCode() . ') '
			. $ex->getMessage()
			. ' in file ' . $ex->getFile()
			. ' on Line ' . $ex->getLine()
		);
		$this->_jsonMdl->addHeader(new String('error'), $sError);
		$this->_response->sendJson($this->_jsonMdl->getHeader(), $this->_jsonMdl->getBody());
	}
}
?>
