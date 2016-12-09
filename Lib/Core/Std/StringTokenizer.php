<?php
class StringTokenizer implements Iterator
{
    private $tokens = [];
    private $iPos = -1;

    public function __construct($string, $chToken)
    {
        $this->tokens = explode($chToken, $string);
    }

    public function rewind()
    {
        return $this->tokens[$this->iPos];
    }

    public function next()
    {
        $this->iPos++;
        return isset($this->tokens[$this->iPos]) ? $this->tokens[$this->iPos] : false;
    }

    public function current()
    {
        return $this->tokens[$this->iPos];
    }

    public function key()
    {
        return $this->iPos;
    }

    public function valid()
    {
        return isset($this->tokens[$this->iPos]);
    }
}
