<?php


namespace Okay\Modules\OkayCMS\FastOrder;


use Okay\Core\FrontTranslations;

class FormSpecification
{
    private $frontTranslations;

    public function __construct(FrontTranslations $frontTranslations)
    {
        $this->frontTranslations = $frontTranslations;
    }

    public function validateFields($rulesPerFieldName, $valuesPerFieldNames)
    {
        $errorsPerFieldNames = [];

        foreach($valuesPerFieldNames as $fieldName => $value) {
            $rules = $rulesPerFieldName[$fieldName];
            $validationResult = $this->validateField($value, $rules);

            $hasError = !empty($validationResult);
            if ($hasError) {
                $errorsPerFieldNames[$fieldName] = $validationResult;
            }
        }

        return $errorsPerFieldNames;
    }

    public function validateField($value, $rules)
    {
        foreach($rules as $rule) {
            if ($this->$rule($value)) {
                continue;
            }

            if ($rule == 'required') {
                return $this->frontTranslations->getTranslation('okay_cms__fast_order__form_required_error');
            }

            if ($rule == 'phone') {
                return $this->frontTranslations->getTranslation('okay_cms__fast_order__form_phone_error');
            }
        }

        return null;
    }

    private function required($value)
    {
        if (empty($value)) {
            return false;
        }

        return true;
    }

    private function phone($value)
    {
        return preg_match("/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/", $value);
    }
}