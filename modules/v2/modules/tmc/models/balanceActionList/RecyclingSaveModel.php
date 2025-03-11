<?php

namespace app\modules\v2\modules\tmc\models\balanceActionList;

/**
 * Утилизация
 * Class RecyclingActionListModel
 *
 * @author Aleksandr Roik
 */
class RecyclingSaveModel extends AbstractSaveModel
{
    /**
     * @return string
     */
    protected function getCalcucationClassName(): ?string
    {
        return null;
    }

    /**
     * Валидация
     *
     * @return $this
     */
    public function validate(): AbstractSaveModel
    {
        return $this;
    }
}
