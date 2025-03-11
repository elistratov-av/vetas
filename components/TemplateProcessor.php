<?php
namespace app\components;

use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\Shared\XMLWriter;
use PhpOffice\PhpWord\TemplateProcessor as PHPWordTemplateProcessor;

class TemplateProcessor extends PHPWordTemplateProcessor
{

    public function setComplexBlock($search, AbstractElement $complexType): void
    {
        $elementName = substr(get_class($complexType), strrpos(get_class($complexType), '\\') + 1);

        //This is the solution
        if ($elementName === 'Section') {
            $elementName = 'Container';
        }
        $objectClass = 'PhpOffice\\PhpWord\\Writer\\Word2007\\Element\\' . $elementName;

        $xmlWriter = new XMLWriter();
        /** @var \PhpOffice\PhpWord\Writer\Word2007\Element\AbstractElement $elementWriter */
        $elementWriter = new $objectClass($xmlWriter, $complexType, false);
        $elementWriter->write();

        $this->replaceXmlBlock($search, $xmlWriter->getData(), 'w:p');
    }
}