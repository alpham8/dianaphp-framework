<?php
class CultureInfo
{
    // http://publib.boulder.ibm.com/infocenter/forms/v3r5m0/index.jsp?topic=/com.ibm.form.designer.locales.doc/i_xfdl_r_formats_ar_BH.html
    protected static $_arDateFormats = array('ar-AE' => array('numeric' => '%G%m%d', 'short' => '%sd/%sm/%G', 'medium' => '%d/%m/%G', 'long' => '%sd %B, %G', 'full' => '%A, %sd %B, %G'),
					     'ar-BH' => array('numeric' => '%G%m%d', 'short' => '%sd/%sm/%G', 'medium' => '%d/%m/%G', 'long' => '%sd %B , %G', 'full' => '%A %sd %B, %G'),
					     'ar-DZ' => array('numeric' => '%G%m%d', 'short' => '%sd/%sm/%G', 'medium' => '%d/%m/%G', 'long' => '%sd %B, %G', 'full' => '%A, %sd %B, %G'),
					     'ar-EG' => array('numeric' => '%G%m%d', 'short' => '$sd/%sm/%G', 'medium' => '%d/%m/%G', 'long' => '%sd %B,  %G'),
					     'ar-IQ' => array('numeric' => '%G%m%d', 'short' => '%sd/%sm/%G', 'medium' => '%d/%m/%G', 'long' => '%sd %B, %G', 'full' => '%A, %sd %B, %G'),
					     'ar-JO' => array('numeric' => '%G%m%d', 'short' => '%sd/%sm/%G', 'medium' => '%d/%m/%G', 'long' => '%sd %B, %G', 'full' => '%A, %sd %B, %G'),
					     'ar-KW' => array('numeric' => '%G%m%d', 'short' => '%sd/%sm/%G', 'medium' => '%d/%m/%G', 'long' => '%sd %B,  %G', 'full' => '%A, %sd %B, %G'),
					     'ar-LB' => array('numeric' => '%G%m%d', 'short' => '%sd/%sm/%G', 'medium' => '%d/%m/%G', 'long' => '%sd %B, %G', 'full' => '%A, %sd %, %G'),
					     'ar-LY' => array('numeric' => '%G%m%d', 'short' => '%sd/%sm/%G', 'medium' => '%d/%m/%G', 'long' => '%sd %B, %G', 'full' => '%A, %sd %B, %G'),
					     'ar-MA' => array('numeric' => '%G%m%d', 'short' => '%sd/%sm/%G', 'medium' => '%d/%m/%G', 'long' => '%sd %B, %G', 'full' => '%A, %sd %B, %G'),
					     'ar-OM' => array('numeric' => '%G%m%d', 'short' => '%sd/%sm/%G', 'medium' => '%d/%m/%G', 'long' => '%sd %B, %G', 'full' => '%A, %sd %B, %G'),
					     'ar-QA' => array('numeric' => '%G%m%d', 'short' => '%sd/%sm/%G', 'medium' => '%d/%m/%G', 'long' => '%sd %B, %G', 'full' => '%A, %sd %B, %G'),
					     'ar-SA' => array('numeric' => '%G%m%d', 'short' => '%sd/%sm/%G', 'medium' => '%d/%m/%G', 'long' => '%sd %B, %G', 'full' => '%A, %sd %B, %G'),
					     'ar-SD' => array('numeric' => '%G%m%d', 'short' => '%sd/%sm/%G', 'medium' => '%d/%m/%G', 'long' => '%d %B, %G', 'full' => '%A, $sd %B, %G'),
					     'ar-SY' => array('numeric' => '%G%m%d', 'short' => '%sd/%sm/%G', 'medium' => '%d/%m/%G', 'long' => '%sd %B, %G', 'full' => '%A, %sd %B, %G'),
					     'ar-TN' => array('numeric' => '%G%m%d', 'short' => '%sd/%m/%G', 'medium' => '%d/%m/%G', 'long' => '%sd %B, %G', 'full' => '%A, %sd %B, %G'),
					     'ar-YE' => array('numeric' => '%G%m%d', 'short' => '%sd/%sm/%G', 'medium' => '%d/%m/%G', 'long' => '%sd %B, %G', 'full' => '%A, %sd %B, %G'),
					     'zh-Hans-CN' => array('numeric' => '%G%m%d', 'short' => '%g-%sm-%sd', 'medium' => '%G-%sm-%sd', 'long' => '%G\'年\'%sm\'月\'%sd\'日\'', 'full' => '%G\'年\'%sm\'月\'%sd\'日\'%A'),
					     'zh-Hans-SG' => array('numeric' => '%G%m%d', 'short' => '%d/%m/%g', 'medium' => '%d-%b-%g', 'long' => '%d %b %G', 'full' => '%d %B %G'),
					     'zh-Hant-HK' => array('numeric' => '%G%m%d', 'short' => '%g\'年\'%sm\'月\'%sd\'日\'', 'medium' => '%G\'年\'%sm\'月\'%sd\'日\'', 'long' => '%G\'年\'%sm\'月\'%sd\'日\'', 'full' => '%G\'年\'%sm\'月\'%sd\'日\' %A'),
					     'zh-Hant-TW' => array('numeric' => '%G%m%d', 'short' => '%G/%sm/%sd', 'medium' => '%G/%sm/%sd', 'long' => '%G\'年\'%sm\'月\'%sd\'日\'', 'full' => '%G\'年\'%sm\'月\'d\'日\'%A'),
					     'hr-HR' => array('numeric' => '%G%m%d', 'short' => '%G.%m.%d', 'medium' => '%G.%m.%d', 'long' => '%G. %B %d', 'full' => '%G. %B %d'),
					     'cs-CZ' => array('numeric' => '%G%m%d', 'short' => '%sd.%sm.%g', 'medium' => '%sd.%sm.%G', 'long' => '%sd. %B %G', 'full' => '%A, %sd. %B %G'),
					     'da-DK' => array('numeric' => '%G%m%d', 'short' => '%d/%m/%g', 'medium' => '%d/%m/%G', 'long' => '%sd. %B %G', 'full' => '%A den %sd. %B %G'),
					     'nl-BE' => array('numeric' => '%G%m%d', 'short' => '%sd/%m/%g', 'medium' => '%sd-%b-%g', 'long' => '%sd %B %G', 'full' => '%A %sd %B %G'),
					     'nl-NL' => array('numeric' => '%G%m%d', 'short' => '%d-%m-%g', 'medium' => '%sd %b %G', 'long' => '%sd %B %G', 'full' => '%A %sd %B %G'),
					     'en-AU' => array('numeric' => '%G%m%d', 'short' => '%sd/%sm/%g', 'medium' => '%d/%m/%G', 'long' => '%sd %B %G', 'full' => '%A, %sd %B %G'),
					     'en-BE' => array('numeric' => '%G%m%d', 'short' => '%d/%m/%g', 'medium' => '%d %b %G', 'long' => '%a %sd %b %G', 'full' => '%A %sd %B %G'),
					     'en-CA' => array('numeric' => '%G%m%d', 'short' => '%G-%m-%d', 'medium' => '%sd %b %G', 'long' => '%B %sd, %G', 'full' => '%A, %B %sd, %G'),
					     'en-HK' => array('numeric' => '%G%m%d', 'short' => '%d/%m/%G', 'medium' => '%sd %b %G', 'long' => '%sd %B %G', 'full' => '%A, %sd %B %G'),
					     'en-IN' => array('numeric' => '%G%m%d', 'short' => '%d/%m/%g', 'medium' => '%d-%b-%g', 'long' => '%sd %B %G', 'full' => '%A %sd %B %G'),
					     'en-IE' => array('numeric' => '%G%m%d', 'short' => '%d/%m/%G', 'medium' => '%sd %b %G', 'long' => '%sd %B %G', 'full' => '%A %s %B %G'),
					     'en-NZ' => array('numeric' => '%G%m%d', 'short' => '%sd/%m/%g', 'medium' => '%sd/%m/%G', 'long' => '%sd %B %G', 'full' => '%A, %sd %B %G'),
					     'en-PH' => array('numeric' => '%G%m%d', 'short' => '%sm/%sd/%g', 'medium' => '%m %sd, %g', 'long' => '%B %sd, %G', 'full' => '%A, %B %sd, %G'),
					     'en-SG' => array('numeric' => '%G%m%d', 'short' => '%sm/%sd/%g', 'medium' => '%b %sd, %G', 'long' => '%B %sd, %G', 'full' => '%A, %B %sd, %G'),
					     'en-ZA' => array('numeric' => '%G%m%d', 'short' => '%G/%m/%d', 'medium' => '%d %b %G', 'long' => '%d %B %G', 'full' => '%A %d %B %G'),
					     'en-GB' => array('numeric' => '%G%m%d', 'short' => '%d/%m/%G', 'medium' => '%sd %b %G', 'long' => '%sd %B %G', 'full' => '%A, %sd %B %G'),
					     'en-US' => array('numeric' => '%G%m%d', 'short' => '%G-%m-%d', 'medium' => '%sd %b %G', 'long' => '%B %sd, %G', 'full' => '%A, %B %sd, %G'),
					     'fi-FI' => array('numeric' => '%G%m%d', 'short' => '%sd.%sm.%G', 'medium' => '%sd.%sm.%G', 'long' => '%sd. %bta %G', 'full' => '%Ana %sd. %Bta %G'),
					     'fr-BE' => array('numeric' => '%G%m%d', 'short' => '%sd/%m/%g', 'medium' => '%d-%b-%g', 'long' => '%sd %B %G', 'full' => '%A %sd %B %G'),
					     'fr-CA' => array('numeric' => '%G%m%d', 'short' => '%g-%m-%d', 'medium' => '%g-%m-%d', 'long' => '%sd %B %G', 'full' => '%A %sd %B %G'),
					     'fr-FR' => array('numeric' => '%G%m%d', 'short' => '%d/%m/%g', 'medium' => '%sd %b %g', 'long' => '%sd %B %G', 'full' => '%A %sd %B %G'),
					     'fr-LU' => array('numeric' => '%G%m%d', 'short' => '%d/%m/%g', 'medium' => '%sd %b %g', 'long' => '%sd %B %G', 'full' => '%A %sd %B %G'),
					     'fr-CH' => array('numeric' => '%G%m%d', 'short' => '%d.%m.%g', 'medium' => '%sd %b %g', 'long' => '%sd %B %G', 'full' => '%A, %sd %B %G'),
					     '' => array('numeric' => '', 'short' => '', 'medium' => '', 'long' => '', 'full' => ''),
					     '' => array('numeric' => '', 'short' => '', 'medium' => '', 'long' => '', 'full' => ''),
					     '' => array('numeric' => '', 'short' => '', 'medium' => '', 'long' => '', 'full' => ''),
					     '' => array('numeric' => '', 'short' => '', 'medium' => '', 'long' => '', 'full' => ''),
					     '' => array('numeric' => '', 'short' => '', 'medium' => '', 'long' => '', 'full' => ''),
					     '' => array('numeric' => '', 'short' => '', 'medium' => '', 'long' => '', 'full' => ''),
					    );
    protected $_arDateFormat;

    public function __construct($sLang)
    {
	if ($sLang->matches('/[a-z]{2}/'))
	{

	}
	elseif ($sLang->matches('/[a-z]{2}.{1}[A-Z]{2})/'))
	{
	    $sPHP = new String(file_get_contents(EPHP_CORE . 'Util/Intl/' . $sLang . '.php'));
	}
	$this->_arDateFormat = $arDateFormat;
    }
}
?>
