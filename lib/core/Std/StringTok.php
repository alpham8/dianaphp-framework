<?php
class StringTokenizer
{
    private $_str;
    private $_chToken;
    private $_iPosToken = 0;
    private $_bInit;

    public function __construct($str, $chToken)
    {
        if (empty($str) && empty($chToken))
        {
            throw new Exception('String and the token char variables cannot be empty.');
        }
        elseif(empty($chToken) && !empty($str))
        {
            throw new Exception('Missing parameter: Token char cannot be empty.');
        }
        elseif(!empty($chToken) && empty($str))
        {
            throw new Exception('Missing parameter: String cannot be empty.');
        }
        elseif(!empty($chToken) && !empty($str) && is_string($str) && strlen($chToken) >= 0)
        {
            $this->_str = $str;
            $this->_chToken = $chToken;
            $this->_bInit = true;
        }
        else
        {
            throw new Exception('TypeError: Illegal call to __construct from class StringTokenizer.');
        }
    }

    public function next()
    {
        if ($this->_iPosToken === false)
        {
            return false;
        }

        if ($this->_bInit === true && (strlen($this->_str) - 1) > $this->_iPosToken)
        {
            $iCh1stPos = strpos($this->_str, $this->_chToken, $this->_iPosToken) + 1;
            $this->_iPosToken = $iCh1stPos;
            $this->_bInit = false;
            return substr($this->_str, 0, $this->_iPosToken - 1);
        }
        elseif ($this->_bInit === false && (strlen($this->_str)-1 ) > $this->_iPosToken)
        {
            $iCh1stPos = $this->_iPosToken;

            $iCh2ndPos = strpos($this->_str, $this->_chToken, $this->_iPosToken);
            if ($iCh2ndPos === FALSE) // You can chuck this if block. I put a echo here and it never executed.
            {
                $this->_iPosToken = false;
                return substr($this->_str, $iCh1stPos);
            }
            else
            {
                $this->_iPosToken = $iCh2ndPos + 1;
                return substr($this->_str, $iCh1stPos, $iCh2ndPos - $iCh1stPos);
            }
        }
        else
        {
            return false;
        }
    }

    public function hasNext()
    {
        return strpos($this->_str, $this->chToken, $this->_iPosToken) === false ? false : true;
    }
}
$strText = 'Hello;this;is;a;text;';
$tokenizer = new StringTokenizer($strText, ';');
$tok = $tokenizer->next();
while ($tok !== false)
{
    echo '**' . $tok . '**' . PHP_EOL;
    $tok = $tokenizer->next();
}
exit(0);
?>
