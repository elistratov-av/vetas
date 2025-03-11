<?php

namespace app\modules\soap\models\etp\response;

class Responsible
{
    /** @var string */
    public $LastName;

    /** @var string */
    public $FirstName;

    /** @var string */
    public $MiddleName;

    /** @var string */
    public $JobTitle;

    /** @var string */
    public $Phone;

    public $Email;

    public function __construct(array $responsible)
    {
        $this->LastName = $responsible['LastName'] ?? '';
        $this->FirstName = $responsible['FirstName'] ?? '';
        $this->MiddleName = $responsible['MiddleName'] ?? '';
        $this->JobTitle = $responsible['JobTitle'] ?? '';
        $this->Phone = $responsible['Phone'] ?? '';
        $this->Email = $responsible['Email'] ?? '';
    }

    public function toArray()
    {
        return [
            'LastName' => $this->LastName,
            'FirstName' => $this->FirstName,
            'MiddleName' => $this->MiddleName,
            'JobTitle' => $this->JobTitle,
            'Phone' => $this->Phone,
            'Email' => $this->Email
        ];
    }
}
