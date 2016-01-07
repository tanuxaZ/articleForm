<?php
/**
 * Класс вклчает методы валидации данных из форм
 */
class formValidate
{
    /**
     * Валидация значений формы
     *
     * @param array $validateData - массив правил валидации
     * $config = array(
     *        array(
     *            'field'   => 'title',
     *            'label'   => 'Заголовок',
     *            'rules'   => 'required|min_length[5]|max_length[200]'
     *       ),
     *       array(
     *           'field'   => 'date',
     *           'label'   => 'Дата',
     *           'rules'   => 'required'
     *       )
     *  );
     * @return bool|array - если ошибок нету - возвращает TRUE, если есть - массив ошибок
     */
    public function isValidate(Array $validateData)
    {
        $errorList = [];
        foreach ($validateData as $validateField) {
            $validateRules = explode('|', $validateField['rules']);
            $fieldName = $validateField['field'];
            $fieldLabel = $validateField['label'];

            foreach ($validateRules as $rule) {
                $funcArgument = [$fieldName, $fieldLabel];
                $ruleName = $rule;
                if (strpos($rule, '[') !== false) {
                    $ruleName = substr($rule, 0, strpos($rule, '['));
                    $funcArgument[] = substr($rule, strpos($rule, '[') + 1, strlen($rule) - strlen($ruleName) - 2);
                }
            }

            switch ($ruleName) {
                case 'required':
                    $err = call_user_func_array('self::isFull', $funcArgument);
                    if ($err !== true) {
                        $errorList[$fieldName][] = $err;
                    }
                    break;
                case 'max_length':
                    $err = call_user_func_array('self::isLessMaxLength', $funcArgument);
                    if ($err !== true) {
                        $errorList[$fieldName][] = $err;
                    }
                    break;
                case 'min_length':
                    $err = call_user_func_array('self::isLessMinLength', $funcArgument);
                    if ($err !== true) {
                        $errorList[$fieldName][] = $err;
                    }
                    break;
            }
        }

        if (count($errorList) == 0) {
            return true;
        }

        return $errorList;
    }

    /**
     * Проверка поля на заполнение
     *
     * @param $fieldName - имя поля
     * @param $fieldLabel - заголовок поля
     * @return bool|string - возвращает TRUE, если поле заполнено и ошибки нет
     *                       строку ошибки, если поле не заполненно
     */
    private static function isFull($fieldName, $fieldLabel)
    {
        if (array_key_exists($fieldName, $_POST)) {
            $str = $_POST[$fieldName];

            if (mb_strlen(trim($str)) > 0) {
                return true;
            }
        }
        return sprintf("Поле '%s' обязательно для заполнения", $fieldLabel);
    }

    /**
     * Проверка поля на максимальную длинну
     *
     * @param $fieldName - название поля
     * @param $fieldLabel - заголовок поля
     * @param $maxCount - максимальное число символов
     * @return bool|string - если к-тво символов не привышает $maxCount возвращает - TRUE,
     * если привышает - строку ошибки
     */
    private static function isLessMaxLength($fieldName, $fieldLabel, $maxCount)
    {
        if (array_key_exists($fieldName, $_POST)) {
            $str = $_POST[$fieldName];

            if (mb_strlen(trim($str)) <= $maxCount) {
                return true;
            }
        }

        return sprintf("Поле '%s' не может содержать больше %d символов", $fieldLabel, $maxCount);
    }

    /**
     * Проверка поля на минимальную длинну
     *
     * @param $fieldName - название поля
     * @param $fieldLabel - заголовок поля
     * @param $minCount - минимальное число символов
     * @return bool|string - если к-тво символов не меньше $minCount возвращает - TRUE,
     * если меньше - строку ошибки
     */
    private static function isLessMinLength($fieldName, $fieldLabel, $minCount)
    {
        if (array_key_exists($fieldName, $_POST)) {
            $str = $_POST[$fieldName];

            if (mb_strlen(trim($str)) >= $minCount) {
                return true;
            }
        }

        return sprintf("Поле '%s' не может содержать меньше %d символов", $fieldLabel, $minCount);
    }
}