<?php


namespace app\modules\soap\skeletons\registration_by_service_number;


class RegByServiceNumberAnimal
{
    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $pet_id;

    /**
     * @var integer {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $species_id;

    /**
     * @soap
     * @var string {minOccurs=1, maxOccurs=1}
     */
    public $species_name;

    /**
     * @var integer {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $breed_id;

    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $breed_name;

    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $nickname_animal;

    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $chip_animal;

    /**
     * @var date {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $birthdate_animal;

    /**
     * @var integer {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $sex_animal;
}
