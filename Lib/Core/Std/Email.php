<?php
namespace Diana\Core\Std
{
    use Diana\Core\Std\StringType;

    class Email
    {
        const M_TYPE_TEXT = 'text';
        const M_TYPE_HTML = 'text/html';
        const NO_SUBJECT = '<<No Subjcet>>';

        protected $sType;
        protected $sFrom;
        protected $sRcpt;
        protected $arCC;
        protected $arBCC;
        protected $sReplyTo;
        protected $sSubject;
        protected $sBody;

        public function __construct()
        {
            $this->sType = new StringType(self::M_TYPE_TEXT);
        }

        public function setFrom(StringType $sFrom)
        {
            $this->sFrom = $sFrom;
        }

        public function setRcpt(StringType $sRcpt)
        {
            $this->sRcpt = $sRcpt;
        }

        public function addCC(StringType $sCC)
        {
            $this->arBCC[] = $sCC;
        }

        public function addBCC(StringType $sBCC)
        {
            $this->arBCC[] = $sBCC;
        }

        public function setSubject(StringType $sSubject)
        {
            $this->sSubject = $sSubject;
        }

        public function getSubject()
        {
            return isset($this->sSubject)
                && checkstring($this->sSubject)
                    ? $this->sSubject
                    : new StringType(self::NO_SUBJECT);
        }

        public function setBody(StringType $sBody)
        {
            $this->sBody = $sBody;
        }

        public function setReplyTo(StringType $sReplyTo)
        {
            $this->sReplyTo = $sReplyTo;
        }

        protected function check()
        {
            return checkstring($this->sFrom)
                && checkstring($this->sRcpt)
                && checkstring($this->sBody);
        }

        public function send()
        {
            if ($this->check()) {
                $sHeaders = "MIME-Version: 1.0\r\n";

                if ($this->sType->equals(self::M_TYPE_TEXT)) {
                    $sHeaders .= "Content-Type:text/plain;charset=UTF-8\r\n";
                } else {
                    $sHeaders .= "Content-Type:text/html;charset=UTF-8\r\n";
                }

                $sHeaders .= 'From: ' . $this->sFrom . "\r\n";

                if (checkstring($this->sReplyTo)) {
                    $sHeaders .= 'Reply-To: ' . $this->sReplyTo . "\r\n";
                }

                if (array_filled($this->arCC)) {
                    $sHeaders .= 'CC: ' . implode(', ', $this->arCC) . "\r\n";
                }

                if (array_filled($this->arBCC)) {
                    $sHeaders .= 'BCC: ' . implode(', ', $this->arBCC) . "\r\n";
                }

                mail(
                    $this->sRcpt->__toString(),
                    $this->getSubject()->__toString(),
                    $this->sBody->__toString(), $sHeaders
                );

                return true;
            }

            return false;
        }
    }
}
