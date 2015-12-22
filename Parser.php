<?php

class Parser
{
    protected $lua = array();
    protected $position = 0;
    protected $lines = 0;
    protected $data = array();

    public function __construct($input)
    {
        if(is_array($input)) {
            $this->lua = $input;
        } elseif(is_string($input)) {
            if(is_file($input)) {
                $this->lua = file($input);
            } else {
                $this->lua = explode("\n", $input);
            }
        }
        if(is_array($this->lua)) {
            $this->lines = count($this->lua);
        }

        if($this->lines <= 1) {
            throw new Exception('Input did not validate as array');
        }
        $this->parse();
        return $this;
    }

    public function toArray()
    {
        return $this->data;
    }

    protected function parse()
    {
        $this->data = $this->parser();
        unset($this->lua);
    }

    protected function parser(&$position = false)
    {
        if($position == false) {
            $position = &$this->position;
        }
        $data = array();
        $stop = false;
        $j = 0;
        if ($position < $this->lines) {
          for ($i = $position; $stop == false;) {
            if ($i >= $this->lines) {
                $stop = true;
                break;
            }

            //$strs = explode("=", utf8_decode($this->lua[$i]));
            $strs = explode("=", $this->lua[$i]);

            if (isset($strs[1]) && trim($strs[1]) == "{") {
              $i++;
              $data[$this->arrayId(trim($strs[0]))] = $this->parser($i);
            } elseif (trim($strs[0]) == "}" || trim($strs[0]) == "},") {
              $i++;
              $stop = true;
            } elseif(trim($strs[0]) == "{") {
                $i++;
                $data[$j] = $this->parser($i);
                $j++;
            } else {
              $i++;
              if (strlen($this->arrayId(trim($strs[0]))) > 0 && strlen($strs[1]) > 0) {
                $data[$this->arrayId(trim($strs[0]))] = $this->trimValue($strs[1]);
              }
            } 
          }
        }
        $position = $i;
        return $data;
    }

    protected function trimValue($string)
    {
        $string = trim($string);
        $find = array(
            '/\["/',
            '/\"]/',
            '/,/',
            '/"/'
        );
        $string = preg_replace($find, '', $string);

        if ($string =='false') {
            $string = false;
        }
        if ($string =='true') {
            $string = true;
        }

        return $string;
    }

    protected function arrayId($string)
    {
        $id = sscanf($string, "[%d]");
        if (strlen($id[0])>0) {
          return $id[0];
        } else {
          if (substr($string,0,1)=="[") {
            $string  = substr($string,1,strlen($string));
          }
          if (substr($string,0,1)=="\"") {
            $string  = substr($string,1,strlen($string));
          }
          if (substr($string,-1,1)=="]") {
            $string  = substr($string,0,strlen($string)-1);
          }
          if (substr($string,-1,1)=="\"") {
            $string  = substr($string,0,strlen($string)-1);
          }
          return $string;
        } 
    }
}