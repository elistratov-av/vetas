<?php

use app\commands\migrate\Migration;

/**
 * Class m200626_115806_alt_set_once_per_day_for_services
 */
class m200626_115806_alt_set_once_per_day_for_services extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Обрезка рогов с/х животного']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Вакцинация животных с проведением клинического осмотра, консультации, инъекции']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Обрезка рогов с/х животного - обезроживание телят']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Обрезка когтей']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Обрезка клюва']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Удаление колтунов']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Санация ушных раковин']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Очистка параанальных желез']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Физиопроцедура']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Оксигенотерапия']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Взятие соскобов, мазков, смывов для диагностических исследований']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Обработка кожного покрова с санацией (туалетом) раневой поверхности']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Общий наркоз для проведения оперативных вмешательств и манипуляций а) 1-ой категории сложности']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Общий наркоз для проведения оперативных вмешательств и манипуляций б) 2-ой категории сложности']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Общий наркоз для проведения оперативных вмешательств и манипуляций в) 3-ей категории сложности']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Общий наркоз для проведения оперативных вмешательств и манипуляций г) 4-ой категории сложности']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Общий наркоз для проведения оперативных вмешательств и манипуляций д) 5-ой категории сложности']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Интубация при введении наркоза для проведения компьютерной томографии']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Обработка ран']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Перевязка ран']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Оперативное вмешательство а) 1-ой категории сложности']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Оперативное вмешательство б) 2-ой категории сложности']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Оперативное вмешательство в) 3-ей категории сложности']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Оперативное вмешательство г) 4-ой категории сложности']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Оперативное вмешательство д) 5-ой категории сложности']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Кастрация, стерилизация (оперативное вмешательство) - с/х ж-ые до 2-х месяцев, коты, самцы декоративных животных (хорьки, норки, морские свинки, лисы и другие животные)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Кастрация, стерилизация (оперативное вмешательство) - кошки, самки декоративных животных (хорьки, норки, морские свинки, лисы и другие животные)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: до 5 кг']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: свыше 5 кг до 15 кг']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Кастрация, стерилизация (оперативное вмешательство) - суки: до 5 кг']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 5 кг до 15 кг']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 15 кг до 25 кг']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 25 кг']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Ампутация рудиментарных фаланг у собак - до 2-х недельного возраста (с местным обезболиванием)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Ампутация рудиментарных фаланг у собак - от 2-х до 4-х недельного возраста']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Ампутация рудиментарных фаланг у собак - свыше 4-х недельного возраста']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Ампутация хвоста у собак - до 10-ти дневного возраста (с местным обезболиванием)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Ампутация хвоста у собак - от 10-ти дневного до 2-х месячного возраста']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Ампутация хвоста у собак - свыше 2-х месячного возраста']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Купирование ушных раковин у собак - до 10-ти дневного возраста (с местным обезболиванием)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Купирование ушных раковин у собак - от 10-ти дневного до 3-х месячного возраста']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Купирование ушных раковин у собак - свыше 3-х месячного возраста']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Удаление параанальных желез у декоративных животных (хорьки, норки и другие животные)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Катетеризация мочевого пузыря - кошек, котов']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Катетеризация мочевого пузыря - сук, кобелей']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Санация мочевого пузыря, промывание полости матки']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Блокады - инфильтрационная']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Блокады - проводниковая, нервного ганглия']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Блокады - эпидуральная']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Наложение гипсовой повязки (без репозиции) - мелкие породы собак и кошки']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Наложение гипсовой повязки (без репозиции) - крупные породы собак']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Снятие гипсовой повязки - мелкие породы собак и кошки']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Репозиция кости']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Вправление вывиха']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Пункция брюшной или грудной полости']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Родовспоможение']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Снятие швов']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Вскрытие абсцессов, гематом и т.д. - без установки дренажа']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Вскрытие абсцессов, гематом и т.д. - с установкой дренажа']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Обработка послеоперационного шва']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Снятие зубного камня']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Удаление зубов - молочных у кошек']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Удаление зубов - постоянных у кошек']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Удаление зубов - молочных и постоянных у собак, грызунов']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Обрезка резцов у грызунов']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Обрезка моляров у грызунов']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Субконъюнктивальная инъекция']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Офтальмоскопия']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Офтальмоскопия - флюоресцииновая проба']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Тонометрия глаза']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Ретробульбарная инъекция']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Определение слезопродукции при диагностике глаз (тест Ширмера)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Химическое прижигание роговичного дефекта']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биомикроскопия глаза']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Гониоскопия глаза']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Общий анализ мочи']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Общий анализ кала']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Общий клинический анализ крови - определение гемоглобина']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Общий клинический анализ крови - подсчет эритроцитов']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Общий клинический анализ крови - подсчет лейкоцитов']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Общий клинический анализ крови - определение СОЭ']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Общий клинический анализ крови - выведение лейкоцитарной формулы']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение общего белка']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение общего билирубина']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение мочевины']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение общего холестерина']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение амилазы']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение гаммаглутамилтрансферазы']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение триглицеридов']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение щелочной фосфатазы']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение глюкозы']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение креатинина']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение АСТ (аспартатаминотрансферазы)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение АЛТ (аланинаминотрансферазы)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение лактатдегидрогеназы']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение железа']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение кальция']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение натрия']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение магния']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение фосфора неорганического']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение белковых фракций']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение липазы']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение калия']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение креатинкиназы']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение мочевой кислоты']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение гемоглобина']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови - определение амилазы панкреатической']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Определение гормонов в сыворотке крови - трийодтиронин']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Определение гормонов в сыворотке крови - тироксин']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Определение гормонов в сыворотке крови - кортизол']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Определение гормонов в сыворотке крови - прогестерон']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Определение гормонов в сыворотке крови - эстрадиол']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Определение гормонов в сыворотке крови - тестостерон']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Определение электролитов крови (хлоридов, натрия, кальция, калия, водородного показателя (pH))']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Исследование на кровепаразитарные болезни']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Гельминтокопрологические исследования']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Микроскопические исследования на дерматофиты, демодекоз и эктопаразиты']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Цитологические исследования']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Гистологическое исследование биологического материала']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Экспресс-диагностика с применением тест-систем для определения инфекционных болезней животных']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Экспресс-диагностика показателей крови на анализаторах IDEXX']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Экспресс-диагностика глюкозы (с использованием глюкометра)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Плазмаферез и гемосорбция']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Санитарная стрижка животных - мелкие животные (до 5 кг)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Санитарная стрижка животных - средние животные (свыше 5 кг до 15 кг)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Санитарная стрижка животных - крупные животные (свыше 15 кг)']);

        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Санитарная помывка животных - мелкие животные (до 5 кг)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Санитарная помывка животных - средние животные (свыше 5 кг до 15 кг)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Санитарная помывка животных - крупные животные (свыше 15 кг)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Отбор проб для лабораторных исследований)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Груминг кошек (комплекс)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Груминг собак (комплекс) до 10 кг - короткошерстные (до 3 см)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Груминг собак (комплекс) до 10 кг - среднешерстные (до 6 см)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Груминг собак (комплекс) до 10 кг - длинношерстные (свыше 6 см)']);

        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Груминг собак (комплекс) свыше 10 кг до 20 кг - короткошерстные (до 3см)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Груминг собак (комплекс) свыше 10 кг до 20 кг - среднешерстные (до 6 см)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Груминг собак (комплекс) свыше 10 кг до 20 кг - длинношерстные (свыше 6 см)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Груминг собак (комплекс) свыше 20 кг: - короткошерстные (до 3 см)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Груминг собак (комплекс) свыше 20 кг: - среднешерстные (до 6 см)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Груминг собак (комплекс) свыше 20 кг: - длинношерстные (свыше 6 см)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Клинический осмотр птиц, рыб, грызунов, рептилий, мелких животных и др.']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Вакцинация']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Стрижка животных']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Санитарная стрижка и помывка']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Офтальмология']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Травматология']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Блокады']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Купирование ушей']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Купирование хвоста']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Удаление прибылых пальцев']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Кастрация, стерилизация']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Консультация и лечебные манипуляции']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Экспресс-диагностика']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Иные исследования']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Определение гормонов в сыворотке крови']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Биохимические исследования крови']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Другие оперативные вмешательства']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Общий клинический анализ крови']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Удаление зубов']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Общий клинический анализ крови - подсчет форменных элементов крови (эритроцитов, лейкоцитов) с определением гемоглобина']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Измерение артериального давления (тонометрия)']);
        $this->update('gov_services', ['once_per_day' => true], ['name' => 'Диспансеризация']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Обрезка рогов с/х животного']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Вакцинация животных с проведением клинического осмотра, консультации, инъекции']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Обрезка рогов с/х животного - обезроживание телят']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Обрезка когтей']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Обрезка клюва']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Удаление колтунов']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Санация ушных раковин']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Очистка параанальных желез']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Физиопроцедура']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Оксигенотерапия']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Взятие соскобов, мазков, смывов для диагностических исследований']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Обработка кожного покрова с санацией (туалетом) раневой поверхности']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Общий наркоз для проведения оперативных вмешательств и манипуляций а) 1-ой категории сложности']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Общий наркоз для проведения оперативных вмешательств и манипуляций б) 2-ой категории сложности']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Общий наркоз для проведения оперативных вмешательств и манипуляций в) 3-ей категории сложности']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Общий наркоз для проведения оперативных вмешательств и манипуляций г) 4-ой категории сложности']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Общий наркоз для проведения оперативных вмешательств и манипуляций д) 5-ой категории сложности']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Интубация при введении наркоза для проведения компьютерной томографии']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Обработка ран']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Перевязка ран']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Оперативное вмешательство а) 1-ой категории сложности']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Оперативное вмешательство б) 2-ой категории сложности']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Оперативное вмешательство в) 3-ей категории сложности']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Оперативное вмешательство г) 4-ой категории сложности']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Оперативное вмешательство д) 5-ой категории сложности']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Кастрация, стерилизация (оперативное вмешательство) - с/х ж-ые до 2-х месяцев, коты, самцы декоративных животных (хорьки, норки, морские свинки, лисы и другие животные)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Кастрация, стерилизация (оперативное вмешательство) - кошки, самки декоративных животных (хорьки, норки, морские свинки, лисы и другие животные)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: до 5 кг']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: свыше 5 кг до 15 кг']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Кастрация, стерилизация (оперативное вмешательство) - суки: до 5 кг']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 5 кг до 15 кг']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 15 кг до 25 кг']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 25 кг']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Ампутация рудиментарных фаланг у собак - до 2-х недельного возраста (с местным обезболиванием)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Ампутация рудиментарных фаланг у собак - от 2-х до 4-х недельного возраста']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Ампутация рудиментарных фаланг у собак - свыше 4-х недельного возраста']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Ампутация хвоста у собак - до 10-ти дневного возраста (с местным обезболиванием)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Ампутация хвоста у собак - от 10-ти дневного до 2-х месячного возраста']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Ампутация хвоста у собак - свыше 2-х месячного возраста']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Купирование ушных раковин у собак - до 10-ти дневного возраста (с местным обезболиванием)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Купирование ушных раковин у собак - от 10-ти дневного до 3-х месячного возраста']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Купирование ушных раковин у собак - свыше 3-х месячного возраста']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Удаление параанальных желез у декоративных животных (хорьки, норки и другие животные)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Катетеризация мочевого пузыря - кошек, котов']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Катетеризация мочевого пузыря - сук, кобелей']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Санация мочевого пузыря, промывание полости матки']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Блокады - инфильтрационная']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Блокады - проводниковая, нервного ганглия']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Блокады - эпидуральная']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Наложение гипсовой повязки (без репозиции) - мелкие породы собак и кошки']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Наложение гипсовой повязки (без репозиции) - крупные породы собак']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Снятие гипсовой повязки - мелкие породы собак и кошки']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Репозиция кости']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Вправление вывиха']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Пункция брюшной или грудной полости']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Родовспоможение']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Снятие швов']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Вскрытие абсцессов, гематом и т.д. - без установки дренажа']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Вскрытие абсцессов, гематом и т.д. - с установкой дренажа']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Обработка послеоперационного шва']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Снятие зубного камня']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Удаление зубов - молочных у кошек']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Удаление зубов - постоянных у кошек']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Удаление зубов - молочных и постоянных у собак, грызунов']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Обрезка резцов у грызунов']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Обрезка моляров у грызунов']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Субконъюнктивальная инъекция']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Офтальмоскопия']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Офтальмоскопия - флюоресцииновая проба']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Тонометрия глаза']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Ретробульбарная инъекция']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Определение слезопродукции при диагностике глаз (тест Ширмера)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Химическое прижигание роговичного дефекта']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биомикроскопия глаза']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Гониоскопия глаза']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Общий анализ мочи']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Общий анализ кала']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Общий клинический анализ крови - определение гемоглобина']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Общий клинический анализ крови - подсчет эритроцитов']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Общий клинический анализ крови - подсчет лейкоцитов']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Общий клинический анализ крови - определение СОЭ']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Общий клинический анализ крови - выведение лейкоцитарной формулы']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение общего белка']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение общего билирубина']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение мочевины']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение общего холестерина']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение амилазы']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение гаммаглутамилтрансферазы']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение триглицеридов']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение щелочной фосфатазы']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение глюкозы']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение креатинина']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение АСТ (аспартатаминотрансферазы)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение АЛТ (аланинаминотрансферазы)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение лактатдегидрогеназы']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение железа']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение кальция']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение натрия']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение магния']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение фосфора неорганического']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение белковых фракций']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение липазы']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение калия']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение креатинкиназы']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение мочевой кислоты']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение гемоглобина']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови - определение амилазы панкреатической']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Определение гормонов в сыворотке крови - трийодтиронин']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Определение гормонов в сыворотке крови - тироксин']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Определение гормонов в сыворотке крови - кортизол']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Определение гормонов в сыворотке крови - прогестерон']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Определение гормонов в сыворотке крови - эстрадиол']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Определение гормонов в сыворотке крови - тестостерон']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Определение электролитов крови (хлоридов, натрия, кальция, калия, водородного показателя (pH))']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Исследование на кровепаразитарные болезни']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Гельминтокопрологические исследования']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Микроскопические исследования на дерматофиты, демодекоз и эктопаразиты']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Цитологические исследования']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Гистологическое исследование биологического материала']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Экспресс-диагностика с применением тест-систем для определения инфекционных болезней животных']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Экспресс-диагностика показателей крови на анализаторах IDEXX']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Экспресс-диагностика глюкозы (с использованием глюкометра)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Плазмаферез и гемосорбция']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Санитарная стрижка животных - мелкие животные (до 5 кг)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Санитарная стрижка животных - средние животные (свыше 5 кг до 15 кг)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Санитарная стрижка животных - крупные животные (свыше 15 кг)']);

        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Санитарная помывка животных - мелкие животные (до 5 кг)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Санитарная помывка животных - средние животные (свыше 5 кг до 15 кг)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Санитарная помывка животных - крупные животные (свыше 15 кг)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Отбор проб для лабораторных исследований)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Груминг кошек (комплекс)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Груминг собак (комплекс) до 10 кг - короткошерстные (до 3 см)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Груминг собак (комплекс) до 10 кг - среднешерстные (до 6 см)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Груминг собак (комплекс) до 10 кг - длинношерстные (свыше 6 см)']);

        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Груминг собак (комплекс) свыше 10 кг до 20 кг - короткошерстные (до 3см)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Груминг собак (комплекс) свыше 10 кг до 20 кг - среднешерстные (до 6 см)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Груминг собак (комплекс) свыше 10 кг до 20 кг - длинношерстные (свыше 6 см)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Груминг собак (комплекс) свыше 20 кг: - короткошерстные (до 3 см)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Груминг собак (комплекс) свыше 20 кг: - среднешерстные (до 6 см)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Груминг собак (комплекс) свыше 20 кг: - длинношерстные (свыше 6 см)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Клинический осмотр птиц, рыб, грызунов, рептилий, мелких животных и др.']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Вакцинация']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Стрижка животных']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Санитарная стрижка и помывка']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Офтальмология']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Травматология']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Блокады']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Купирование ушей']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Купирование хвоста']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Удаление прибылых пальцев']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Кастрация, стерилизация']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Консультация и лечебные манипуляции']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Экспресс-диагностика']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Иные исследования']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Определение гормонов в сыворотке крови']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Биохимические исследования крови']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Другие оперативные вмешательства']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Общий клинический анализ крови']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Удаление зубов']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Общий клинический анализ крови - подсчет форменных элементов крови (эритроцитов, лейкоцитов) с определением гемоглобина']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Измерение артериального давления (тонометрия)']);
        $this->update('gov_services', ['once_per_day' => false], ['name' => 'Диспансеризация']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200626_115806_alt_set_once_per_day_for_services cannot be reverted.\n";

        return false;
    }
    */
}
