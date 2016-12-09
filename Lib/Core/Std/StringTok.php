<?php
class StringTokenizer
{
    private $str;
    private $chToken;
    private $iPosToken = 0;
    private $bInit;

    public function __construct($str, $chToken)
    {
        if (
            empty($str)
            && empty($chToken)
        ) {
            throw new \Exception('String and the token char variables cannot be empty.');
        } elseif (
            empty($chToken)
            && !empty($str)
        ) {
            throw new \Exception('Missing parameter: Token char cannot be empty.');
        } elseif (
            !empty($chToken)
            && empty($str)
        ) {
            throw new \Exception('Missing parameter: String cannot be empty.');
        } elseif (
            !empty($chToken)
            && !empty($str)
            && is_string($str)
            && strlen($chToken) >= 0
        ) {
            $this->str = $str;
            $this->chToken = $chToken;
            $this->bInit = true;
        } else {
            throw new \Exception(
                'TypeError: Illegal call to __construct from class StringTokenizer.'
            );
        }
    }

    public function next()
    {
        if ($this->iPosToken === false) {
            return false;
        }

        if (
            $this->bInit === true
            && (strlen($this->str) - 1) > $this->iPosToken
        ) {
            $iCh1stPos = strpos($this->str, $this->chToken, $this->iPosToken) + 1;
            $this->iPosToken = $iCh1stPos;
            $this->bInit = false;

            return substr($this->str, 0, $this->iPosToken - 1);
        } elseif (
            $this->bInit === false
            && (strlen($this->str)-1) > $this->iPosToken
        ) {
            $iCh1stPos = $this->iPosToken;

            $iCh2ndPos = strpos($this->str, $this->chToken, $this->iPosToken);

            if ($iCh2ndPos === false) {
                $this->iPosToken = false;
                return substr($this->str, $iCh1stPos);
            } else {
                $this->iPosToken = $iCh2ndPos + 1;
                return substr($this->str, $iCh1stPos, $iCh2ndPos - $iCh1stPos);
            }
        } else {
            return false;
        }
    }

    public function hasNext()
    {
        return strpos($this->str, $this->chToken, $this->iPosToken) === false ? false : true;
    }
}
$strText = 'Hello;this;is;a;text;';
$tokenizer = new StringTokenizer($strText, ';');
$tok = $tokenizer->next();
while ($tok !== false) {
    echo '**' . $tok . '**' . PHP_EOL;
    $tok = $tokenizer->next();
}
exit(0);
