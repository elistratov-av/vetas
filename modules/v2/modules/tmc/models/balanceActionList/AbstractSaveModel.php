<?php

namespace app\modules\v2\modules\tmc\models\balanceActionList;

use app\models\db\tmc\Balance;
use app\models\db\tmc\BalanceAction;
use app\models\db\tmc\BalanceActionTmcList;
use app\models\db\tmc\BalanceFlow;
use app\modules\v2\common\balance\CalculationInterface;
use app\modules\v2\modules\tmc\dto\BalanceActionListSaveDto;
use yii\web\BadRequestHttpException;

/**
 * Абстрактный класс для обработки определенного дейтсвия для базансовых операций
 * Class AbstractActionListSaveModel
 *
 * @author Aleksandr Roik
 */
abstract class AbstractSaveModel
{
    /**
     * @var Balance
     */
    private $balance;

    /**
     * @var BalanceAction
     */
    protected $balanceAction;

    /**
     * @var BalanceActionListSaveDto
     */
    protected $itemDto;

    /**
     * @var CalculationInterface
     */
    protected $calculation;

    /**
     * AbstractActionModel constructor.
     *
     * @param BalanceAction $balanceAction
     * @param BalanceActionListSaveDto $itemDto
     */
    public function __construct(BalanceAction $balanceAction, BalanceActionListSaveDto $itemDto)
    {
        $this->balanceAction = $balanceAction;
        $this->itemDto = $itemDto;

        $calcClassName = $this->getCalcucationClassName();
        if ($calcClassName) {
            $this->calculation = new $calcClassName($itemDto->toArray());
        }
    }

    /**
     * Валидация
     *
     * @return $this
     */
    abstract public function validate(): AbstractSaveModel;

    /**
     * Название класса для расчетов
     *
     * @return string
     * @see CalculationInterface
     */
    abstract protected function getCalcucationClassName(): ?string;

    /**
     * Начало выполнения обработки
     *
     * @return void
     */
    public function execute()
    {
        $this->validate();

        if (!$this->beforeSave()) {
            return;
        }

        $this->afterSave(
            $this->saveBalanceActionTmcList()
        );
    }

    /**
     * Запускается до выполнения
     *
     * @return bool
     */
    public function beforeSave(): bool
    {
        return true;
    }

    /**
     * Запускаектся после выполнения
     *
     * @param BalanceActionTmcList $actionList
     */
    public function afterSave(BalanceActionTmcList $actionList)
    {

    }

    /**
     * Сохраняем данные об действии над ТМЦ
     *
     * @return BalanceActionTmcList
     * @throws BadRequestHttpException
     */
    protected function saveBalanceActionTmcList(): BalanceActionTmcList
    {
        //1. Сохраняем данные об действии над ТМЦ
        if ($this->itemDto->id) {
            $actionList = BalanceActionTmcList::findOne($this->itemDto->id);
        } else {
            $actionList = new BalanceActionTmcList(
                $this->getBalanceActionListAttr()
            );
        }

        if (!$actionList->save()) {
            $errors = $actionList->getErrorSummary(true);
            throw new BadRequestHttpException(
                empty($errors) ? 'Ошибка при сохранении информации о действии над ТМЦ ' : implode(";", array_unique(array_values($errors)))
            );
        }

        return $actionList;
    }

    /**
     * Операции над балансом
     *
     * @return $this
     * @throws BadRequestHttpException
     */
    protected function saveBalanceFlow($attributes): self
    {
        if (!$attributes) {
            return $this;
        }

        $flow = new BalanceFlow($attributes);

        if (!$flow->save()) {
            $errors = $flow->getErrorSummary(true);
            throw new BadRequestHttpException(
                empty($errors) ? 'Ошибка при сохранении информации о действии над ТМЦ ' : implode(";", array_unique(array_values($errors)))
            );
        }

        return $this;
    }

    /**
     * Возвращает аттрибуты сущности для модели BalanceActionTmcList
     *
     * @return array
     */
    protected function getBalanceActionListAttr(): array
    {
        return [
            'id_action'             => $this->balanceAction->getPrimaryKey(),
            'id_balance_tmc'        => $this->itemDto->idBalanceTmc,
            'id_organization'       => $this->itemDto->idOrganization,
            'id_specialist'         => $this->itemDto->idSpecialist,
            'count_selected'        => $this->itemDto->countSelected,
            'count'                 => $this->getCount(),
            'count_production_form' => $this->getCountProductionForm(),
            'count_utilize'         => $this->getCountUtilize(),
            'write_off_all'         => $this->itemDto->writeOffAll ?? false,
            'write_off_pack_form'   => $this->itemDto->writeOffPackForm ?? false,
            'id_dosage'             => $this->itemDto->idDosage,
            'comment'               => $this->itemDto->comment,
        ];
    }

    /**
     * Количество единиц
     *
     * @return float|null
     */
    protected function getCount(): ?float
    {
        return $this->calculation ? $this->calculation->getCount() : null;
    }

    /**
     * Количество форм
     *
     * @return float|null
     */
    protected function getCountProductionForm(): ?float
    {
        return $this->calculation ? $this->calculation->getCountProductionForm() : null;
    }

    /**
     * Количество единиц для списания
     *
     * @return float|null
     */
    protected function getCountUtilize(): ?float
    {
        return null;
    }

    /**
     * Возвращает объект баланса (с кеширование)
     *
     * @return Balance
     */
    protected function getBalance(): Balance
    {
        if ($this->balance === null) {
            $this->balance = Balance::findOne($this->itemDto->idBalanceTmc);

            if (!$this->balance) {
                throw new BadRequestHttpException("Значение «id баланса» неверно.");
            }
        }

        return $this->balance;
    }
}
