<?php

namespace app\modules\soap\models\etp\response;

class Department
{
    /** @var string */
    public $Name;

    /** @var string */
    public $Code;

    /** @var string */
    public $Inn;

    /** @var string */
    public $Ogrn;

    /** @var string */
    public $RegDate;

    /** @var string */
    public $SystemCode;

    public function __construct(array $department)
    {
        $this->Name = $department['Name']  ?? new nillable();
        $this->Code = $department['Code'] ?? new nillable();
        $this->Inn = $department['Inn'] ?? new nillable();
        $this->Ogrn = $department['Ogrn'] ?? new nillable();
        $this->RegDate = $department['RegDate'] ?? new nillable();
        $this->SystemCode = $department['SystemCode'] ?? new nillable();
    }

    public function toArray()
    {
        return [
            'Name' => $this->Name,
            'Code' => $this->Code,
            'Inn' => $this->Inn,
            'Ogrn' => $this->Ogrn,
            'RegDate' => $this->RegDate,
            'SystemCode' => $this->SystemCode
        ];
    }
}
