<?php

include './example_persons_array.php';

/* Разбиение ФИО */
function getPartsFromFullname($fullname) {
    $fullname = explode(' ', $fullname);

    return [
        'surname' => $fullname[0],
        'name' => $fullname[1],
        'patronomyc' => $fullname[2],
    ];
}

/* Объединение ФИО */
function getFullnameFromParts($surname, $name, $patronomyc) {
    return implode(' ', [$surname, $name, $patronomyc]);
}

/* Сокращение ФИО */
function getShortName($fullname) {
    $fullname = getPartsFromFullname($fullname);
    $surname = mb_substr($fullname['surname'], 0, 1);

    return "${fullname['name']} ${surname}.";
}

/* Определение пола по ФИО */
function getGenderFromName($fullname) {
    $sum = 0;
    $fullname = getPartsFromFullname($fullname);

    $surname = $fullname['surname'];
    $name = $fullname['name'];
    $patronomyc = $fullname['patronomyc'];

    if ( mb_substr($surname, -2, 2) === 'ва' ) $sum -= 1;
    if ( mb_substr($surname, -1, 1) === 'в' ) $sum += 1;

    if ( mb_substr($name, -1, 1) === 'а' ) $sum -= 1;
    if ( mb_substr($name, -1, 1) === 'й' || mb_substr($name, -1, 1) === 'н' ) $sum += 1;

    if ( mb_substr($patronomyc, -3, 3) === 'вна' ) $sum -= 1;
    if ( mb_substr($patronomyc, -2, 2) === 'ич' ) $sum += 1;

    if ($sum > 0) return 1;
    elseif ($sum < 0) return -1;
    elseif ($sum === 0) return 0;
}

/* Определение возрастно-полового состава */
function getGenderDescription($array) {
    $mens = array_filter($array, function($val) {
        if (getGenderFromName($val['fullname']) === 1) return $val['fullname'];
    });

    $womens = array_filter($array, function($val) {
        if (getGenderFromName($val['fullname']) === -1) return $val['fullname'];
    });

    $undefined = array_filter($array, function($val) {
        if (getGenderFromName($val['fullname']) === 0) return $val['fullname'];
    });

    $mens = count($mens);
    $womens = count($womens);
    $undefined = count($undefined);

    $mens = round($mens / count($array) * 100, 1);
    $womens = round($womens / count($array) * 100, 1);
    $undefined = round($undefined / count($array) * 100, 1);

    return <<<EOT
    Гендерный состав аудитории: <br/>
    --------------------------- <br/>
    Мужчины - {$mens}% <br/>
    Женщины - {$womens}% <br/>
    Не удалось определить - {$undefined}% <br/>
    EOT;
}

/* Случайный человек с противоположным полом */
function getRandomHuman($array, $gender) {
    $randomKey = array_rand($array, 1);
    $randomHuman = $array[$randomKey]['fullname'];
    $randomGender = getGenderFromName($randomHuman);

    if ($randomGender === 0) return getRandomHuman($array, $gender);
    if ($randomGender === $gender) return getRandomHuman($array, $gender);

    return $randomHuman;
}

/* Идеальный подбор пары */
function getPerfectPartner($surname, $name, $patronomyc, $array) {
    $fullname = getFullnameFromParts($surname, $name, $patronomyc);
    $fullname = mb_convert_case($fullname, MB_CASE_TITLE_SIMPLE);

    $gender = getGenderFromName($fullname);
    if ($gender === 0) return 'Пол неопределен, пару подобрать невозможно.';

    $randomHuman = getRandomHuman($array, $gender);

    $fullname = getShortName($fullname);
    $randomHuman = getShortName($randomHuman);
    $result = round(rand(5000, 10000) / 100, 2);

    return <<<EOT
    $fullname + $randomHuman = <br/>
    ♡ Идеально на {$result}% ♡
    EOT;
}

?>