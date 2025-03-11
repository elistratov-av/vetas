<?php


namespace app\modules\animalid\skeletons\interfaces;


interface IntegrationModelInterface
{
    public function load($data);

    /*
    * @param string[]|string $attributeNames attribute name or list of attribute names that should be validated.
    * If this parameter is empty, it means any attribute listed in the applicable
    * validation rules should be validated.
    * @param bool $clearErrors whether to call [[clearErrors()]] before performing validation
    * @return bool whether the validation is successful without any error.
    * @throws InvalidArgumentException if the current scenario is unknown.
    */
    public function validate($attributeNames = null, $clearErrors = true);

    public function update();

    public function findIdenty();

    public function save();

    public function merge($finded);

    public function getErrorSummary($true);
}
