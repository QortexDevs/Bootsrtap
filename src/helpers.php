<?php
if (!function_exists('format_money_ru')) {
	function format_money_ru($value)
	{
		return number_format($value, 2, ',', ' ');
	}
}

if (!function_exists('format_period_name_ru')) {
	function format_period_name_ru($value)
	{
		$monthNamesRu = [
			1 => 'Январь',
			2 => 'Февраль',
			3 => 'Март',
			4 => 'Апрель',
			5 => 'Май',
			6 => 'Июнь',
			7 => 'Июль',
			8 => 'Август',
			9 => 'Сентябрь',
			10 => 'Октябрь',
			11 => 'Ноябрь',
			12 => 'Декабрь'
		];
		return $monthNamesRu[$value->month] . ' ' . $value->year;
	}
}
