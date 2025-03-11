<?php

namespace app\modules\v2\common\balance;

use app\models\db\tmc\Balance;
use app\models\db\tmc\Dosages;
use app\models\db\tmc\ProductionForm;
use yii\web\BadRequestHttpException;

/**
 * Рассчет при списаниии с баланса
 * Class WriteOffCalculation
 *
 * @package app\common\components\balance
 * @author Aleksandr Roik
 */
class WriteOffCalculation implements CalculationInterface
{
    /**
     * id баланса
     *
     * @var int
     */
    protected $idBalanceTmc;

    /**
     * id дозировки
     *
     * @var int|null
     */
    protected $idDosage;

    /**
     * Количество, введенное пользователем
     *
     * @var int|null
     */
    protected $countSelected;

    /**
     * Количество форм производства для списания
     * !!!Результат вычисляется
     *
     * @var int|null
     */
    protected $countProductionForm;

    /**
     * Флаг: списать целиком весь баланс
     *
     * @var boolean|null
     */
    protected $writeOffAll;

    /**
     * Флаг: списать целиком форму производства
     *
     * @var boolean|null
     */
    protected $writeOffPackForm;

    /**
     * Количество ТМЦ для списания в единицах измерения
     * !!!Результат вычисляется
     *
     * @var float
     */
    private $count;

    /**
     * Количество ТМЦ для утилизация в единицах измерения
     * !!!Результат вычисляется
     *
     * @var float
     */
    private $countUtilize;

    /**
     * Сумма для списания
     * !!!Результат вычисляется
     *
     * @var float
     */
    private $price;

    /**
     * Флаг, указывающий на использование утилизации
     *
     * @var bool
     */
    private $useUtilize = false;

    /**
     * @var Balance
     */
    private $balance;

    /**
     * @var Dosages
     */
    private $dosage;

    /**
     * WriteOffCalculation constructor.
     */
    public function __construct($conf = [])
    {
        $this->configInit($conf);
    }

    /**
     * Установка входящих параметров. Для синхрона со списком
     *
     * @param $conf
     */
    private function configInit($conf)
    {
        foreach ($conf as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    /**
     * Измеение флага, что указывает на использование утилизации
     *
     * @param bool $useUtilize
     * @return WriteOffCalculation
     */
    public function setUseUtilize(bool $useUtilize): self
    {
        $this->useUtilize = $useUtilize;

        return $this;
    }

    /**
     * Возвращает количество ТМЦ для списания, предварительно первый раз вычислив его
     *
     * @return float
     */
    public function getCount(): float
    {
        if ($this->count === null) {
            $this->count = $this->calcCount();
        }

        return $this->count;
    }

    /**
     * Возвращает количество ТМЦ для утилизации, предварительно первый раз вычислив его
     *
     * @return float
     */
    public function getCountUtilize(): float
    {
        if ($this->countUtilize === null) {
            $this->countUtilize = $this->calcCountUtilize();
        }

        return $this->countUtilize;
    }

    /**
     * Возвращает сумму ТМЦ для списания, предварительно первый раз вычислив ее
     */
    public function getPrice(): float
    {
        if ($this->price === null) {
            $this->price = $this->calcPrice();
        }

        return $this->price;
    }

    /**
     * Возвращает количество форм производства по использованных единицах измерения
     *
     * @return float
     * @throws BadRequestHttpException
     */
    public function getCountProductionForm(): float
    {
        if ($this->countProductionForm === null) {
            $this->countProductionForm = $this->calcProductionForm();

            // Если есть утилизация, то берем полные формы
            if ($this->useUtilize && $this->getProductionForms()->is_utilize) {
                $this->countProductionForm = ceil($this->countProductionForm);
            }
        }

        return $this->countProductionForm;
    }

    /**
     * Вычисляет количество ТМЦ для списания
     *
     * @return float
     * @throws BadRequestHttpException
     */
    private function calcCount(): ?float
    {
        if ($this->writeOffAll) {
            //Если передано "write_off_all" - списываем целиком весь баланс
            return (float)$this->getBalance()->count;
        }

        //count_selected = единицы измерения
        $count = (float)$this->countSelected;
        if ($this->idDosage) {
            //Если по дозам - считаем количество доз.
            $count = (float)($count * $this->getDosage()->dosage);
        }

        return $count;
    }

    /**
     * Вычисляет количетсво форм производства
     *
     * @return float
     */
    private function calcProductionForm(): float
    {
        $count = $this->getCount();

        if (!$count) {
            throw new BadRequestHttpException("Количество ТМЦ для списания равно нулю.");
        }

        return $count / $this->getProductionForms()->volume;
    }

    /**
     * Вычисляет количество ТМЦ для списания
     *
     * @return float
     * @throws BadRequestHttpException
     */
    private function calcCountUtilize(): ?float
    {
        if (!$this->useUtilize || !$this->getProductionForms()->is_utilize) {
            return 0;
        }

        $countUtilize = ($this->getCountProductionForm() * $this->getProductionForms()->volume) - $this->getCount();

        //Проверки, чтобы не уйти в минус по списанию. Это может быть, если каким-то чудом на остатку нет
        //полного количества для одной формы.
        //Тогда на списание отдаем остаток.
        //Если $countUtilize - отрицательное, значит для утилизации нет ничего
        if ((float)bccomp($countUtilize, ($this->getBalance()->count - $this->getCount()), 2) > 0) {
            $countUtilize = $this->getBalance()->count - $this->getCount();
        }
        if ((float)bccomp($countUtilize, '0.00', 2) < 0) {
            $countUtilize = 0;
        }

        return (float)$countUtilize;
    }

    /**
     * Вычисляет сумму ТМЦ для списания
     */
    private function calcPrice(): float
    {
        //TODO: надо уточнить вычисление цены
        if ($this->writeOffPackForm) {
            return (float)($this->getCountProductionForm() * $this->getBalance()->price);
        } else {
            return (float)($this->getCount() / $this->getProductionForms()->volume * $this->getBalance()->price);
        }
    }

    /**
     * Возвращает объект баланса (с кеширование)
     *
     * @return Balance
     */
    private function getBalance(): Balance
    {
        if ($this->balance === null) {
            $this->balance = Balance::findOne($this->idBalanceTmc);

            if (!$this->balance) {
                throw new BadRequestHttpException("Значение «id баланса» неверно.");
            }
        }

        return $this->balance;
    }

    /**
     * Возвращает объект дозировки (с кеширование)
     *
     * @return Dosages
     * @throws BadRequestHttpException
     */
    public function getDosage(): ?Dosages
    {
        if ($this->idDosage === null) {
            return null;
        }

        if ($this->dosage === null) {
            $this->dosage = $this
                ->getBalance()
                ->getDosages()
                ->andWhere(['id' => $this->idDosage])
                ->one();

            if (!$this->dosage) {
                throw new BadRequestHttpException("Указанна несуществующая дозировка.");
            }
        }

        return $this->dosage;
    }

    /**
     * Возвращает форму выпуска
     *
     * @return int
     * @throws BadRequestHttpException
     */
    private function getProductionForms(): ProductionForm
    {
        return $this->getBalance()->production_form;
    }
}
