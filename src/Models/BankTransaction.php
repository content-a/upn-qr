<?php

namespace UpnQr\Models;

class BankTransaction {
    private $name;

    private $reference;

    private $amount;

    // NAMEN
    private $purpose;

    public function __construct($name, $reference, $amount){
        $this->setName($name);
        $this->setReference($reference);
        $this->setAmount($amount);
        $this->purpose = null;
    }

    public function setName($value){
        $value = strtoupper($value);
        $this->name = $value;
    }

    public function setReference($value){
        $value = strtoupper($value);

        // Remove whitespaces and minus.
        $value = str_replace(" ", "", $value);
        $value = str_replace("-", "", $value);

        $this->reference = $value;
    }

    public function setAmount($value){
        $value = (float) $value;
        $this->amount = $value;
    }

    public function setPurpose($value){
        $this->purpose = $value;
    }

    public function getName(){
        return $this->name;
    }

    public function getFirstName(){
        $names = explode(" ", $this->name);
        return $names[0];
    }

    public function getLastName(){
        $names = explode(" ", $this->name);
        return $names[1];
    }

    public function getReference(){
        return $this->reference;
    }

    public function getReferenceCode(){
        return substr($this->reference, 0, 4);
    }

    public function getReferenceNumber(){
        return substr($this->reference, 4);
    }


    /**
     * Return amount.
     *
     * @return  float
     */
    public function getAmount(){
        return $this->amount;
    }

    public function getPurpose(){
        return $this->purpose;
    }
}
